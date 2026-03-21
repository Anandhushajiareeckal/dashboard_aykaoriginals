<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model {
    use SoftDeletes;
    protected $fillable = ['user_id','name','department','email','phone','salary','joining_date','status'];
    protected $casts = ['salary'=>'decimal:2','joining_date'=>'date'];
    public function user() { return $this->belongsTo(User::class); }
    public function meetings() { return $this->hasMany(Meeting::class); }
}
