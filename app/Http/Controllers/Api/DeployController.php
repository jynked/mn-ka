<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use ZipArchive;

class DeployController extends Controller
{
    protected $phpPath;
    protected $phpVersion;
    protected $basePath;

    /**
     * Таблицы, которые нужно исключить из синхронизации
     */
    protected $excludedTables = [
        'users',
        'telegraph_chats',
        'telegraph_bots',
        'telegram_settings',
        'telegram_admin_requests',
        'sessions',
        'role_user',
        'roles',
        'request_history',
        'product_requests',
        'personal_access_tokens',
        'password_reset_tokens',
        'notifications',
        'job_batches',
        'jobs',
        'migrations',
    ];

    public function __construct()
    {
        $this->basePath = base_path();
    }

    /**
     * Выполнить деплой на сервере
     */
    public function deploy(Request $request)
    {
        $startTime = microtime(true);
        Log::info('🚀 Начало деплоя', [
            'ip' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        $result = [
            'success' => false,
            'message' => '',
            'data' => [],
        ];

        try {
            // Определяем PHP путь
            $this->phpPath = $this->getPhpPath();
            $this->phpVersion = $this->getPhpVersion();

            Log::info("Используется PHP: {$this->phpPath} (версия: {$this->phpVersion})");

            // 0. Очистка файлов разработки в начале (на случай остатков)
            $this->cleanDevelopmentFiles();

            // 1. Git pull (внутри handleGitPull будет настроена безопасная директория)
            $gitPullResult = $this->handleGitPull();
            
            // Получаем текущий commit hash ПОСЛЕ настройки безопасной директории
            $oldCommitHash = $this->getCurrentCommitHash();
            $result['data']['git_pull'] = $gitPullResult['status'];
            if (!$gitPullResult['success']) {
                throw new \Exception("Ошибка git pull: {$gitPullResult['error']}");
            }

            // 2. Composer install
            $composerResult = $this->handleComposerInstall();
            $result['data']['composer_install'] = $composerResult['status'];
            if (!$composerResult['success']) {
                throw new \Exception("Ошибка composer install: {$composerResult['error']}");
            }

            // 2.5. Очистка кешей после composer install (важно: удаляет кеш провайдеров dev-пакетов)
            $this->clearPackageDiscoveryCache();

            // 3. Миграции
            $migrationsResult = $this->runMigrations();
            $result['data']['migrations'] = $migrationsResult;
            if ($migrationsResult['status'] !== 'success') {
                throw new \Exception("Ошибка миграций: {$migrationsResult['error']}");
            }

            // 3.5. Проверка и принудительное выполнение конкретной миграции перед seeders
            $runSeeders = $request->input('run_seeders', false);
            if ($runSeeders) {
                // Проверяем, существует ли поле html_content в таблице services
                try {
                    $hasHtmlContent = \Illuminate\Support\Facades\Schema::hasColumn('services', 'html_content');
                    if (!$hasHtmlContent) {
                        Log::info('Поле html_content не найдено в таблице services, выполняем миграцию принудительно...');
                        // Выполняем конкретную миграцию принудительно
                        $migrationProcess = Process::path($this->basePath)
                            ->run("{$this->phpPath} artisan migrate --path=database/migrations/2025_12_18_193853_add_html_content_to_services_table.php --force");
                        
                        if ($migrationProcess->successful()) {
                            Log::info('Миграция html_content выполнена успешно');
                        } else {
                            Log::warning('Ошибка при выполнении миграции html_content: ' . $migrationProcess->errorOutput());
                        }
                    } else {
                        Log::info('Поле html_content уже существует в таблице services');
                    }
                } catch (\Exception $e) {
                    Log::warning('Ошибка при проверке поля html_content: ' . $e->getMessage());
                    // Пытаемся выполнить миграцию в любом случае
                    try {
                        $migrationProcess = Process::path($this->basePath)
                            ->run("{$this->phpPath} artisan migrate --path=database/migrations/2025_12_18_193853_add_html_content_to_services_table.php --force");
                        Log::info('Миграция html_content выполнена (fallback)');
                    } catch (\Exception $e2) {
                        Log::error('Критическая ошибка при выполнении миграции: ' . $e2->getMessage());
                    }
                }
            }
            
            // 3.6. Выполнение seeders (только если явно запрошено)
            if ($runSeeders) {
                $seedersResult = $this->runSeeders();
                $result['data']['seeders'] = $seedersResult;
                Log::info('Seeders выполнены по запросу');
            } else {
                $result['data']['seeders'] = [
                    'status' => 'skipped',
                    'message' => 'Seeders пропущены (используйте --with-seed для выполнения)',
                ];
                Log::info('Seeders пропущены (не указан флаг run_seeders)');
            }

            // 4. Очистка временных файлов разработки
            $this->cleanDevelopmentFiles();

            // 5. Очистка кешей
            $cacheResult = $this->clearAllCaches();
            $result['data']['cache_cleared'] = $cacheResult['success'];

            // 6. Оптимизация
            $optimizeResult = $this->optimizeApplication();
            $result['data']['optimized'] = $optimizeResult['success'];

            // 7. Финальная очистка файлов разработки (в конце, после всех операций)
            $this->cleanDevelopmentFiles();

            // 8. Запускаем Artisan команду для очистки hot файла (дополнительная проверка)
            try {
                $cleanHotProcess = Process::path($this->basePath)
                    ->run("{$this->phpPath} artisan clean:hot --force");

                if ($cleanHotProcess->successful()) {
                    Log::info('Файл public/hot очищен через Artisan команду');
                }
            } catch (\Exception $e) {
                Log::warning('Ошибка при запуске clean:hot', ['error' => $e->getMessage()]);
            }

            // Получаем новый commit hash
            $newCommitHash = $this->getCurrentCommitHash();

            // Сохраняем время последнего deploy (для проверки в sync-sql-file)
            $this->saveLastDeployTime();

            // Формируем успешный ответ
            $result['success'] = true;
            $result['message'] = 'Деплой успешно завершен';
            $result['data'] = array_merge($result['data'], [
                'php_version' => $this->phpVersion,
                'php_path' => $this->phpPath,
                'old_commit_hash' => $oldCommitHash,
                'new_commit_hash' => $newCommitHash,
                'commit_changed' => $oldCommitHash !== $newCommitHash,
                'deployed_at' => now()->toDateTimeString(),
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ]);

            Log::info('✅ Деплой успешно завершен', $result['data']);

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['data']['error'] = $e->getMessage();
            $result['data']['trace'] = config('app.debug') ? $e->getTraceAsString() : null;
            $result['data']['deployed_at'] = now()->toDateTimeString();
            $result['data']['duration_seconds'] = round(microtime(true) - $startTime, 2);

            Log::error('❌ Ошибка деплоя', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Определить путь к PHP
     */
    protected function getPhpPath(): string
    {
        // 1. Проверить явно указанный путь в .env
        $phpPath = env('PHP_PATH');
        if ($phpPath && $this->isPhpExecutable($phpPath)) {
            return $phpPath;
        }

        // 2. Попробовать автоматически найти PHP (сначала полные пути, потом короткие)
        $possiblePaths = [
            '/usr/local/bin/php8.2',
            '/usr/local/bin/php8.3',
            '/usr/local/bin/php8.1',
            '/usr/bin/php8.2',
            '/usr/bin/php8.3',
            '/usr/bin/php8.1',
            'php8.2',
            'php8.3',
            'php8.1',
            'php',
        ];
        
        foreach ($possiblePaths as $path) {
            if ($this->isPhpExecutable($path)) {
                // Проверяем версию - нужна минимум 8.1
                $version = $this->getPhpVersionFromPath($path);
                if ($version && version_compare($version, '8.1.0', '>=')) {
                    return $path;
                }
            }
        }

        // 3. Fallback на 'php' (с предупреждением)
        Log::warning('Не найдена подходящая версия PHP (требуется >= 8.1), используется системный php');
        return 'php';
    }

    /**
     * Получить версию PHP из пути
     */
    protected function getPhpVersionFromPath(string $path): ?string
    {
        try {
            exec("{$path} --version 2>&1", $output, $returnCode);
            if ($returnCode === 0 && isset($output[0])) {
                preg_match('/PHP\s+(\d+\.\d+\.\d+)/', $output[0], $matches);
                return $matches[1] ?? null;
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return null;
    }

    /**
     * Проверить доступность PHP
     */
    protected function isPhpExecutable(string $path): bool
    {
        try {
            // Проверка через which (Unix-like)
            $result = shell_exec("which {$path} 2>/dev/null");
            if ($result && trim($result)) {
                return true;
            }

            // Проверка через exec (версия PHP)
            exec("{$path} --version 2>&1", $output, $returnCode);
            return $returnCode === 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получить версию PHP
     */
    protected function getPhpVersion(): string
    {
        $version = $this->getPhpVersionFromPath($this->phpPath);
        return $version ?? 'unknown';
    }

    /**
     * Выполнить git pull
     */
    protected function handleGitPull(): array
    {
        try {
            // Настройка безопасной директории для git (решает проблему dubious ownership)
            // ВАЖНО: Это должно быть первым шагом перед всеми git командами
            $this->ensureGitSafeDirectory();
            
            // Определяем безопасную директорию для всех git команд
            $safeDirectoryPath = escapeshellarg($this->basePath);
            $gitEnv = [
                'GIT_CEILING_DIRECTORIES' => dirname($this->basePath),
            ];
            $gitBaseCmd = "git -c safe.directory={$safeDirectoryPath}";

            // Проверяем статус git перед pull
            $statusProcess = Process::path($this->basePath)
                ->env($gitEnv)
                ->run("{$gitBaseCmd} status --porcelain 2>&1");

            $hasChanges = !empty(trim($statusProcess->output()));

            // Если есть локальные изменения, сохраняем их в stash
            if ($hasChanges) {
                Log::info('Обнаружены локальные изменения, сохраняем в stash...');
                $stashProcess = Process::path($this->basePath)
                    ->env($gitEnv)
                    ->run("{$gitBaseCmd} stash push -m \"Auto-stash before deploy " . now()->toDateTimeString() . "\" 2>&1");

                if (!$stashProcess->successful()) {
                    Log::warning('Не удалось сохранить изменения в stash', [
                        'error' => $stashProcess->errorOutput(),
                    ]);
                }
            }

            // Сбрасываем неотслеживаемые файлы, которые могут конфликтовать
            $this->cleanUntrackedFiles();

            // Получаем текущий commit перед обновлением для сравнения
            $beforeCommit = $this->getCurrentCommitHash();
            Log::info("📦 Commit до обновления: " . ($beforeCommit ?: 'не определен'));

            // Проверяем текущий статус Git
            $statusOutput = Process::path($this->basePath)
                ->env($gitEnv)
                ->run("{$gitBaseCmd} status --short 2>&1");
            Log::info("📊 Текущий статус Git: " . trim($statusOutput->output() ?: 'чисто'));

            // 1. Сначала получаем последние изменения из репозитория
            Log::info("📥 Выполняем git fetch origin main...");
            $fetchProcess = Process::path($this->basePath)
                ->env($gitEnv)
                ->run("{$gitBaseCmd} fetch origin main 2>&1");

            if (!$fetchProcess->successful()) {
                Log::warning('⚠️ Не удалось выполнить git fetch', [
                    'output' => $fetchProcess->output(),
                    'error' => $fetchProcess->errorOutput(),
                ]);
            } else {
                Log::info('✅ Git fetch выполнен успешно', [
                    'output' => trim($fetchProcess->output() ?: 'нет вывода'),
                ]);
            }

            // 2. Проверяем, есть ли новые коммиты
            $checkAheadProcess = Process::path($this->basePath)
                ->env($gitEnv)
                ->run("{$gitBaseCmd} rev-list HEAD..origin/main --count 2>&1");
            $commitsAhead = trim($checkAheadProcess->output() ?: '0');
            Log::info("📊 Новых коммитов для загрузки: {$commitsAhead}");

            // 3. Сбрасываем локальную ветку на origin/main (принудительное обновление)
            Log::info("🔄 Выполняем git reset --hard origin/main...");
            $process = Process::path($this->basePath)
                ->env($gitEnv)
                ->run("{$gitBaseCmd} reset --hard origin/main 2>&1");

            Log::info("Git reset output: " . trim($process->output() ?: 'нет вывода'));
            if ($process->errorOutput()) {
                Log::warning("Git reset errors: " . trim($process->errorOutput()));
            }

            if (!$process->successful()) {
                Log::warning('Git reset --hard не удался, пробуем git pull', [
                    'error' => $process->errorOutput(),
                ]);

                // Если reset не удался, пробуем обычный pull
                $process = Process::path($this->basePath)
                    ->env($gitEnv)
                    ->run("{$gitBaseCmd} pull origin main --no-rebase --force 2>&1");
            }

            // 3. Получаем новый commit после обновления
            $afterCommit = $this->getCurrentCommitHash();
            Log::info("📦 Commit после обновления: " . ($afterCommit ?: 'не определен'));

            // 4. Проверяем, обновились ли файлы
            if ($beforeCommit && $afterCommit && $beforeCommit !== $afterCommit) {
                Log::info("✅ Код успешно обновлен: {$beforeCommit} -> {$afterCommit}");

                // Показываем измененные файлы
                try {
                    $diffProcess = Process::path($this->basePath)
                        ->env($gitEnv)
                        ->run("{$gitBaseCmd} diff --name-only {$beforeCommit} {$afterCommit} 2>&1");

                    $changedFiles = array_filter(explode("\n", trim($diffProcess->output())));
                    if (!empty($changedFiles)) {
                        $fileList = implode(', ', array_slice($changedFiles, 0, 10));
                        if (count($changedFiles) > 10) {
                            $fileList .= ' ... (всего ' . count($changedFiles) . ' файлов)';
                        }
                        Log::info("📝 Обновленные файлы: {$fileList}");
                    }
                } catch (\Exception $e) {
                    Log::warning('Не удалось получить список измененных файлов', [
                        'error' => $e->getMessage(),
                    ]);
                }
            } elseif ($beforeCommit && $afterCommit && $beforeCommit === $afterCommit) {
                Log::info("ℹ️ Код уже актуален, изменений нет");
            } else {
                Log::warning("⚠️ Не удалось определить состояние коммитов", [
                    'before' => $beforeCommit,
                    'after' => $afterCommit,
                    'message' => 'Возможно, Git репозиторий не инициализирован или есть проблемы с доступом',
                ]);

                // Дополнительная проверка: проверяем, что это Git репозиторий
                $gitCheckProcess = Process::path($this->basePath)
                    ->env($gitEnv)
                    ->run("{$gitBaseCmd} rev-parse --is-inside-work-tree 2>&1");

                if (!$gitCheckProcess->successful() || trim($gitCheckProcess->output()) !== 'true') {
                    Log::error("❌ Это не Git репозиторий! Путь: {$this->basePath}");
                } else {
                    Log::info("✅ Это Git репозиторий, но commit hash не определен");
                }
            }

            // 5. Дополнительная проверка: список последних коммитов
            try {
                $logProcess = Process::path($this->basePath)
                    ->env($gitEnv)
                    ->run("{$gitBaseCmd} log --oneline -3 2>&1");
                $lastCommits = trim($logProcess->output());
                if ($lastCommits) {
                    Log::info("📋 Последние 3 коммита:\n{$lastCommits}");
                }
            } catch (\Exception $e) {
                // Ignore
            }

            if ($process->successful()) {
                return [
                    'success' => true,
                    'status' => 'success',
                    'output' => $process->output(),
                    'had_local_changes' => $hasChanges,
                ];
            }

            return [
                'success' => false,
                'status' => 'error',
                'error' => $process->errorOutput() ?: $process->output(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Очистить неотслеживаемые файлы, которые могут конфликтовать
     */
    protected function cleanUntrackedFiles(): void
    {
        try {
            // Получаем список неотслеживаемых файлов
            $safeDirectoryPath = escapeshellarg($this->basePath);
            $gitEnv = [
                'GIT_CEILING_DIRECTORIES' => dirname($this->basePath),
            ];
            $gitBaseCmd = "git -c safe.directory={$safeDirectoryPath}";
            $untrackedProcess = Process::path($this->basePath)
                ->env($gitEnv)
                ->run("{$gitBaseCmd} ls-files --others --exclude-standard 2>&1");

            $untrackedFiles = array_filter(explode("\n", trim($untrackedProcess->output())));

            if (empty($untrackedFiles)) {
                return;
            }

            // Удаляем только файлы, которые точно должны быть в репозитории
            $filesToRemove = [
                'DEPLOY_NEXT_STEPS.md',
                'DEPLOY_SYSTEM_PLAN.md',
                'app/Console/Commands/ClearLogs.php',
                'app/Console/Commands/Deploy.php',
                'app/Http/Controllers/Api/DeployController.php',
                'app/Http/Middleware/VerifyDeployToken.php',
            ];

            foreach ($filesToRemove as $file) {
                $filePath = $this->basePath . '/' . $file;
                if (in_array($file, $untrackedFiles) && file_exists($filePath)) {
                    Log::info("Удаляем неотслеживаемый файл: {$file}");
                    @unlink($filePath);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Ошибка при очистке неотслеживаемых файлов', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Настроить безопасную директорию для git
     * Решает проблему "detected dubious ownership in repository"
     */
    protected function ensureGitSafeDirectory(): void
    {
        try {
            // Сначала пытаемся добавить в глобальную конфигурацию
            // Используем кавычки для экранирования пути с пробелами
            $escapedPath = escapeshellarg($this->basePath);
            $process = Process::path($this->basePath)
                ->run("git config --global --add safe.directory {$escapedPath} 2>&1");

            // Если глобально не получилось, пробуем локально
            if (!$process->successful()) {
                $processLocal = Process::path($this->basePath)
                    ->run("git config --local --add safe.directory {$escapedPath} 2>&1");

                // Если и локально не получилось, используем переменную окружения
                if (!$processLocal->successful()) {
                    // Используем переменную окружения для текущей сессии
                    putenv("GIT_CEILING_DIRECTORIES=" . dirname($this->basePath));

                    // Альтернативный способ - через GIT_CONFIG
                    $gitConfig = "safe.directory={$this->basePath}";
                    putenv("GIT_CONFIG_GLOBAL={$gitConfig}");
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки настройки - возможно, уже настроено или нет прав
            Log::warning('Не удалось настроить safe.directory для git', [
                'path' => $this->basePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Выполнить composer install
     */
    protected function handleComposerInstall(): array
    {
        try {
            // Получаем путь к composer
            $composerPath = $this->getComposerPath();
            
            Log::info("Используется composer: {$composerPath}");
            Log::info("Проверка существования composer: " . (file_exists($composerPath) ? 'да' : 'нет'));
            Log::info("basePath: {$this->basePath}");
            Log::info("Проверка composer.phar в проекте: " . (file_exists($this->basePath . '/composer.phar') ? 'да' : 'нет'));

            // Определяем HOME директорию (для composer)
            // Попробуем получить из пользователя или использовать базовую директорию
            $homeDir = getenv('HOME');
            if (!$homeDir) {
                // Попробуем определить по пути проекта или использовать временную директорию
                $projectUser = posix_getpwuid(posix_geteuid());
                $homeDir = $projectUser['dir'] ?? '/tmp';
            }

            // Используем PHP 8.2 для запуска composer
            // Добавляем --no-scripts временно, чтобы избежать проблем с prePackageUninstall
            // Затем запустим скрипты отдельно после успешной установки
            $command = "{$this->phpPath} {$composerPath} install --no-dev --optimize-autoloader --no-interaction --no-scripts";
            
            Log::info("Выполняется команда: {$command}");

            // Устанавливаем переменные окружения для composer и увеличиваем таймаут
            $process = Process::path($this->basePath)
                ->timeout(600) // 10 минут для composer install
                ->env([
                    'HOME' => $homeDir,
                    'COMPOSER_HOME' => $homeDir . '/.composer',
                    'COMPOSER_DISABLE_XDEBUG_WARN' => '1',
                ])
                ->run($command);

            // Laravel автоматически запускает нужные скрипты через post-autoload-dump
            // при выполнении composer install, поэтому дополнительный вызов не нужен

            if ($process->successful()) {
                return [
                    'success' => true,
                    'status' => 'success',
                    'output' => $process->output(),
                ];
            }

            return [
                'success' => false,
                'status' => 'error',
                'error' => $process->errorOutput() ?: $process->output(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Получить путь к composer
     */
    protected function getComposerPath(): string
    {
        // 1. Проверить явно указанный путь в .env
        $composerPath = env('COMPOSER_PATH');
        if ($composerPath && file_exists($composerPath)) {
            Log::info("Composer найден через COMPOSER_PATH: {$composerPath}");
            return $composerPath;
        }

        // 2. Проверить composer.phar в корне проекта (самый надежный вариант)
        $projectComposer = $this->basePath . '/composer.phar';
        if (file_exists($projectComposer)) {
            Log::info("Composer найден в проекте: {$projectComposer}");
            return $projectComposer;
        }

        Log::warning("composer.phar не найден в проекте: {$projectComposer}");

        // 3. Попробовать установить composer.phar автоматически
        $installed = $this->installComposerPhar();
        if ($installed && file_exists($projectComposer)) {
            Log::info("Composer установлен автоматически: {$projectComposer}");
            return $projectComposer;
        }

        // 4. Попробовать найти composer в стандартных местах
        $possiblePaths = [
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            'composer', // Последняя попытка - использовать из PATH
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'composer') {
                // Для 'composer' проверяем через which
                $whichProcess = Process::run('which composer');
                if ($whichProcess->successful() && trim($whichProcess->output())) {
                    $foundPath = trim($whichProcess->output());
                    Log::info("Composer найден через which: {$foundPath}");
                    return $foundPath;
                }
            } else {
                if (file_exists($path)) {
                    Log::info("Composer найден в стандартном месте: {$path}");
                    return $path;
                }
            }
        }

        // 5. Fallback на 'composer' (будет ошибка, если не найден)
        Log::error("Composer не найден ни в одном из стандартных мест");
        return 'composer';
    }

    /**
     * Установить composer.phar в проект
     */
    protected function installComposerPhar(): bool
    {
        try {
            $composerPharPath = $this->basePath . '/composer.phar';
            
            // Если уже установлен, не устанавливаем снова
            if (file_exists($composerPharPath)) {
                return true;
            }

            Log::info('Попытка автоматической установки composer.phar...');

            // Скачиваем установщик
            $installerPath = $this->basePath . '/composer-setup.php';
            $installerContent = @file_get_contents('https://getcomposer.org/installer');
            
            if ($installerContent === false) {
                Log::warning('Не удалось скачать composer installer');
                return false;
            }

            file_put_contents($installerPath, $installerContent);

            // Запускаем установщик
            $command = "{$this->phpPath} {$installerPath} --install-dir={$this->basePath} --filename=composer.phar";
            $process = Process::path($this->basePath)
                ->timeout(120)
                ->run($command);

            // Удаляем установщик
            if (file_exists($installerPath)) {
                @unlink($installerPath);
            }

            if ($process->successful() && file_exists($composerPharPath)) {
                // Делаем файл исполняемым
                @chmod($composerPharPath, 0755);
                Log::info('composer.phar успешно установлен в проект');
                return true;
            }

            Log::warning('Не удалось установить composer.phar: ' . ($process->errorOutput() ?: $process->output()));
            return false;

        } catch (\Exception $e) {
            Log::warning('Ошибка при установке composer.phar: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Выполнить миграции
     */
    protected function runMigrations(): array
    {
        try {
            $process = Process::path($this->basePath)
                ->run("{$this->phpPath} artisan migrate --force");

            if ($process->successful()) {
                // Парсим вывод для определения количества миграций
                $output = $process->output();
                // Ищем разные форматы вывода миграций
                preg_match_all('/Migrating:\s+(\d{4}_\d{2}_\d{2}_\d{6}_[\w_]+)/', $output, $matches1);
                preg_match_all('/DONE\s+(\d{4}_\d{2}_\d{2}_\d{6}_[\w_]+)/', $output, $matches2);
                preg_match_all('/(\d{4}_\d{2}_\d{2}_\d{6}_[\w_]+)\s+\.+.*DONE/', $output, $matches3);
                
                $migrationsRun = max(
                    count($matches1[0]),
                    count($matches2[0]),
                    count($matches3[0])
                );
                
                // Также проверяем, есть ли в выводе информация о выполненных миграциях
                if (stripos($output, 'migrated') !== false || stripos($output, 'migrating') !== false) {
                    // Если есть упоминания о миграциях, но не нашли точное количество
                    if ($migrationsRun === 0 && (stripos($output, 'nothing to migrate') === false && stripos($output, 'nothing to migrate') === false)) {
                        // Возможно, миграции были выполнены, но в другом формате
                        $migrationsRun = 1; // Предполагаем, что хотя бы одна миграция была
                    }
                }

                return [
                    'status' => 'success',
                    'migrations_run' => $migrationsRun,
                    'message' => $migrationsRun > 0
                        ? "Выполнено миграций: {$migrationsRun}"
                        : 'Новых миграций не обнаружено',
                    'output' => $output,
                ];
            }

            return [
                'status' => 'error',
                'error' => $process->errorOutput() ?: $process->output(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Выполнить seeders
     */
    protected function runSeeders(): array
    {
        try {
            $seeders = [
                'RoleSeeder',
                'MenuSeeder',
                'AppCategorySeeder',
                'CasesBlockSettingsSeeder',
                'HowWorkBlockSettingsSeeder',
                'FaqBlockSettingsSeeder',
                'WhyChooseUsBlockSettingsSeeder',
                'AboutSettingsSeeder',
                'ContactSettingsSeeder',
                'FooterSettingsSeeder',
                'ImportProductsServicesSeeder', // Импорт данных продуктов, сервисов и баннеров
                'RegisterAllMediaFilesSeeder',
                'UpdateMediaFolderSeeder',
                'ServicesFromExcelSeeder', // Импорт услуг из Excel файла
                'ServicesFromCsvSeeder', // Импорт услуг из CSV файла с изображениями
            ];

            $results = [];
            $totalSuccess = 0;
            $totalFailed = 0;

            foreach ($seeders as $seeder) {
                try {
                    Log::info("Выполнение seeder: {$seeder}");
                    $process = Process::path($this->basePath)
                        ->timeout(300) // 5 минут на каждый seeder
                        ->run("{$this->phpPath} artisan db:seed --class={$seeder}");

                    if ($process->successful()) {
                        $results[$seeder] = 'success';
                        $totalSuccess++;
                        Log::info("✅ Seeder выполнен успешно: {$seeder}");
                    } else {
                        $error = $process->errorOutput() ?: $process->output();
                        $results[$seeder] = 'error: ' . substr($error, 0, 200);
                        $totalFailed++;
                        Log::warning("⚠️ Ошибка выполнения seeder: {$seeder}", [
                            'error' => $error,
                        ]);
                        // Продолжаем выполнение остальных seeders даже при ошибке
                    }
                } catch (\Exception $e) {
                    $results[$seeder] = 'exception: ' . $e->getMessage();
                    $totalFailed++;
                    Log::error("❌ Исключение при выполнении seeder: {$seeder}", [
                        'error' => $e->getMessage(),
                    ]);
                    // Продолжаем выполнение остальных seeders
                }
            }

            return [
                'status' => $totalFailed === 0 ? 'success' : 'partial',
                'total' => count($seeders),
                'success' => $totalSuccess,
                'failed' => $totalFailed,
                'results' => $results,
                'message' => $totalFailed === 0
                    ? "Все seeders выполнены успешно ({$totalSuccess})"
                    : "Выполнено seeders: {$totalSuccess}, ошибок: {$totalFailed}",
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Очистить кеш package discovery
     * Важно: после удаления dev-пакетов нужно очистить кеш провайдеров
     */
    protected function clearPackageDiscoveryCache(): void
    {
        try {
            // Удаляем кеш package discovery
            $packagesCachePath = $this->basePath . '/bootstrap/cache/packages.php';
            if (file_exists($packagesCachePath)) {
                unlink($packagesCachePath);
                Log::info('Кеш package discovery удален');
            }

            // Удаляем кеш сервис-провайдеров (если существует)
            $servicesCachePath = $this->basePath . '/bootstrap/cache/services.php';
            if (file_exists($servicesCachePath)) {
                unlink($servicesCachePath);
                Log::info('Кеш сервис-провайдеров удален');
            }

            // Очищаем кеш конфигурации (может содержать ссылки на удаленные провайдеры)
            $process = Process::path($this->basePath)
                ->run("{$this->phpPath} artisan config:clear");

            if (!$process->successful()) {
                Log::warning('Не удалось очистить кеш конфигурации', [
                    'error' => $process->errorOutput(),
                ]);
            }

            // Переобнаруживаем пакеты (только установленные, без dev-зависимостей)
            $discoverProcess = Process::path($this->basePath)
                ->timeout(60)
                ->run("{$this->phpPath} artisan package:discover --ansi");

            if (!$discoverProcess->successful()) {
                Log::warning('Не удалось переобнаружить пакеты', [
                    'error' => $discoverProcess->errorOutput(),
                ]);
            } else {
                Log::info('Пакеты успешно переобнаружены');
            }
        } catch (\Exception $e) {
            Log::warning('Ошибка при очистке кеша package discovery', [
                'error' => $e->getMessage(),
            ]);
            // Не бросаем исключение, чтобы не прерывать деплой
        }
    }

    /**
     * Очистить временные файлы разработки
     * Удаляет файлы, которые не должны быть в продакшене
     */
    protected function cleanDevelopmentFiles(): void
    {
        try {
            $filesToRemove = [
                'public/hot',
                'public/hot/',
            ];

            foreach ($filesToRemove as $file) {
                $filePath = $this->basePath . '/' . trim($file, '/');

                // Удаляем через shell команды (наиболее надежно)
                $escapedPath = escapeshellarg($filePath);
                $publicPath = escapeshellarg($this->basePath . '/public');

                // Множественные способы удаления для максимальной надежности
                Process::path($this->basePath)
                    ->run("rm -f {$escapedPath} 2>/dev/null || true");

                Process::path($this->basePath)
                    ->run("rm -rf {$escapedPath} 2>/dev/null || true");

                Process::path($this->basePath)
                    ->run("find {$publicPath} -maxdepth 1 -name 'hot' -delete 2>/dev/null || true");

                // Через PHP функции (как дополнительная проверка)
                if (file_exists($filePath)) {
                    if (is_file($filePath)) {
                        @unlink($filePath);
                    } elseif (is_dir($filePath)) {
                        $this->deleteDirectory($filePath);
                    }
                    Log::info("Удален файл разработки: {$file}");
                }
            }

            // Финальная проверка через 2 секунды (на случай асинхронного создания)
            Process::path($this->basePath)
                ->timeout(5)
                ->run("sleep 2 && find " . escapeshellarg($this->basePath . '/public') . " -maxdepth 1 -name 'hot' -delete 2>/dev/null || true");

            // Дополнительно: запускаем Artisan команду для гарантированного удаления
            try {
                Process::path($this->basePath)
                    ->timeout(10)
                    ->run("{$this->phpPath} artisan clean:hot --force 2>/dev/null || true");
            } catch (\Exception $e) {
                // Игнорируем ошибки
            }

        } catch (\Exception $e) {
            Log::warning('Ошибка при очистке файлов разработки', [
                'error' => $e->getMessage(),
            ]);
            // Не бросаем исключение, чтобы не прерывать деплой
        }
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

    /**
     * Очистить все кеши
     */
    protected function clearAllCaches(): array
    {
        $commands = [
            'config:clear',
            'cache:clear',
            'route:clear',
            'view:clear',
            'optimize:clear',
        ];

        $results = [];
        foreach ($commands as $command) {
            try {
                $process = Process::path($this->basePath)
                    ->run("{$this->phpPath} artisan {$command}");

                $results[$command] = $process->successful();
            } catch (\Exception $e) {
                $results[$command] = false;
                Log::warning("Ошибка очистки кеша: {$command}", ['error' => $e->getMessage()]);
            }
        }

        return [
            'success' => !in_array(false, $results, true),
            'details' => $results,
        ];
    }

    /**
     * Оптимизировать приложение
     */
    protected function optimizeApplication(): array
    {
        $commands = [
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        $results = [];
        foreach ($commands as $command) {
            try {
                $process = Process::path($this->basePath)
                    ->run("{$this->phpPath} artisan {$command}");

                $results[$command] = $process->successful();
            } catch (\Exception $e) {
                $results[$command] = false;
                Log::warning("Ошибка оптимизации: {$command}", ['error' => $e->getMessage()]);
            }
        }

        return [
            'success' => !in_array(false, $results, true),
            'details' => $results,
        ];
    }

    /**
     * Получить текущий commit hash
     */
    protected function getCurrentCommitHash(): ?string
    {
        try {
            $safeDirectoryPath = escapeshellarg($this->basePath);
            $process = Process::path($this->basePath)
                ->env([
                    'GIT_CEILING_DIRECTORIES' => dirname($this->basePath),
                ])
                ->run("git -c safe.directory={$safeDirectoryPath} rev-parse HEAD 2>&1");

            if ($process->successful()) {
                $hash = trim($process->output());
                if (!empty($hash) && strlen($hash) === 40) {
                    return $hash;
                }
            } else {
                Log::warning('Не удалось получить commit hash', [
                    'output' => $process->output(),
                    'error' => $process->errorOutput(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Ошибка при получении commit hash', [
                'error' => $e->getMessage(),
            ]);
        }
        return null;
    }

    /**
     * Синхронизация БД и файлов с локальной разработки
     */
    public function syncSqlFile(Request $request)
    {
        $startTime = microtime(true);
        Log::info('🔄 Начало синхронизации БД и файлов', [
            'ip' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        $result = [
            'success' => false,
            'message' => '',
            'data' => [],
        ];

        try {
            // Определяем PHP путь
            $this->phpPath = $this->getPhpPath();
            $this->phpVersion = $this->getPhpVersion();

            $skipFiles = $request->input('skip_files') === '1' || $request->boolean('skip_files');
            
            // Проверяем, был ли недавно deploy (в течение последних 5 минут)
            $shouldSkipFiles = $this->shouldSkipFilesSync($skipFiles);
            
            // 1. Восстановление БД
            $dbResult = $this->restoreDatabase($request);
            $result['data']['database_restored'] = $dbResult['success'] ? 'yes' : 'no';
            if (!$dbResult['success']) {
                throw new \Exception("Ошибка восстановления БД: {$dbResult['error']}");
            }

            // 2. Синхронизация файлов (если не пропущено)
            $filesResult = ['processed' => 0, 'skipped' => 0];
            if (!$shouldSkipFiles && $request->hasFile('files_archive')) {
                $filesResult = $this->syncFiles($request);
            } elseif ($shouldSkipFiles) {
                Log::info('Пропущена синхронизация файлов: недавно был выполнен deploy');
            }
            
            $result['data']['files_processed'] = $filesResult['processed'];
            $result['data']['files_skipped'] = $filesResult['skipped'];

            // 3. Очистка кешей после синхронизации
            $this->clearAllCaches();

            // Формируем успешный ответ
            $result['success'] = true;
            $result['message'] = 'Синхронизация успешно завершена';
            $result['data'] = array_merge($result['data'], [
                'php_version' => $this->phpVersion,
                'php_path' => $this->phpPath,
                'synced_at' => now()->toDateTimeString(),
                'duration_seconds' => round(microtime(true) - $startTime, 2),
            ]);

            Log::info('✅ Синхронизация успешно завершена', $result['data']);

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['data']['error'] = $e->getMessage();
            $result['data']['trace'] = config('app.debug') ? $e->getTraceAsString() : null;
            $result['data']['synced_at'] = now()->toDateTimeString();
            $result['data']['duration_seconds'] = round(microtime(true) - $startTime, 2);

            Log::error('❌ Ошибка синхронизации', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Проверить, нужно ли пропустить синхронизацию файлов
     */
    protected function shouldSkipFilesSync(bool $forceSkip): bool
    {
        if ($forceSkip) {
            return true;
        }

        // Проверяем, был ли недавно deploy (в течение последних 5 минут)
        // Храним время последнего deploy в файле
        $lastDeployFile = storage_path('app/last_deploy_time.txt');
        
        if (file_exists($lastDeployFile)) {
            $lastDeployTime = (int) file_get_contents($lastDeployFile);
            $timeSinceDeploy = time() - $lastDeployTime;
            
            // Если deploy был менее 5 минут назад, пропускаем синхронизацию файлов
            if ($timeSinceDeploy < 300) {
                return true;
            }
        }

        return false;
    }

    /**
     * Восстановление базы данных из SQL дампа
     */
    protected function restoreDatabase(Request $request): array
    {
        try {
            if (!$request->hasFile('sql_file')) {
                return [
                    'success' => false,
                    'error' => 'SQL файл не предоставлен',
                ];
            }

            $sqlFile = $request->file('sql_file');
            $tempDir = storage_path('app/temp');
            
            // Создаем директорию если не существует
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $tempSqlPath = $tempDir . '/sync_' . time() . '.sql';
            
            // Сохраняем загруженный файл
            $sqlFile->move($tempDir, basename($tempSqlPath));

            $connection = config('database.default');
            $config = config("database.connections.{$connection}");

            if ($connection === 'sqlite') {
                // Для SQLite удаляем старую БД и создаем новую
                $dbPath = $config['database'];
                if (file_exists($dbPath)) {
                    unlink($dbPath);
                }
                
                // Восстанавливаем из SQL дампа
                $this->restoreSqliteFromDump($tempSqlPath, $dbPath);
            } elseif (in_array($connection, ['mysql', 'mariadb'])) {
                // Для MySQL используем mysql команду
                $this->restoreMysqlFromDump($config, $tempSqlPath);
            } else {
                return [
                    'success' => false,
                    'error' => "Неподдерживаемый тип БД: {$connection}",
                ];
            }

            // Удаляем временный файл
            @unlink($tempSqlPath);

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Восстановление SQLite из дампа
     */
    protected function restoreSqliteFromDump(string $dumpPath, string $dbPath): void
    {
        // Создаем новую БД
        $db = new \PDO("sqlite:{$dbPath}");
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Читаем и выполняем SQL дамп
        $sql = file_get_contents($dumpPath);
        
            // Разбиваем на отдельные запросы
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^--/', $stmt);
                }
            );
            
            foreach ($statements as $statement) {
                if (empty(trim($statement))) {
                    continue;
                }
                
                // Пропускаем запросы для исключенных таблиц
                if ($this->shouldSkipStatement($statement)) {
                    continue;
                }
                
                $db->exec($statement);
            }
    }

    /**
     * Восстановление MySQL из дампа
     */
    protected function restoreMysqlFromDump(array $config, string $dumpPath): void
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        // Сначала пробуем использовать mysql команду
        $mysqlAvailable = $this->checkMysqlAvailable();
        
        if ($mysqlAvailable) {
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($dumpPath)
            );
            
            $process = Process::run($command);
            
            if ($process->successful()) {
                return; // Успешно восстановлено через mysql
            }
            
            // Если mysql не сработал, пробуем через PHP
            Log::warning('mysql команда не сработала, используем PHP метод', [
                'error' => $process->errorOutput(),
            ]);
        } else {
            Log::info('mysql не найден, используем PHP метод');
        }
        
        // Альтернативный способ через PHP/PDO
        $this->restoreMysqlFromDumpPhp($config, $dumpPath);
    }

    /**
     * Проверить доступность mysql команды
     */
    protected function checkMysqlAvailable(): bool
    {
        try {
            $process = Process::run('which mysql');
            return $process->successful() && !empty(trim($process->output()));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Восстановление MySQL из дампа через PHP
     */
    protected function restoreMysqlFromDumpPhp(array $config, string $dumpPath): void
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        try {
            // Используем существующее подключение Laravel (уже настроено правильно)
            $pdo = \DB::connection()->getPdo();
            
            // Читаем SQL дамп
            $sql = file_get_contents($dumpPath);
            
            if (empty($sql)) {
                throw new \Exception('SQL дамп пуст или не может быть прочитан');
            }
            
            // Удаляем комментарии и разбиваем на запросы
            $sql = preg_replace('/--.*$/m', '', $sql); // Удаляем однострочные комментарии
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Удаляем многострочные комментарии
            
            // Разбиваем на отдельные запросы
            $allStatements = array_filter(
                array_map('trim', preg_split('/;\s*$/m', $sql)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^(SET|USE)/i', $stmt);
                }
            );
            
            // Фильтруем исключенные таблицы
            $statements = array_filter($allStatements, function($stmt) {
                return !$this->shouldSkipStatement($stmt);
            });
            
            $excludedCount = count($allStatements) - count($statements);
            if ($excludedCount > 0) {
                Log::info("Исключено SQL запросов: {$excludedCount}");
            }
            
            // Выполняем запросы по частям (батчами для больших дампов)
            $batchSize = 100;
            $batch = [];
            $executed = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                
                $batch[] = $statement;
                
                if (count($batch) >= $batchSize) {
                    $this->executeBatch($pdo, $batch);
                    $executed += count($batch);
                    $batch = [];
                }
            }
            
            // Выполняем оставшиеся запросы
            if (!empty($batch)) {
                $this->executeBatch($pdo, $batch);
                $executed += count($batch);
            }
            
            Log::info("Восстановлено запросов: {$executed}");
            
        } catch (\Exception $e) {
            throw new \Exception("Ошибка восстановления MySQL через PHP: " . $e->getMessage());
        }
    }

    /**
     * Выполнить батч SQL запросов
     */
    protected function executeBatch(\PDO $pdo, array $statements): void
    {
        // Выполняем запросы по одному, так как некоторые могут быть DDL (CREATE TABLE и т.д.)
        // которые не поддерживают транзакции в MySQL
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
            } catch (\Exception $e) {
                // Логируем ошибку, но продолжаем выполнение остальных запросов
                Log::warning('Ошибка выполнения SQL запроса', [
                    'error' => $e->getMessage(),
                    'statement' => substr($statement, 0, 200),
                ]);
                
                // Пропускаем проблемный запрос и продолжаем
                continue;
            }
        }
    }

    /**
     * Синхронизация файлов из архива
     */
    protected function syncFiles(Request $request): array
    {
        try {
            if (!$request->hasFile('files_archive')) {
                return ['processed' => 0, 'skipped' => 0];
            }

            $archive = $request->file('files_archive');
            $tempDir = storage_path('app/temp');
            
            // Создаем директорию если не существует
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $tempArchivePath = $tempDir . '/sync_files_' . time() . '.zip';
            $archive->move($tempDir, basename($tempArchivePath));

            $uploadDir = public_path('upload');
            if (!is_dir($uploadDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($uploadDir, 0755, true);
            }

            if (!class_exists('ZipArchive')) {
                throw new \Exception('Класс ZipArchive не доступен. Установите расширение php-zip');
            }
            
            $zip = new ZipArchive();
            if ($zip->open($tempArchivePath) !== true) {
                throw new \Exception('Не удалось открыть ZIP архив');
            }

            $processed = 0;
            $skipped = 0;
            $fileHashes = $this->getExistingFileHashes($uploadDir);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                
                if ($filename === false || strpos($filename, '__MACOSX/') !== false) {
                    continue;
                }

                $targetPath = $uploadDir . '/' . $filename;
                $targetDir = dirname($targetPath);
                
                if (!is_dir($targetDir)) {
                    \Illuminate\Support\Facades\File::makeDirectory($targetDir, 0755, true);
                }

                // Извлекаем файл во временную директорию для проверки
                $tempFile = storage_path('app/temp/' . basename($filename));
                file_put_contents($tempFile, $zip->getFromIndex($i));
                
                // Проверяем на дубли по хешу
                $fileHash = md5_file($tempFile);
                $relativePath = str_replace(public_path('upload') . '/', '', $targetPath);
                
                if (isset($fileHashes[$relativePath]) && $fileHashes[$relativePath] === $fileHash) {
                    // Файл уже существует с тем же хешем - пропускаем
                    $skipped++;
                    @unlink($tempFile);
                    continue;
                }

                // Копируем файл
                if (copy($tempFile, $targetPath)) {
                    $processed++;
                    // Обновляем хеш
                    $fileHashes[$relativePath] = $fileHash;
                }
                
                @unlink($tempFile);
            }

            $zip->close();
            @unlink($tempArchivePath);

            return [
                'processed' => $processed,
                'skipped' => $skipped,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации файлов', ['error' => $e->getMessage()]);
            return [
                'processed' => 0,
                'skipped' => 0,
            ];
        }
    }

    /**
     * Получить хеши существующих файлов для предотвращения дублей
     */
    protected function getExistingFileHashes(string $uploadDir): array
    {
        $hashes = [];
        
        if (!is_dir($uploadDir)) {
            return $hashes;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace(public_path('upload') . '/', '', $file->getPathname());
                $hashes[$relativePath] = md5_file($file->getPathname());
            }
        }

        return $hashes;
    }

    /**
     * Проверить, нужно ли пропустить SQL запрос (для исключенных таблиц)
     */
    protected function shouldSkipStatement(string $statement): bool
    {
        $statement = trim($statement);
        if (empty($statement)) {
            return true;
        }
        
        // Проверяем каждый исключенный таблицу
        foreach ($this->excludedTables as $excludedTable) {
            // Проверяем различные типы SQL запросов
            $patterns = [
                "/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`'\"]?{$excludedTable}[`'\"]?/i",
                "/INSERT\s+INTO\s+[`'\"]?{$excludedTable}[`'\"]?/i",
                "/DROP\s+TABLE\s+(?:IF\s+EXISTS\s+)?[`'\"]?{$excludedTable}[`'\"]?/i",
                "/ALTER\s+TABLE\s+[`'\"]?{$excludedTable}[`'\"]?/i",
                "/TRUNCATE\s+TABLE\s+[`'\"]?{$excludedTable}[`'\"]?/i",
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $statement)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Сохранить время последнего deploy
     */
    protected function saveLastDeployTime(): void
    {
        try {
            $lastDeployFile = storage_path('app/last_deploy_time.txt');
            $dir = dirname($lastDeployFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($lastDeployFile, (string) time());
        } catch (\Exception $e) {
            Log::warning('Не удалось сохранить время последнего deploy', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Проверка требований для синхронизации (API endpoint)
     */
    public function checkSyncRequirements()
    {
        $result = [
            'success' => true,
            'message' => '',
            'data' => [],
        ];

        try {
            // Проверка PHP Zip
            $zipAvailable = extension_loaded('zip') && class_exists('ZipArchive');
            $result['data']['php_zip'] = [
                'available' => $zipAvailable,
                'message' => $zipAvailable ? 'Установлено' : 'НЕ установлено',
            ];

            // Проверка MySQL утилит
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            if (in_array($connection, ['mysql', 'mariadb'])) {
                // Проверка mysqldump
                $mysqldumpCheck = Process::run('which mysqldump');
                $mysqldumpAvailable = $mysqldumpCheck->successful() && !empty(trim($mysqldumpCheck->output()));
                
                // Проверка mysql
                $mysqlCheck = Process::run('which mysql');
                $mysqlAvailable = $mysqlCheck->successful() && !empty(trim($mysqlCheck->output()));

                $result['data']['mysql_tools'] = [
                    'mysqldump' => [
                        'available' => $mysqldumpAvailable,
                        'path' => $mysqldumpAvailable ? trim($mysqldumpCheck->output()) : null,
                    ],
                    'mysql' => [
                        'available' => $mysqlAvailable,
                        'path' => $mysqlAvailable ? trim($mysqlCheck->output()) : null,
                    ],
                ];

                // Проверка подключения к БД
                try {
                    \DB::connection()->getPdo();
                    $result['data']['database_connection'] = [
                        'available' => true,
                        'database' => $config['database'],
                        'host' => $config['host'] . ':' . $config['port'],
                    ];
                } catch (\Exception $e) {
                    $result['data']['database_connection'] = [
                        'available' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            } elseif ($connection === 'sqlite') {
                $sqliteCheck = Process::run('which sqlite3');
                $sqliteAvailable = $sqliteCheck->successful() && !empty(trim($sqliteCheck->output()));
                
                $result['data']['sqlite_tool'] = [
                    'available' => $sqliteAvailable,
                    'path' => $sqliteAvailable ? trim($sqliteCheck->output()) : null,
                    'message' => $sqliteAvailable ? 'sqlite3 найден' : 'sqlite3 не найден (будет использован PHP метод)',
                ];
            }

            // Проверка прав доступа
            $uploadDir = public_path('upload');
            $tempDir = storage_path('app/temp');
            
            $result['data']['permissions'] = [
                'upload_dir' => [
                    'exists' => is_dir($uploadDir),
                    'writable' => is_dir($uploadDir) ? is_writable($uploadDir) : false,
                    'path' => $uploadDir,
                ],
                'temp_dir' => [
                    'exists' => is_dir($tempDir),
                    'writable' => is_dir($tempDir) ? is_writable($tempDir) : (is_writable(dirname($tempDir))),
                    'path' => $tempDir,
                ],
            ];

            // Проверка конфигурации
            $result['data']['configuration'] = [
                'server_url' => env('SERVER_URL') ? 'настроен' : 'не настроен',
                'deploy_token' => env('DEPLOY_TOKEN') ? 'настроен' : 'не настроен',
            ];

            // Общий статус
            $allOk = $zipAvailable;
            if (in_array($connection, ['mysql', 'mariadb'])) {
                $allOk = $allOk && $mysqldumpAvailable && $mysqlAvailable;
            }

            $result['success'] = $allOk;
            $result['message'] = $allOk 
                ? 'Все требования выполнены' 
                : 'Некоторые требования не выполнены';

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            $result['data']['error'] = $e->getMessage();
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}


