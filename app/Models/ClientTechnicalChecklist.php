<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ClientTechnicalChecklist extends Model
{
    protected $fillable = ['client_id','task_code','task_title','is_completed','technical_notes','completed_at'];
    protected function casts(): array { return ['is_completed'=>'boolean','completed_at'=>'datetime']; }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
