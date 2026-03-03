<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CopyMediaFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:copy-from-old-project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скопировать все медиа файлы из старого проекта';

    protected $oldProjectPath;
    protected $oldDbConnection;

    public function __construct()
    {
        parent::__construct();
        
        $this->oldProjectPath = env('OLD_PROJECT_PATH', 'C:\OSPanel\domains\lagom');
        
        $this->oldDbConnection = [
            'driver' => 'mysql',
            'host' => env('OLD_DB_HOST', '127.0.0.1'),
            'port' => env('OLD_DB_PORT', '3306'),
            'database' => env('OLD_DB_DATABASE', 'lagom'),
            'username' => env('OLD_DB_USERNAME', 'root'),
            'password' => env('OLD_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Начало копирования медиа файлов из старого проекта...');

        try {
            $this->connectToOldDatabase();
            $mediaIds = $this->collectAllMediaIds();

            $this->info("📋 Найдено медиа файлов для копирования: " . count($mediaIds));

            $bar = $this->output->createProgressBar(count($mediaIds));
            $bar->start();

            $copied = 0;
            $failed = 0;
            $skipped = 0;

            foreach ($mediaIds as $mediaId) {
                $result = $this->copyMediaFile($mediaId);
                if ($result === 'copied') {
                    $copied++;
                } elseif ($result === 'skipped') {
                    $skipped++;
                } else {
                    $failed++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("✅ Скопировано файлов: {$copied}");
            if ($skipped > 0) {
                $this->info("ℹ️ Пропущено (уже существуют): {$skipped}");
            }
            if ($failed > 0) {
                $this->warn("⚠️ Не удалось скопировать файлов: {$failed}");
            }

            $this->info('✅ Копирование медиа файлов завершено!');
        } catch (\Exception $e) {
            $this->error('❌ Ошибка копирования: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function connectToOldDatabase(): void
    {
        config(['database.connections.old_db' => $this->oldDbConnection]);
        DB::purge('old_db');
        DB::reconnect('old_db');
        
        try {
            DB::connection('old_db')->select('SELECT 1');
            $this->info('✅ Подключение к старой БД установлено');
        } catch (\Exception $e) {
            throw new \Exception('Не удалось подключиться к старой БД: ' . $e->getMessage());
        }
    }

    protected function collectAllMediaIds(): array
    {
        $mediaIds = [];

        $productImages = DB::connection('old_db')
            ->table('products')
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->toArray();
        $productIcons = DB::connection('old_db')
            ->table('products')
            ->whereNotNull('icon_id')
            ->pluck('icon_id')
            ->toArray();

        $serviceImages = DB::connection('old_db')
            ->table('services')
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->toArray();
        $serviceIcons = DB::connection('old_db')
            ->table('services')
            ->whereNotNull('icon_id')
            ->pluck('icon_id')
            ->toArray();

        $caseImages = DB::connection('old_db')
            ->table('cases')
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->toArray();
        $caseIcons = DB::connection('old_db')
            ->table('cases')
            ->whereNotNull('icon_id')
            ->pluck('icon_id')
            ->toArray();

        $caseGalleryImages = DB::connection('old_db')
            ->table('cases_image')
            ->pluck('image_id')
            ->toArray();

        $allIds = array_merge(
            $productImages,
            $productIcons,
            $serviceImages,
            $serviceIcons,
            $caseImages,
            $caseIcons,
            $caseGalleryImages
        );

        $mediaIds = array_unique(array_filter($allIds));
        return array_values($mediaIds);
    }

    protected function copyMediaFile(int $mediaId): string
    {
        try {
            $oldMedia = DB::connection('old_db')
                ->table('media')
                ->where('id', $mediaId)
                ->first();

            if (!$oldMedia) {
                return 'failed';
            }

            $oldPath = $this->getOldMediaPath($oldMedia);
            if (!$oldPath || !file_exists($oldPath)) {
                return 'failed';
            }

            $category = $this->determineCategory($mediaId);
            $newDir = public_path("upload/{$category}");
            
            if (!File::exists($newDir)) {
                File::makeDirectory($newDir, 0755, true);
            }

            $extension = pathinfo($oldMedia->name ?? $oldMedia->original_name ?? 'file', PATHINFO_EXTENSION);
            if (empty($extension) && $oldMedia->extension) {
                $extension = $oldMedia->extension;
            }
            
            $newFileName = ($oldMedia->name ?? uniqid()) . '.' . $extension;
            $newPath = $newDir . '/' . $newFileName;

            if (file_exists($newPath)) {
                return 'skipped';
            }

            if (!copy($oldPath, $newPath)) {
                return 'failed';
            }

            return 'copied';
        } catch (\Exception $e) {
            return 'failed';
        }
    }

    protected function getOldMediaPath($oldMedia): ?string
    {
        $possiblePaths = [];

        if ($oldMedia->metadata) {
            $metadata = json_decode($oldMedia->metadata, true);
            if (isset($metadata['path'])) {
                $possiblePaths[] = $this->oldProjectPath . '/public/' . ltrim($metadata['path'], '/');
            }
        }

        if ($oldMedia->disk && $oldMedia->name) {
            $possiblePaths[] = $this->oldProjectPath . '/public/' . ltrim($oldMedia->disk, '/') . '/' . $oldMedia->name;
        }

        if ($oldMedia->name) {
            $possiblePaths[] = $this->oldProjectPath . '/public/upload/' . $oldMedia->name;
            $possiblePaths[] = $this->oldProjectPath . '/public/uploads/' . $oldMedia->name;
        }

        if ($oldMedia->original_name) {
            $possiblePaths[] = $this->oldProjectPath . '/public/upload/' . $oldMedia->original_name;
            $possiblePaths[] = $this->oldProjectPath . '/public/uploads/' . $oldMedia->original_name;
        }

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function determineCategory(int $mediaId): string
    {
        $isProductImage = DB::connection('old_db')
            ->table('products')
            ->where('image_id', $mediaId)
            ->exists();
        
        $isProductIcon = DB::connection('old_db')
            ->table('products')
            ->where('icon_id', $mediaId)
            ->exists();

        $isServiceImage = DB::connection('old_db')
            ->table('services')
            ->where('image_id', $mediaId)
            ->exists();
        
        $isServiceIcon = DB::connection('old_db')
            ->table('services')
            ->where('icon_id', $mediaId)
            ->exists();

        $isCaseImage = DB::connection('old_db')
            ->table('cases')
            ->where('image_id', $mediaId)
            ->exists();
        
        $isCaseIcon = DB::connection('old_db')
            ->table('cases')
            ->where('icon_id', $mediaId)
            ->exists();

        $isCaseGallery = DB::connection('old_db')
            ->table('cases_image')
            ->where('image_id', $mediaId)
            ->exists();

        if ($isProductImage || $isProductIcon) {
            return $isProductIcon ? 'icons' : 'products';
        }

        if ($isServiceImage || $isServiceIcon) {
            return $isServiceIcon ? 'icons' : 'services';
        }

        if ($isCaseImage || $isCaseIcon || $isCaseGallery) {
            return ($isCaseIcon) ? 'icons' : 'cases';
        }

        return 'general';
    }
}
