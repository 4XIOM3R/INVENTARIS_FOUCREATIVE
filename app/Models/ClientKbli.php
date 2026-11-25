<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientKbli extends Model
{
    protected $fillable = ['client_id','kbli_code','title','license_status','location'];
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
