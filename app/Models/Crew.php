<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crew extends Model {
    use SoftDeletes;
    protected $table = 'crew';
    protected $fillable = ['name','role','email','phone','location','status'];
    public function projects() { return $this->belongsToMany(Project::class, 'project_crew'); }
}
