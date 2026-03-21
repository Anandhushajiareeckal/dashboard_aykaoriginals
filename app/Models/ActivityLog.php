<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model {
    protected $fillable = [
        'user_id', 'action', 'module', 'entity_id',
        'entity_label', 'description', 'ip_address',
        'user_agent', 'url', 'method',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function getColorAttribute(): string {
        return match($this->action) {
            'login'    => 'bg-blue-50 text-blue-700',
            'logout'   => 'bg-gray-100 text-gray-600',
            'created'  => 'bg-green-50 text-green-700',
            'updated'  => 'bg-amber-50 text-amber-700',
            'deleted'  => 'bg-red-50 text-red-600',
            'status'   => 'bg-purple-50 text-purple-700',
            'exported' => 'bg-indigo-50 text-indigo-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
