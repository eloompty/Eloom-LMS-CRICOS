<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RestoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /* Restore upload database */
    public function restore(Request $request)
    {
        if (checkRole('database_backup', 'add') != true) {
            return redirect()->back()->with('failure', 'You do not have permission to restore a backup');
        }

        $request->validate([
            'file' => 'required|file|mimetypes:text/plain,application/sql,application/octet-stream',
        ]);

        $file = $request->file('file');

        // Make sure the file is an SQL database dump
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return redirect()->back()->with('failure', 'Invalid file format. Please upload a valid SQL database dump.');
        }

        // Store the uploaded .sql file outside the web root
        $path = $file->store('temp');

        try {
            $sql = Storage::get($path);

            // Turn off foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Drop every existing table before importing the dump
            foreach (DB::select('SHOW TABLES') as $row) {
                foreach ((array) $row as $table) {
                    DB::statement('DROP TABLE IF EXISTS `' . str_replace('`', '', $table) . '`;');
                }
            }

            // Import the backup
            DB::unprepared($sql);

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Artisan::call('cache:clear');

            activityLog('Admin', 'Restored the database from an uploaded backup');

            return redirect()->back()->with('success', 'Database has been restored');
        } catch (\Exception $e) {
            return redirect()->back()->with('failure', 'Error during the database restore: ' . $e->getMessage());
        } finally {
            Storage::delete($path);
        }
    }
}
