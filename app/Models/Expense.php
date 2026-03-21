<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model {
    use SoftDeletes;
    protected $fillable = ['description','category','project_id','amount','expense_date','receipt_path'];
    protected $casts = ['amount'=>'decimal:2','expense_date'=>'date'];
    public function project() { return $this->belongsTo(Project::class); }
}
