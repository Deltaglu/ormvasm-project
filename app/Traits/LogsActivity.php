<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created', $model->getActivityDescription('created'));
        });

        static::updated(function ($model) {
            $model->logActivity('updated', $model->getActivityDescription('updated'));
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getActivityDescription('deleted'));
        });
    }

    public function logActivity(string $action, string $description)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'description' => $description,
            'properties' => $this->getDirty(),
            'ip_address' => request()->ip(),
        ]);
    }

    protected function getActivityDescription(string $action): string
    {
        $name = class_basename($this);
        $identifier = $this->name ?? $this->numero ?? $this->reference ?? $this->id;

        $actions = [
            'created' => 'Création de ',
            'updated' => 'Modification de ',
            'deleted' => 'Suppression de ',
        ];

        return ($actions[$action] ?? '') . $name . " [{$identifier}]";
    }
}
