<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientCredential extends Model
{
    protected $fillable = ['client_id','platform','username_or_email','secret_value','pic_name','pic_contact','needs_new_domain','verification_status','notes'];
    protected function casts(): array
    {
        return ['secret_value'=>'encrypted','needs_new_domain'=>'boolean'];
    }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
