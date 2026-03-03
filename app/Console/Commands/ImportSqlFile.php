<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSqlFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import-sql 
                            {file : Путь к SQL файлу для импорта}
                            {--skip-fk : Пропустить создание foreign keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импортировать SQL файл в базу данных с правильной обработкой foreign keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        // Проверяем существование файла
        if (!File::exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $this->info("Импорт SQL файла: {$filePath}");
        $this->info("Размер файла: " . $this->formatBytes(File::size($filePath)));

        if (!$this->confirm('Продолжить импорт?', true)) {
            $this->info('Импорт отменен.');
            return 0;
        }

        try {
            // Отключаем проверку внешних ключей
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");

            $this->info('Отключена проверка foreign keys...');

            // Читаем SQL файл
            $sql = File::get($filePath);

            if ($this->option('skip-fk')) {
                // Удаляем команды ALTER TABLE ADD CONSTRAINT
                $this->info('Удаление foreign key constraints из SQL...');
                
                // Удаляем ALTER TABLE с ADD CONSTRAINT
                $sql = preg_replace('/ALTER TABLE\s+[^;]*ADD CONSTRAINT[^;]*FOREIGN KEY[^;]*;/is', '', $sql);
                
                // Удаляем CONSTRAINT из CREATE TABLE
                $sql = preg_replace('/,\s*CONSTRAINT\s+[^\s]+\s+FOREIGN\s+KEY[^,)]*/is', '', $sql);
                $sql = preg_replace('/,\s*CONSTRAINT\s+[^\s]+\s+REFERENCES[^,)]*/is', '', $sql);
            }

            // Разбиваем SQL на отдельные запросы
            $queries = $this->splitSqlQueries($sql);

            $this->info('Выполнение SQL запросов...');
            $progressBar = $this->output->createProgressBar(count($queries));
            $progressBar->start();

            $executed = 0;
            $errors = 0;

            foreach ($queries as $query) {
                $query = trim($query);
                
                // Пропускаем пустые запросы и комментарии
                if (empty($query) || 
                    strpos($query, '--') === 0 || 
                    strpos($query, '/*') === 0) {
                    $progressBar->advance();
                    continue;
                }

                try {
                    DB::statement($query);
                    $executed++;
                } catch (\Exception $e) {
                    $errors++;
                    // Пропускаем ошибки с foreign keys если skip-fk
                    if (!$this->option('skip-fk') || 
                        strpos($e->getMessage(), 'foreign key') === false) {
                        $this->newLine();
                        $this->warn("Ошибка: " . $e->getMessage());
                    }
                }
                
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Включаем проверку внешних ключей обратно
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info("✅ Импорт завершен!");
            $this->info("Выполнено запросов: {$executed}");
            if ($errors > 0) {
                $this->warn("Ошибок: {$errors}");
            }

            return 0;
        } catch (\Exception $e) {
            // Включаем проверку внешних ключей в случае ошибки
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $fkError) {
                // Игнорируем
            }

            $this->error("❌ Ошибка при импорте: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Разбить SQL на отдельные запросы
     */
    protected function splitSqlQueries(string $sql): array
    {
        // Удаляем комментарии
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $sql = preg_replace('/--.*?$/m', '', $sql);

        // Разбиваем по точкам с запятой, учитывая строки и функции
        $queries = [];
        $current = '';
        $inString = false;
        $stringChar = null;

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            // Обработка строк
            if (($char === '"' || $char === "'" || $char === '`') && $prev !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                    $stringChar = null;
                }
            }

            $current .= $char;

            // Если нашли точку с запятой вне строки - это конец запроса
            if ($char === ';' && !$inString) {
                $queries[] = trim($current);
                $current = '';
            }
        }

        // Добавляем последний запрос если есть
        if (!empty(trim($current))) {
            $queries[] = trim($current);
        }

        return array_filter($queries);
    }

    /**
     * Форматировать размер файла
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

