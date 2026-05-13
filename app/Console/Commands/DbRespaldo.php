<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DbRespaldo extends Command
{
    protected $signature = 'db:respaldo
        {--keep=7 : Días a conservar backups}';

    protected $description = 'Crea un respaldo MySQL y elimina los antiguos';

    public function handle(): int
    {
        $db     = config('database.connections.mysql.database');
        $user   = config('database.connections.mysql.username');
        $pass   = config('database.connections.mysql.password');
        $host   = config('database.connections.mysql.host');
        $port   = config('database.connections.mysql.port');

        if (!$db) {
            $this->error('La conexión MySQL no está configurada en .env');
            return Command::FAILURE;
        }

        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app/backups')]);
        if (!$disk->exists('')) {
            $disk->makeDirectory('');
        }

        $filename = 'respaldo_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path("app/backups/{$filename}");

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($path)
        );

        $output = null;
        $resultCode = null;
        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error("Error al ejecutar mysqldump (código {$resultCode})");
            return Command::FAILURE;
        }

        $size = filesize($path);
        $this->info("Respaldo creado: {$filename} (" . number_format($size / 1024, 1) . " KB)");

        $keep = max(1, (int) $this->option('keep'));
        $files = collect($disk->files())->filter(fn($f) => str_starts_with($f, 'respaldo_'))->sort();
        $toDelete = $files->slice(0, -$keep);

        foreach ($toDelete as $f) {
            $disk->delete($f);
            $this->line("Eliminado backup antiguo: {$f}");
        }

        return Command::SUCCESS;
    }
}
