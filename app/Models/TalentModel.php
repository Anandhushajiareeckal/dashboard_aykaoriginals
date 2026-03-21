<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TalentModel extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'talent_models';

    protected $fillable = [
        'name', 'age',
        'height', 'bust', 'waist', 'hips', 'shoe_size',
        'categories', 'email', 'phone', 'location',
        'about', 'budget', 'is_inhouse', 'status',
    ];

    protected $casts = [
        'categories' => 'array',
        'is_inhouse'  => 'boolean',
        'budget'      => 'decimal:2',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_model');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portfolio')->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(400)->height(400)->sharpen(10)->nonQueued();
        $this->addMediaConversion('preview')
             ->width(800)->height(800)->nonQueued();
    }
}
