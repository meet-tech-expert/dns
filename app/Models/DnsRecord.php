<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DnsRecord extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = ['domain_id', 'type', 'name', 'value', 'ttl', 'priority', 'created_ip', 'updated_ip', 'updates'];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
