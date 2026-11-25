<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientOnboardingItem extends Model
{
    protected $fillable = ['client_id','category','item_name','status','detail_notes','file_path','billing_schedule','package_choice','has_chatbot_addon'];
    protected function casts(): array { return ['has_chatbot_addon'=>'boolean']; }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
