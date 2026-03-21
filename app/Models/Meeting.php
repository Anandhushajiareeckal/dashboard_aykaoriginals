<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model {
    use SoftDeletes;
    protected $fillable = ['title','brand_id','project_id','employee_id','meeting_at','duration_minutes','mode','notes','reminder_sent'];
    protected $casts = ['meeting_at'=>'datetime','reminder_sent'=>'boolean'];
    public function brand() { return $this->belongsTo(Brand::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
}
