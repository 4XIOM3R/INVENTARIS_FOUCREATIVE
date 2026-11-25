<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientKnowledgeBase extends Model
{
    protected $fillable = ['client_id','section_type','category_label','title','year','metadata'];
    protected function casts(): array { return ['metadata'=>'array']; }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
