<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia {
    use SoftDeletes, InteractsWithMedia;
    protected $fillable = ['title','brand_id','category','budget','start_date','end_date','status','progress','notes'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','budget'=>'decimal:2'];
    public function brand() { return $this->belongsTo(Brand::class); }
    public function models() { return $this->belongsToMany(TalentModel::class, 'project_model'); }
    public function crew() { return $this->belongsToMany(Crew::class, 'project_crew'); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function meetings() { return $this->hasMany(Meeting::class); }
}
