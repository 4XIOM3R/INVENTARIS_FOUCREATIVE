<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'onboarding_token', 'company_name', 'brand_name', 'established_year',
        'company_history', 'positioning_statement', 'target_market', 'nib',
        'nib_issued_date', 'nib_updated_date', 'investment_status', 'npwp',
        'office_address', 'workshop_address', 'phone', 'wa_business_number',
        'wa_active_7_days', 'system_email', 'nib_email', 'corporate_emails',
        'website', 'social_media', 'status',
    ];

    protected function casts(): array
    {
        return [
            'target_market' => 'array',
            'corporate_emails' => 'array',
            'social_media' => 'array',
            'wa_active_7_days' => 'boolean',
            'nib_issued_date' => 'date',
            'nib_updated_date' => 'date',
        ];
    }

    public function kblis(): HasMany { return $this->hasMany(ClientKbli::class); }
    public function credentials(): HasMany { return $this->hasMany(ClientCredential::class); }
    public function onboardingItems(): HasMany { return $this->hasMany(ClientOnboardingItem::class); }
    public function knowledgeBases(): HasMany { return $this->hasMany(ClientKnowledgeBase::class); }
    public function technicalChecklists(): HasMany { return $this->hasMany(ClientTechnicalChecklist::class); }

    public function getCompletionPercentageAttribute(): int
    {
        $onboarding = $this->onboardingItems()->get();
        $credentials = $this->credentials()->get();
        $total = $onboarding->count() + $credentials->count();
        if ($total === 0) return 0;
        $complete = $onboarding->where('status', 'SUDAH LENGKAP')->count()
            + $credentials->where('verification_status', 'SUDAH LENGKAP')->count();
        return (int) round(($complete / $total) * 100);
    }
}
