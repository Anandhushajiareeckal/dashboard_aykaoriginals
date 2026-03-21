<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Followup extends Model {
    protected $fillable = ['user_id','note','followup_date','completed'];
    protected $casts = ['followup_date'=>'date','completed'=>'boolean'];
    public function followupable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }
}
