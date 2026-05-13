<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DbRespaldo extends Command
{
    protected $signature = 'db:respaldo
                            {--keep=7 : Número de backups a conservar}';

    protected $description = 'Genera un respaldo MySQL de la base de datos';

    public function handle(): int
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            $this->error('Este comando solo funciona con MySQL.');

            return self::FAILURE;
        }

        $config = $connection->getConfig();
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        $keep = max(1, (int) $this->option('keep'));
        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = "respaldo_{$database}_{$timestamp}.sql.gz";
        $path = 'backups/'.$filename;

        Storage::makeDirectory('backups');

        $command = [
            'mysqldump',
            '-h', $host,
            '-P', (string) $port,
            '-u', $username,
            '--routines',
            '--single-transaction',
            '--opt',
            $database,
        ];

        $this->info("Generando respaldo: {$filename}");

        $process = new Process($command);
        $process->setEnv(['MYSQL_PWD' => $password]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Error al generar el respaldo: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        Storage::put($path, gzencode($process->getOutput()));

        $fullPath = Storage::path($path);
        $size = round(filesize($fullPath) / 1024 / 1024, 2);
        $this->info("Respaldo completado: {$filename} ({$size} MB)");

        $this->pruneOldBackups($keep);

        return self::SUCCESS;
    }

    private function pruneOldBackups(int $keep): void
    {
        $files = Storage::files('backups');
        $backupFiles = [];

        foreach ($files as $file) {
            if (str_starts_with(basename($file), 'respaldo_')) {
                $backupFiles[] = $file;
            }
        }

        if (count($backupFiles) <= $keep) {
            return;
        }

        sort($backupFiles);

        $toDelete = array_slice($backupFiles, 0, count($backupFiles) - $keep);

        foreach ($toDelete as $file) {
            Storage::delete($file);
            $this->line("Eliminado respaldo antiguo: {$file}");
        }
    }
}
