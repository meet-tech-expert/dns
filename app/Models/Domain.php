<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'domain_name', 'status', 'created_ip', 'updated_ip', 'updates'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dnsRecords()
    {
        return $this->hasMany(DnsRecord::class);
    }
}
