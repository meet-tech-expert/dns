<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DNSRecord;
use App\Models\Domain;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Support\Facades\Auth;

class DNSController extends Controller
{
    protected $cloudflare;
    protected $cloudflare_zones;
    public $importFile;

    public function __construct()
    {
        $key = new APIToken(env('CLOUDFLARE_API_TOKEN'));
        $adapter = new Guzzle($key);
        $this->cloudflare = new DNS($adapter);
        $this->cloudflare_zones = new Zones($adapter);
    }


    public function exportRecords($format)
    {
        $domain = auth()->user()->domains()->where('name', request()->get('domainName'))->firstOrFail();
        $records = $domain->dnsRecords()->get();

        if ($format === 'csv') {

            return response()->streamDownload(function () use ($records) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['type', 'name', 'value', 'ttl', 'priority', 'created_ip', 'updated_ip', 'updates']);
                foreach ($records as $record) {
                    fputcsv($output, [
                        $record->type, $record->name, $record->value, 
                        $record->ttl, $record->priority, $record->created_ip, 
                        $record->updated_ip, $record->updates
                    ]);
                }
                fclose($output);
            }, 'dns_records.csv');
        }

        if ($format === 'json') {
            return response()->json($records);
        }
    }

    public function importRecords()
    {
        $domain = auth()->user()->domains()->where('name', request()->get('domainName'))->firstOrFail();

        if ($this->importFile) {
            $path = $this->importFile->getRealPath();
            $file = fopen($path, 'r');
            $header = fgetcsv($file);
            
            while ($row = fgetcsv($file)) {
                $data = array_combine($header, $row);
                try {
                    $this->validateRecord($data);
                    $domain->dnsRecords()->create([
                        'type' => $data['type'],
                        'name' => $data['name'],
                        'value' => $data['value'],
                        'ttl' => $data['ttl'],
                        'priority' => $data['priority'] ?? null,
                        'created_ip' => request()->ip(),
                        'updated_ip' => request()->ip(),
                        'updates' => 0
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            fclose($file);
            $this->importFile = null;
            $this->notify('success', __('Records imported successfully.'));
        }
    }

    public function validateRecord($data)
    {
        return validator($data, [
            'type' => 'required|string|in:A,AAAA,CNAME,MX,TXT,SRV,NS',
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'ttl' => 'required|integer|min:1',
            'priority' => 'nullable|integer'
        ])->validate();
    }

    protected function validateDomainOwnership($domainName)
    {
        $user = Auth::user();
        $domain = $user->domains()->where('domain_name', $domainName)->first();

        if (!$domain) {
            abort(403, 'Domain not found or not owned by the user.');
        }

        return $domain;
    }

    public function view()
    {
        $user = auth()->user();
        // dd($user->domains());
        $domains = $user->domains;
        return view('dns.index', compact('domains'));
    }

    protected function getCloudflareZoneID($domainName)
    {
        try {
            $response = $this->cloudflare->getZoneID($domainName);

            if ($response->success && !empty($response->result)) {
                return $response->result[0]->id;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve zone ID for ' . $domainName . ': ' . $e->getMessage());
        }

        return null;
    }

    public function get($domainName)
    {
        $userDomains = auth()->user()->domains;
        $domain = $userDomains->firstWhere('domain_name', $domainName);

        if (!$domain) {
            return response()->json(['error' => 'Domain not found'], 404);
        }

        $zoneId = $domain->cloudflare_zone_id;

        if (!$zoneId) {
            $zones = $this->cloudflare_zones->listZones($domainName);

            if (empty($zones->result)) {
                return response()->json(['error' => 'Zone not found in Cloudflare'], 404);
            }

            $zoneId = $zones->result[0]->id;
            $domain->update(['cloudflare_zone_id' => $zoneId]);
        }

        $response = $this->cloudflare->listRecords($zoneId);
        if (empty($response->result)) {
            return response()->json(['error' => 'Failed to retrieve DNS records'], 500);
        }

        foreach ($response->result as $record) {
            DNSRecord::updateOrCreate(
                ['domain_id' => $domain->id, 'name' => $record->name],
                [
                    'type' => $record->type,
                    'value' => $record->content,
                    'ttl' => $record->ttl,
                    'priority' => $record->priority ?? null,
                    'created_ip' => now(),
                    'updated_ip' => now(),
                    'updates' => 1
                ]
            );
        }

        return response()->json($response->result);
    }

    public function add($domainName)
    {
        $domain = $this->validateDomainOwnership($domainName);
        return view('dns.add', ['domain' => $domain]);
    }

    public function create(Request $request, $domainName)
    {
        $domain = $this->validateDomainOwnership($domainName);
        $response = $this->cloudflare->addRecord(
            $domain->cloudflare_zone_id, 
            $request->type, 
            $request->name, 
            $request->content, 
            $request->ttl, 
            $request->priority
        );

        if ($response->success) {
            DNSRecord::create([
                'domain_id' => $domain->id,
                'type' => $response->result->type,
                'name' => $response->result->name,
                'value' => $response->result->content,
                'ttl' => $response->result->ttl,
                'priority' => $response->result->priority ?? null,
                'created_ip' => now(),
                'updated_ip' => now(),
                'updates' => 1
            ]);
        }

        return response()->json($response);
    }

    public function edit($domainName)
    {
        $domain = $this->validateDomainOwnership($domainName);
        $zoneId = $domain->cloudflare_zone_id;

        if (!$zoneId) {
            $zones = $this->cloudflare_zones->listZones($domainName);

            if (empty($zones->result)) {
                return response()->json(['error' => 'Zone not found in Cloudflare'], 404);
            }

            $zoneId = $zones->result[0]->id;
            $domain->update(['cloudflare_zone_id' => $zoneId]);
        }

        return view('dns.records', ['domainName' => $domainName, 'recordId' => $zoneId]);
    }


    public function update(Request $request, $domainName, $recordId)
    {
        $domain = $this->validateDomainOwnership($domainName);
        $response = $this->cloudflare->updateRecordDetails(
            $domain->cloudflare_zone_id, 
            $recordId, 
            [
                'type' => $request->type,
                'name' => $request->name,
                'content' => $request->content,
                'ttl' => $request->ttl,
                'priority' => $request->priority
            ]
        );

        if ($response->success) {
            $dnsRecord = DNSRecord::where('domain_id', $domain->id)->where('id', $recordId)->first();
            if ($dnsRecord) {
                $dnsRecord->update([
                    'type' => $response->result->type,
                    'name' => $response->result->name,
                    'value' => $response->result->content,
                    'ttl' => $response->result->ttl,
                    'priority' => $response->result->priority ?? null,
                    'updated_ip' => now(),
                    'updates' => $dnsRecord->updates + 1
                ]);
            }
        }

        return response()->json($response);
    }

    public function delete($domainName, $recordId)
    {
        $domain = $this->validateDomainOwnership($domainName);
        $dnsRecord = DNSRecord::where('domain_id', $domain->id)->where('id', $recordId)->firstOrFail();

        $response = $this->cloudflare->deleteRecord($domain->cloudflare_zone_id, $recordId);

        if ($response->success) {
            $dnsRecord->delete();
        }

        return response()->json($response);
    }
}
