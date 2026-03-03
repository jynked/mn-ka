<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class CleanHotFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:hot 
                            {--force : Принудительно удалить файл}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удалить файл public/hot (Vite dev server)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $basePath = base_path();
        $hotPath = $basePath . '/public/hot';
        
        $this->info('🧹 Проверка наличия файла public/hot...');
        
        if (!file_exists($hotPath)) {
            $this->info('✅ Файл public/hot не существует');
            return Command::SUCCESS;
        }
        
        $this->warn('⚠️  Файл public/hot найден. Удаляем...');
        
        // Способ 1: Через PHP
        if (is_file($hotPath)) {
            if (@unlink($hotPath)) {
                $this->info('✅ Файл удален через PHP');
                Log::info('Файл public/hot удален через команду clean:hot');
                return Command::SUCCESS;
            }
        } elseif (is_dir($hotPath)) {
            $this->deleteDirectory($hotPath);
            if (!file_exists($hotPath)) {
                $this->info('✅ Директория удалена через PHP');
                Log::info('Директория public/hot удалена через команду clean:hot');
                return Command::SUCCESS;
            }
        }
        
        // Способ 2: Через shell команды
        $escapedPath = escapeshellarg($hotPath);
        $publicPath = escapeshellarg($basePath . '/public');
        
        // Удаляем файл
        $rmFileProcess = Process::path($basePath)
            ->run("rm -f {$escapedPath} 2>/dev/null || true");
        
        // Удаляем директорию
        $rmDirProcess = Process::path($basePath)
            ->run("rm -rf {$escapedPath} 2>/dev/null || true");
        
        // Удаляем через find
        $findProcess = Process::path($basePath)
            ->run("find {$publicPath} -maxdepth 1 -name 'hot' -delete 2>/dev/null || true");
        
        // Проверяем результат
        if (!file_exists($hotPath)) {
            $this->info('✅ Файл успешно удален');
            Log::info('Файл public/hot удален через команду clean:hot (shell)');
            return Command::SUCCESS;
        }
        
        $this->error('❌ Не удалось удалить файл public/hot');
        Log::error('Не удалось удалить файл public/hot', [
            'path' => $hotPath,
            'exists' => file_exists($hotPath),
            'is_file' => is_file($hotPath),
            'is_dir' => is_dir($hotPath),
        ]);
        
        return Command::FAILURE;
    }
    
    /**
     * Рекурсивно удалить директорию
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
}

