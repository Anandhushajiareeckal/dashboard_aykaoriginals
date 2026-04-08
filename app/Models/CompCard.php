<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompCard extends Model
{
    protected $fillable = [
        'talent_model_id', 'template', 'title', 'agency_name',
        'agency_phone', 'agency_email', 'agency_website',
        'notable_clients', 'recent_campaigns', 'special_skills',
        'day_rate', 'half_day_rate', 'available_for',
        'public_slug', 'view_count', 'is_active',
    ];

    protected $casts = [
        'available_for' => 'array',
        'is_active'     => 'boolean',
        'day_rate'      => 'decimal:2',
        'half_day_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (CompCard $card) {
            if (empty($card->public_slug)) {
                $card->public_slug = Str::slug($card->talentModel->name ?? 'model') . '-' . Str::random(6);
            }
        });
    }

    public function talentModel()
    {
        return $this->belongsTo(TalentModel::class, 'talent_model_id');
    }

    public function shares()
    {
        return $this->hasMany(CompCardShare::class);
    }

    public function getTemplateNameAttribute(): string
    {
        return match($this->template) {
            'noir'       => 'Noir Editorial',
            'clean'      => 'Clean White',
            'bold'       => 'Bold Magazine',
            'luxury'     => 'Luxury Minimal',
            'typo'       => 'Typographic',
            default      => 'Noir Editorial',
        };
    }
}