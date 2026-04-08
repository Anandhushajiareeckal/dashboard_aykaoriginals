<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TalentModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'talent_models';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'location',
        'age',
        'gender',
        'nationality',
        'height',
        'bust',
        'waist',
        'hips',
        'shoe_size',
        'hair_color',
        'eye_color',
        'dress_size',
        'about',
        'status',
        'is_inhouse',
        'budget',
        'categories',
    ];

    protected $casts = [
        'is_inhouse' => 'boolean',
        'categories' => 'array',
        'budget'     => 'decimal:2',
    ];

    // -- Media Collections --------------------------------------
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portfolio');
        $this->addMediaCollection('compcard_hero')->singleFile();
        $this->addMediaCollection('compcard_shots');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(800)
            ->nonQueued();
    }

    // -- Relationships ------------------------------------------
    public function compCards()
    {
        return $this->hasMany(CompCard::class, 'talent_model_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_model', 'talent_model_id', 'project_id');
    }

    // -- Accessors ----------------------------------------------
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Active'     => 'green',
            'Inactive'   => 'gray',
            'On Leave'   => 'amber',
            'On Project' => 'blue',
            default      => 'gray',
        };
    }

    public function getIsInhouseAttribute($value): bool
    {
        return (bool) $value;
    }

    public function getCategoriesAttribute($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [$value];
        }
        return [];
    }
}
