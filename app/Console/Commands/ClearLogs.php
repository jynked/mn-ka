<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear 
                            {--all : Очистить все файлы логов}
                            {--file= : Очистить конкретный файл лога (например: laravel.log)}
                            {--empty : Очистить только пустые файлы логов}
                            {--days= : Удалить логи старше указанного количества дней}
                            {--force : Пропустить подтверждение}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистить файлы логов Laravel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logsPath = storage_path('logs');

        if (!File::exists($logsPath)) {
            $this->error("Директория логов не найдена: {$logsPath}");
            return 1;
        }

        $options = [
            'all' => $this->option('all'),
            'file' => $this->option('file'),
            'empty' => $this->option('empty'),
            'days' => $this->option('days'),
            'confirm' => $this->option('force'),
        ];

        // Определяем действие
        if ($options['file']) {
            return $this->clearSpecificFile($logsPath, $options['file'], $options['confirm']);
        } elseif ($options['all']) {
            return $this->clearAllLogs($logsPath, $options['confirm']);
        } elseif ($options['empty']) {
            return $this->clearEmptyLogs($logsPath, $options['confirm']);
        } elseif ($options['days']) {
            return $this->clearOldLogs($logsPath, (int)$options['days'], $options['confirm']);
        } else {
            // По умолчанию - очистка основного файла laravel.log
            return $this->clearMainLog($logsPath, $options['confirm']);
        }
    }

    /**
     * Очистить основной файл лога (laravel.log)
     */
    protected function clearMainLog(string $logsPath, bool $skipConfirm): int
    {
        $logFile = $logsPath . '/laravel.log';

        if (!File::exists($logFile)) {
            $this->info('Файл laravel.log не найден.');
            return 0;
        }

        $fileSize = File::size($logFile);
        $fileSizeHuman = $this->formatBytes($fileSize);

        $this->info("Файл лога: laravel.log");
        $this->info("Размер: {$fileSizeHuman}");

        if (!$skipConfirm && !$this->confirm('Очистить файл laravel.log?', true)) {
            $this->info('Операция отменена.');
            return 0;
        }

        try {
            File::put($logFile, '');
            $this->info('✅ Файл laravel.log успешно очищен.');
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Ошибка при очистке файла: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Очистить конкретный файл лога
     */
    protected function clearSpecificFile(string $logsPath, string $fileName, bool $skipConfirm): int
    {
        // Предотвращаем удаление файлов вне директории логов
        $fileName = basename($fileName);
        $logFile = $logsPath . '/' . $fileName;

        if (!File::exists($logFile)) {
            $this->error("Файл {$fileName} не найден в директории логов.");
            return 1;
        }

        $fileSize = File::size($logFile);
        $fileSizeHuman = $this->formatBytes($fileSize);

        $this->info("Файл лога: {$fileName}");
        $this->info("Размер: {$fileSizeHuman}");

        if (!$skipConfirm && !$this->confirm("Очистить файл {$fileName}?", true)) {
            $this->info('Операция отменена.');
            return 0;
        }

        try {
            File::put($logFile, '');
            $this->info("✅ Файл {$fileName} успешно очищен.");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Ошибка при очистке файла: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Очистить все файлы логов
     */
    protected function clearAllLogs(string $logsPath, bool $skipConfirm): int
    {
        $logFiles = File::files($logsPath);
        
        if (empty($logFiles)) {
            $this->info('Файлы логов не найдены.');
            return 0;
        }

        $totalSize = 0;
        $fileList = [];

        foreach ($logFiles as $file) {
            $size = $file->getSize();
            $totalSize += $size;
            $fileList[] = [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($size),
            ];
        }

        $this->info('Найдено файлов логов: ' . count($fileList));
        $this->info('Общий размер: ' . $this->formatBytes($totalSize));

        if ($this->option('verbose')) {
            $this->table(['Файл', 'Размер'], $fileList);
        }

        if (!$skipConfirm && !$this->confirm('Очистить все файлы логов?', false)) {
            $this->info('Операция отменена.');
            return 0;
        }

        $clearedCount = 0;
        $errorCount = 0;

        foreach ($logFiles as $file) {
            try {
                File::put($file->getPathname(), '');
                $clearedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->warn("Ошибка при очистке {$file->getFilename()}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Очищено файлов: {$clearedCount}");
        if ($errorCount > 0) {
            $this->warn("⚠️  Ошибок: {$errorCount}");
        }

        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Очистить только пустые файлы логов
     */
    protected function clearEmptyLogs(string $logsPath, bool $skipConfirm): int
    {
        $logFiles = File::files($logsPath);
        $emptyFiles = [];

        foreach ($logFiles as $file) {
            if ($file->getSize() === 0) {
                $emptyFiles[] = $file;
            }
        }

        if (empty($emptyFiles)) {
            $this->info('Пустые файлы логов не найдены.');
            return 0;
        }

        $this->info('Найдено пустых файлов: ' . count($emptyFiles));

        if ($this->option('verbose')) {
            $fileList = array_map(function ($file) {
                return ['name' => $file->getFilename()];
            }, $emptyFiles);
            $this->table(['Файл'], $fileList);
        }

        if (!$skipConfirm && !$this->confirm('Удалить пустые файлы логов?', false)) {
            $this->info('Операция отменена.');
            return 0;
        }

        $deletedCount = 0;
        $errorCount = 0;

        foreach ($emptyFiles as $file) {
            try {
                File::delete($file->getPathname());
                $deletedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->warn("Ошибка при удалении {$file->getFilename()}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Удалено пустых файлов: {$deletedCount}");
        if ($errorCount > 0) {
            $this->warn("⚠️  Ошибок: {$errorCount}");
        }

        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Очистить логи старше указанного количества дней
     */
    protected function clearOldLogs(string $logsPath, int $days, bool $skipConfirm): int
    {
        if ($days < 1) {
            $this->error('Количество дней должно быть больше 0.');
            return 1;
        }

        $logFiles = File::files($logsPath);
        $cutoffDate = now()->subDays($days);
        $oldFiles = [];

        foreach ($logFiles as $file) {
            $modifiedTime = $file->getMTime();
            $fileDate = \Carbon\Carbon::createFromTimestamp($modifiedTime);
            
            if ($fileDate->lt($cutoffDate)) {
                $oldFiles[] = $file;
            }
        }

        if (empty($oldFiles)) {
            $this->info("Логи старше {$days} дней не найдены.");
            return 0;
        }

        $totalSize = 0;
        $fileList = [];

        foreach ($oldFiles as $file) {
            $size = $file->getSize();
            $totalSize += $size;
            $modifiedTime = \Carbon\Carbon::createFromTimestamp($file->getMTime());
            $fileList[] = [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($size),
                'modified' => $modifiedTime->format('Y-m-d H:i:s'),
            ];
        }

        $this->info("Найдено файлов старше {$days} дней: " . count($oldFiles));
        $this->info('Общий размер: ' . $this->formatBytes($totalSize));

        if ($this->option('verbose')) {
            $this->table(['Файл', 'Размер', 'Изменен'], $fileList);
        }

        if (!$skipConfirm && !$this->confirm("Удалить логи старше {$days} дней?", false)) {
            $this->info('Операция отменена.');
            return 0;
        }

        $deletedCount = 0;
        $errorCount = 0;

        foreach ($oldFiles as $file) {
            try {
                File::delete($file->getPathname());
                $deletedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->warn("Ошибка при удалении {$file->getFilename()}: {$e->getMessage()}");
            }
        }

        $this->info("✅ Удалено файлов: {$deletedCount}");
        if ($errorCount > 0) {
            $this->warn("⚠️  Ошибок: {$errorCount}");
        }

        return $errorCount > 0 ? 1 : 0;
    }

    /**
     * Форматировать размер файла в читаемый формат
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

