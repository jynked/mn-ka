<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Media;
use Illuminate\Database\Seeder;

/**
 * Seeder для обновления всех записей в media, у которых не указана папка
 */
class UpdateMediaFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало обновления папок для медиа файлов...');

        // Получаем общую папку
        $commonFolder = Folder::withoutUserScope()->where('slug', 'common')->first();
        
        if (!$commonFolder) {
            // Если папка не найдена, создаем её
            $commonFolder = Folder::withoutUserScope()->create([
                'name' => 'Общая',
                'slug' => 'common',
                'src' => 'folder',
                'parent_id' => null,
                'position' => 0,
                'protected' => true,
            ]);
            
            $this->command->info("✓ Создана общая папка (ID: {$commonFolder->id})");
        } else {
            $this->command->info("✓ Найдена общая папка (ID: {$commonFolder->id})");
        }

        // Находим все записи media, у которых folder_id = null
        $mediaWithoutFolder = Media::withoutUserScope()
            ->whereNull('folder_id')
            ->get();

        $count = $mediaWithoutFolder->count();
        
        if ($count === 0) {
            $this->command->info('✅ Все медиа файлы уже имеют папку.');
            return;
        }

        $this->command->info("📋 Найдено медиа файлов без папки: {$count}");

        // Обновляем записи
        $updated = Media::withoutUserScope()
            ->whereNull('folder_id')
            ->update(['folder_id' => $commonFolder->id]);

        $this->command->info("✅ Обновлено медиа файлов: {$updated}");
        $this->command->info('✅ Обновление папок для медиа файлов завершено!');
    }
}





