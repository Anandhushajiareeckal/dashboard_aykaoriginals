<?php
namespace App\Services;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ActivityLogger {
    protected Request $request;
    public function __construct(Request $request) { $this->request = $request; }
    public function log(string $action, ?string $module = null, ?int $entityId = null, ?string $entityLabel = null, ?string $description = null): ActivityLog {
        return ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'module'       => $module,
            'entity_id'    => $entityId,
            'entity_label' => $entityLabel,
            'description'  => $description ?? $this->buildDescription($action, $module, $entityLabel),
            'ip_address'   => $this->getIp(),
            'user_agent'   => $this->request->userAgent(),
            'url'          => $this->request->fullUrl(),
            'method'       => $this->request->method(),
        ]);
    }
    public function login(): void {
        $user = Auth::user();
        $this->log('login', 'Auth', $user->id, $user->name, "User \"{$user->name}\" logged in");
    }
    public function logout(): void {
        $user = Auth::user();
        if ($user) $this->log('logout', 'Auth', $user->id, $user->name, "User \"{$user->name}\" logged out");
    }
    public function created(string $module, int $id, string $label): void {
        $this->log('created', $module, $id, $label, "Created {$module} \"{$label}\"");
    }
    public function updated(string $module, int $id, string $label): void {
        $this->log('updated', $module, $id, $label, "Updated {$module} \"{$label}\"");
    }
    public function deleted(string $module, int $id, string $label): void {
        $this->log('deleted', $module, $id, $label, "Deleted {$module} \"{$label}\"");
    }
    public function statusChanged(string $module, int $id, string $label, string $status): void {
        $this->log('status', $module, $id, $label, "Changed {$module} \"{$label}\" status to \"{$status}\"");
    }
    public function exported(string $module, string $label): void {
        $this->log('exported', $module, null, $label, "Exported {$label}");
    }
    protected function getIp(): string {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
            $val = $this->request->server($key);
            if ($val) return trim(explode(',', $val)[0]);
        }
        return $this->request->ip() ?? '127.0.0.1';
    }
    protected function buildDescription(string $action, ?string $module, ?string $label): string {
        $who = Auth::user()?->name ?? 'System';
        return match($action) {
            'login'   => "{$who} logged in",
            'logout'  => "{$who} logged out",
            'created' => "{$who} created {$module} \"{$label}\"",
            'updated' => "{$who} updated {$module} \"{$label}\"",
            'deleted' => "{$who} deleted {$module} \"{$label}\"",
            default   => "{$who} performed {$action} on {$module} \"{$label}\"",
        };
    }
}
