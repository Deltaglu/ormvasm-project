<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TenantConnectionManager
{
    public function connect(string $database, string $runId = 'runtime', string $hypothesisId = 'H1'): void
    {
        if (trim($database) === '') {
            throw new InvalidArgumentException('Tenant database cannot be null or empty.');
        }

        Config::set('database.connections.tenant.database', $database);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // #region agent log
        file_put_contents(
            base_path('debug-2538fb.log'),
            json_encode([
                'sessionId' => '2538fb',
                'runId' => $runId,
                'hypothesisId' => $hypothesisId,
                'location' => 'app/Services/TenantConnectionManager.php:24',
                'message' => 'Tenant connection configured',
                'data' => ['database' => $database],
                'timestamp' => round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL,
            FILE_APPEND
        );
        // #endregion
    }
}

