<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model {
    use SoftDeletes;
    protected $fillable = ['invoice_number','project_id','brand_id','amount','tax','total','status','due_date','paid_date','notes'];
    protected $casts = ['amount'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2','due_date'=>'date','paid_date'=>'date'];
    public function project() { return $this->belongsTo(Project::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
}
