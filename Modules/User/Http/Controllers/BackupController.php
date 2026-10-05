<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /**
     * Open database backup page.
     * @return Renderable
     */
    public function index()
    {
        if (checkRole('database_backup', 'view') == true) {
            activityLog('Admin', 'Opened Database Backup Menu');
            return view('user::admin.backup.index');
        } else {
            return abort(404);
        }
    }

    /* Download the database */
    public function download(Request $request)
    {
        if (checkRole('database_backup', 'add') != true) {
            return redirect()->back()->with('failure', 'You do not have permission to download backup');
        }

        $connection = config('database.default');
        $database = config('database.connections.' . $connection);

        $backupPath = storage_path('app/database_backup_' . date('Y-m-d_H-i-s') . '.sql');

        // Pass the password through the environment so it never appears in the process list
        $process = new Process([
            'mysqldump',
            '--user=' . $database['username'],
            '--host=' . $database['host'],
            '--port=' . $database['port'],
            $database['database'],
        ], null, ['MYSQL_PWD' => (string) $database['password']]);
        $process->setTimeout(600);

        $handle = fopen($backupPath, 'w');
        $process->run(function ($type, $buffer) use ($handle) {
            if ($type === Process::OUT) {
                fwrite($handle, $buffer);
            }
        });
        fclose($handle);

        if (!$process->isSuccessful()) {
            @unlink($backupPath);
            Log::error('BackupController->download : ' . $process->getErrorOutput());

            return redirect()->back()->with('failure', 'Could not create the database backup. Check the application log.');
        }

        activityLog('Admin', 'Downloaded a database backup');

        return response()->download($backupPath)->deleteFileAfterSend(true);
    }
}
