<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model {
    use SoftDeletes;
    protected $fillable = ['name','location','contact_name','contact_designation','email','phone','website','description','next_followup_date'];
    protected $casts = ['next_followup_date' => 'date'];
    public function projects() { return $this->hasMany(Project::class); }
    public function followups() { return $this->morphMany(Followup::class, 'followupable'); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function meetings() { return $this->hasMany(Meeting::class); }
}
