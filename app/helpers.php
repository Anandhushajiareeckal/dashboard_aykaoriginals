<?php
if (!function_exists('activity_log')) {
    function activity_log(string $action, string $entity_type, int $entity_id, string $description = ''): void {
        \App\Models\ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'description' => $description,
        ]);
    }
}
