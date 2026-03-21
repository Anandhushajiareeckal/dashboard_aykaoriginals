<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;
    protected $fillable = ['name','email','password'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed'];
    public function getJWTIdentifier()    { return $this->getKey(); }
    public function getJWTCustomClaims() { return []; }
    public function employee()            { return $this->hasOne(Employee::class); }
    public function activityLogs()        { return $this->hasMany(ActivityLog::class); }
}