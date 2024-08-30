<?php
namespace App\Livewire;

use Livewire\Component;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use App\Models\DnsRecord;

class DnsRecordsEditor extends Component
{
    use WithFileUploads;

    public $domainName;
    public $domainId;
    public $records = [];
    public $editingRecord = null;
    public $newRecord = [
        'name' => '',
        'content' => '',
        'ttl' => '',
        'type' => 'A',
        'priority' => null
    ];
    
    public $showEditForm = false;
    public $showAddForm = false;
    public $loading = true;
    public $recordId;

    protected $cloudflare;
    protected $cloudflare_zones;
    public $deletingRecordId = null;
    public $editingRecordId = null;
    public $togglingProxyRecordId = null;
    public $addingRecord = null;
    public $importFile;


    public function mount($domainName, $recordId = null)
    {
        $this->domainName = $domainName;
        $user = auth()->user();
        $domain = $user->domains()->where('domain_name', $domainName)->first();

        if ($domain) {
            $this->domainId = $domain->id;
        } else {
            $this->notify('error', __('Domain not found.'));
        }

        $this->recordId = $recordId;

        $this->initializeCloudflare();

        $this->loadRecords();

        if ($this->recordId) {
            // $this->startEdit($this->recordId);
        }
    }

    public function exportRecords($format)
    {
        $user = auth()->user();
        $domains = $user->domains();
        $domain = $domains->where('domain_name', $this->domainName)->firstOrFail();
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
            return response()->streamDownload(function () use ($records) {
                echo json_encode($records);
            }, 'dns_records.json', ['Content-Type' => 'application/json']);
        }
    }

    public function importRecords()
    {
        $user = auth()->user();
        $domains = $user->domains();
        $domain = $domains->where('domain_name', $this->domainName)->firstOrFail();

        if ($this->importFile) {
            $path = $this->importFile->getRealPath();
            $file = fopen($path, 'r');
            $header = fgetcsv($file);
            
            while ($row = fgetcsv($file)) {
                $data = array_combine($header, $row);

                try {
                    $this->validateRecord($data);

                    $zoneId = $this->getZoneId();
                    $priority = in_array($data['type'], ['MX', 'SRV']) ? ($data['priority'] ?? 0) : '0';

                    $proxied = isset($data['proxied']) ? (bool) $data['proxied'] : false;
                    $this->cloudflare->addRecord(
                        $zoneId,
                        $data['type'],
                        $data['name'],
                        $data['value'],
                        $data['ttl'],
                        $proxied,
                        (string)$priority
                    );

                    $domain->dnsRecords()->updateOrCreate(
                        [
                            'type' => $data['type'],
                            'name' => $data['name']
                        ],
                        [
                            'value' => $data['value'],
                            'ttl' => $data['ttl'],
                            'priority' => $priority,
                            'created_ip' => request()->ip(),
                            'updated_ip' => request()->ip(),
                            'updates' => \DB::raw('updates + 1')
                        ]
                    );

                } catch (\Exception $e) {
                    $this->notify('error', __('Records failed to import: '.$e->getMessage()));
                    $this->loadRecords();
                    return;
                }
            }

            fclose($file);
            $this->importFile = null;
            $this->loadRecords();
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


    public function triggerImport()
    {
        $this->dispatch('triggerFileInput');
    }

    public function updatedImportFile()
    {
        $this->importRecords();
    }

    private function initializeCloudflare()
    {
        $key = new APIToken(env('CLOUDFLARE_API_TOKEN'));
        $adapter = new Guzzle($key);
        $this->cloudflare = new DNS($adapter);
        $this->cloudflare_zones = new Zones($adapter);
    }

    public function loadRecords()
    {
        $this->loading = true;

        try {
            $zoneId = $this->getZoneId();
            $response = $this->cloudflare->listRecords($zoneId);

            $cloudflareRecords = !empty($response->result) ? $response->result : [];

            // Get all local DNS records for this zone
            $localRecords = DnsRecord::where('domain_id', $this->domainId)
                ->whereNull('deleted_at')
                ->get();

            // Create or update records based on Cloudflare data
            foreach ($cloudflareRecords as $record) {
                $localRecord = DnsRecord::where('type', $record->type)
                    ->where('name', $record->name)
                    ->first();

                if ($localRecord) {
                    // Update local record if the Cloudflare record has changed
                    $localRecord->update([
                        'value' => $record->content,
                        'ttl' => $record->ttl,
                        'priority' => $record->priority ?? null,
                        'updated_ip' => request()->ip(),
                        'updates' => \DB::raw('updates + 1')
                    ]);
                } else {
                    // Create new local record
                    DnsRecord::create([
                        'domain_id' => $this->domainId,
                        'type' => $record->type,
                        'name' => $record->name,
                        'value' => $record->content,
                        'ttl' => $record->ttl,
                        'priority' => $record->priority ?? null,
                        'created_ip' => request()->ip(),
                        'updated_ip' => request()->ip(),
                        'updates' => 0
                    ]);
                }
            }

            // Delete local records that no longer exist in Cloudflare
            foreach ($localRecords as $localRecord) {
                $existsInCloudflare = collect($cloudflareRecords)->contains(function ($record) use ($localRecord) {
                    return $record->type === $localRecord->type && $record->name === $localRecord->name;
                });

                if (!$existsInCloudflare) {
                    $localRecord->delete(); // Soft delete
                }
            }

            $this->records = $cloudflareRecords;
        } catch (\Exception $e) {
            $this->notify('error', __('Failed to load records: '.$e->getMessage()));
            Log::error($e->getMessage());
            $this->records = [];
        } finally {
            $this->loading = false;
        }
    }


    public function notify($type = 'success', $msg = 'Notification') {
        $this->dispatch('notification', type: $type, msg: $msg);
    }
    public function startEdit($recordId)
    {
        $this->editingRecord = collect($this->records)->firstWhere('id', $recordId);
        $this->showEditForm = true;
    }

    public function deleteRecord($recordId, $softDelete = false)
    {
        $this->deletingRecordId = $recordId;

        try {
            $record = DnsRecord::find($recordId);

            if ($softDelete && $record) {
                $record->delete();
                $this->notify('success', __('Record soft deleted successfully.'));
            } else {
                if ($record) {
                    $record->delete();
                }
                
                $zoneId = $this->getZoneId();
                $this->cloudflare->deleteRecord($zoneId, $recordId);
                $this->notify('success', __('Record deleted successfully.'));
            }
        } catch (\Exception $e) {
            $this->notify('error', __('Failed to delete record.'));
        } finally {
            $this->loadRecords();
            $this->deletingRecordId = null;
        }
    }


    public function toggleProxy($recordId)
    {
        $this->togglingProxyRecordId = $recordId;
        try {
            $record = collect($this->records)->firstWhere('id', $recordId);
            $record->proxied = !$record->proxied;

            $zoneId = $this->getZoneId();
            $this->cloudflare->updateRecordDetails($zoneId, $recordId, [
                'type' => $record->type,
                'name' => $record->name,
                'content' => $record->content,
                'ttl' => $record->ttl,
                'proxied' => $record->proxied,
            ]);

            $this->notify('success', __('Proxy status toggled successfully.'));
        } catch (\Exception $e) {
            $this->notify('error', __('Failed to toggle proxy.'));
            Log::error($e->getMessage());
        } finally {
            $this->loadRecords();
            $this->togglingProxyRecordId = null;
        }
    }

    public function updateRecord()
    {
        $this->editingRecordId = $this->editingRecord->id;

        try {
            $zoneId = $this->getZoneId();
            $proxied = $this->editingRecord->proxied !== null ? (bool) $this->editingRecord->proxied : false;

            $data = [
                'type' => $this->editingRecord->type,
                'name' => $this->editingRecord->name,
                'content' => $this->editingRecord->content,
                'ttl' => $this->editingRecord->ttl,
                'proxied' => $proxied,
            ];            

            if (in_array($this->editingRecord->type, ['MX', 'SRV'])) {
                $data['priority'] = $this->editingRecord->priority ?? 0;
            }

            $this->cloudflare->updateRecordDetails($zoneId, $this->editingRecord->id, $data);

            $this->notify('success', __('Record updated successfully.'));
        } catch (\Exception $e) {
            $this->notify('error', __('Failed to update record.'));
        } finally {
            $this->showEditForm = false;
            $this->loadRecords();
            $this->editingRecordId = 0;
        }
    }

    
    public function addRecord($reloadRecords = true)
    {
        $this->addingRecord = 1;

        try {
            $validatedData = $this->validate([
                'newRecord.type' => 'required|string|in:A,AAAA,CNAME,TXT,MX,NS,SRV',
                'newRecord.name' => 'required|string|max:255',
                'newRecord.content' => 'required|string',
                'newRecord.ttl' => 'required|integer|min:1',
                'newRecord.priority' => 'nullable|integer',
                'newRecord.proxied' => 'nullable|boolean',
            ]);
            
            $zoneId = $this->getZoneId();
            
            $priority = in_array($validatedData['newRecord']['type'], ['MX', 'SRV']) 
                ? (string) ($validatedData['newRecord']['priority'] ?? '0') 
                : '';

            $proxied = $validatedData['newRecord']['proxied'] ?? false;

            $this->cloudflare->addRecord(
                $zoneId, 
                $validatedData['newRecord']['type'], 
                $validatedData['newRecord']['name'], 
                $validatedData['newRecord']['content'], 
                $validatedData['newRecord']['ttl'], 
                $proxied,  
                $priority
            );      

            $this->notify('success', __('Record added successfully.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $this->notify('error', __('Validation failed: ') . implode(', ', $errors));
        } catch (\Exception $e) {
            $this->notify('error', __('Failed to add record: ' . $e->getMessage()));
        } finally {
            $this->showAddForm = false;
            if ($reloadRecords) {
                $this->loadRecords();
            }
            $this->addingRecord = null;
        }
    }


    private function getZoneId()
    {
        try {
            if(!$this->cloudflare_zones) {
                $key = new APIToken(env('CLOUDFLARE_API_TOKEN'));
                $adapter = new Guzzle($key);
                $this->cloudflare = new DNS($adapter);
                $this->cloudflare_zones = new Zones($adapter);
            }
            $zones = $this->cloudflare_zones->listZones($this->domainName);

            if (empty($zones->result)) {
                return response()->json(['error' => 'Zone not found in Cloudflare'], 404);
            }

            $zoneId = $zones->result[0]->id;
            return $zoneId;
        } catch (\Exception $e) {
            $this->notify('error', __('Failed to fetch zone ID.'));
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.dns-records-editor', [
            'success' => session('success'),
            'error' => session('error')
        ]);
    }
}
