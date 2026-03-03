<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use App\Models\Chapter;
use App\Models\Product;
use App\Models\Service;
use App\Models\Folder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrateLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy-data 
                            {--legacy-path= : Path to legacy project directory}
                            {--legacy-db-host=127.0.0.1 : Legacy database host}
                            {--legacy-db-port=3306 : Legacy database port}
                            {--legacy-db-name= : Legacy database name}
                            {--legacy-db-user=root : Legacy database user}
                            {--legacy-db-pass= : Legacy database password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from legacy project (chapters, products, services, media files)';

    /**
     * Legacy database connection
     */
    protected $legacyConnection;

    /**
     * Path to legacy project
     */
    protected $legacyPath;

    /**
     * Mapping arrays for IDs
     */
    protected $mediaMapping = [];
    protected $chapterMapping = [];
    protected $productMapping = [];
    protected $serviceMapping = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting legacy data migration...');

        // Get legacy project path
        $this->legacyPath = $this->option('legacy-path') ?: 'C:\OSPanel\domains\lagom';
        
        if (!is_dir($this->legacyPath)) {
            $this->error("❌ Legacy project path not found: {$this->legacyPath}");
            return 1;
        }

        // Setup legacy database connection
        if (!$this->setupLegacyConnection()) {
            return 1;
        }

        // Get "Общая" folder ID
        $commonFolder = Folder::where('slug', 'common')->first();
        if (!$commonFolder) {
            $this->error('❌ Folder "Общая" not found. Please run migrations first.');
            return 1;
        }
        $commonFolderId = $commonFolder->id;

        // Verify tables exist in legacy database
        if (!$this->verifyLegacyTables()) {
            return 1;
        }

        try {
            // Step 1: Migrate Media
            $this->info("\n📦 Step 1: Migrating Media files...");
            $this->migrateMedia($commonFolderId);

            // Step 2: Migrate Chapters
            $this->info("\n📚 Step 2: Migrating Chapters...");
            $this->migrateChapters();

            // Step 3: Migrate Products
            $this->info("\n📦 Step 3: Migrating Products...");
            $this->migrateProducts();

            // Step 4: Migrate Services
            $this->info("\n🔧 Step 4: Migrating Services...");
            $this->migrateServices();

            // Step 5: Migrate Product-Service relationships
            $this->info("\n🔗 Step 5: Migrating Product-Service relationships...");
            $this->migrateProductServiceRelations();

            $this->info("\n✅ Migration completed successfully!");
            $this->displaySummary();

            return 0;
        } catch (\Exception $e) {
            $this->error("\n❌ Migration failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Verify required tables exist in legacy database
     */
    protected function verifyLegacyTables()
    {
        $requiredTables = ['media', 'chapters', 'products', 'services', 'product_service'];
        
        try {
            foreach ($requiredTables as $table) {
                $stmt = $this->legacyConnection->query("SHOW TABLES LIKE '{$table}'");
                if ($stmt->rowCount() === 0) {
                    $this->error("❌ Table '{$table}' not found in legacy database");
                    return false;
                }
            }
            $this->info("✅ All required tables found in legacy database");
            return true;
        } catch (\PDOException $e) {
            $this->error("❌ Error verifying tables: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setup legacy database connection
     */
    protected function setupLegacyConnection()
    {
        $host = $this->option('legacy-db-host');
        $port = $this->option('legacy-db-port');
        $database = $this->option('legacy-db-name') ?: $this->ask('Legacy database name');
        $username = $this->option('legacy-db-user');
        $password = $this->option('legacy-db-pass') ?: $this->secret('Legacy database password');

        if (!$database) {
            $this->error('❌ Legacy database name is required');
            return false;
        }

        try {
            $this->legacyConnection = new \PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );

            $this->info("✅ Connected to legacy database: {$database}");
            return true;
        } catch (\PDOException $e) {
            $this->error("❌ Failed to connect to legacy database: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Migrate Media files
     */
    protected function migrateMedia($commonFolderId)
    {
        $query = "SELECT * FROM media";
        $stmt = $this->legacyConnection->query($query);
        $legacyMedia = $stmt->fetchAll();

        $bar = $this->output->createProgressBar(count($legacyMedia));
        $bar->start();

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($legacyMedia as $legacy) {
            try {
                $oldId = $legacy['id'];

                // Check if already migrated
                if (Media::where('name', $legacy['name'])->where('original_name', $legacy['original_name'])->exists()) {
                    $existing = Media::where('name', $legacy['name'])->where('original_name', $legacy['original_name'])->first();
                    $this->mediaMapping[$oldId] = $existing->id;
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Determine file path
                $metadata = $legacy['metadata'] ? json_decode($legacy['metadata'], true) : [];
                $oldPath = $metadata['path'] ?? ($legacy['disk'] . '/' . $legacy['name']);
                
                // Remove leading slash if present
                $oldPath = ltrim($oldPath, '/');
                
                // Try multiple possible paths
                $possiblePaths = [
                    $this->legacyPath . '/public/' . $oldPath,
                    $this->legacyPath . '/public/' . $legacy['disk'] . '/' . $legacy['name'],
                    $this->legacyPath . '/public/upload/' . $legacy['name'],
                ];

                $legacyPublicPath = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $legacyPublicPath = $path;
                        break;
                    }
                }

                $newPublicPath = public_path('upload/' . $legacy['name']);

                // Copy file if exists
                if ($legacyPublicPath && file_exists($legacyPublicPath)) {
                    // Create upload directory if not exists
                    $uploadDir = public_path('upload');
                    if (!File::exists($uploadDir)) {
                        File::makeDirectory($uploadDir, 0755, true);
                    }

                    // Copy file
                    File::copy($legacyPublicPath, $newPublicPath);
                } else {
                    $this->warn("\n⚠️  File not found for media ID {$oldId}: {$legacy['name']}");
                    $this->warn("   Tried paths: " . implode(', ', $possiblePaths));
                }

                // Prepare metadata
                $newMetadata = [
                    'path' => 'upload/' . $legacy['name'],
                    'mime_type' => $metadata['mime_type'] ?? null,
                    'original_path' => $oldPath, // Save original path for reference
                ];

                // Create new Media record
                $newMedia = Media::create([
                    'name' => $legacy['name'],
                    'original_name' => $legacy['original_name'],
                    'extension' => $legacy['extension'],
                    'disk' => 'upload',
                    'width' => $legacy['width'] ?? null,
                    'height' => $legacy['height'] ?? null,
                    'type' => $legacy['type'] ?? 'photo',
                    'size' => $legacy['size'] ?? 0,
                    'folder_id' => $commonFolderId,
                    'user_id' => null,
                    'temporary' => false,
                    'metadata' => json_encode($newMetadata),
                ]);

                $this->mediaMapping[$oldId] = $newMedia->id;
                $migrated++;
            } catch (\Exception $e) {
                $errors++;
                $this->warn("\n⚠️  Error migrating media ID {$legacy['id']}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n   Migrated: {$migrated}, Skipped: {$skipped}, Errors: {$errors}");
    }

    /**
     * Migrate Chapters
     */
    protected function migrateChapters()
    {
        $query = "SELECT * FROM chapters ORDER BY id";
        $stmt = $this->legacyConnection->query($query);
        $legacyChapters = $stmt->fetchAll();

        $bar = $this->output->createProgressBar(count($legacyChapters));
        $bar->start();

        $migrated = 0;

        foreach ($legacyChapters as $legacy) {
            try {
                $oldId = $legacy['id'];

                // Check if already exists
                $existing = Chapter::where('name', $legacy['name'])->first();
                if ($existing) {
                    $this->chapterMapping[$oldId] = $existing->id;
                    $bar->advance();
                    continue;
                }

                $newChapter = Chapter::create([
                    'name' => $legacy['name'],
                    'order' => $oldId - 1, // Use old ID as order
                    'is_active' => true,
                ]);

                $this->chapterMapping[$oldId] = $newChapter->id;
                $migrated++;
            } catch (\Exception $e) {
                $this->warn("\n⚠️  Error migrating chapter ID {$legacy['id']}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n   Migrated: {$migrated} chapters");
    }

    /**
     * Migrate Products
     */
    protected function migrateProducts()
    {
        $query = "SELECT * FROM products";
        $stmt = $this->legacyConnection->query($query);
        $legacyProducts = $stmt->fetchAll();

        $bar = $this->output->createProgressBar(count($legacyProducts));
        $bar->start();

        $migrated = 0;

        foreach ($legacyProducts as $legacy) {
            try {
                $oldId = $legacy['id'];

                // Check if already exists
                $existing = Product::where('slug', $legacy['slug'])->first();
                if ($existing) {
                    $this->productMapping[$oldId] = $existing->id;
                    $bar->advance();
                    continue;
                }

                // Map related IDs
                $chapterId = $legacy['chapter_id'] ? ($this->chapterMapping[$legacy['chapter_id']] ?? null) : null;
                $imageId = $legacy['image_id'] ? ($this->mediaMapping[$legacy['image_id']] ?? null) : null;
                $iconId = $legacy['icon_id'] ? ($this->mediaMapping[$legacy['icon_id']] ?? null) : null;

                // Handle slug uniqueness
                $slug = $legacy['slug'];
                $counter = 1;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $legacy['slug'] . '-' . $counter;
                    $counter++;
                }

                $newProduct = Product::create([
                    'name' => $legacy['name'],
                    'slug' => $slug,
                    'description' => $legacy['description'] ? json_decode($legacy['description'], true) : null,
                    'image_id' => $imageId,
                    'icon_id' => $iconId,
                    'chapter_id' => $chapterId,
                    'order' => 0,
                    'is_active' => true,
                ]);

                $this->productMapping[$oldId] = $newProduct->id;
                $migrated++;
            } catch (\Exception $e) {
                $this->warn("\n⚠️  Error migrating product ID {$legacy['id']}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n   Migrated: {$migrated} products");
    }

    /**
     * Migrate Services
     */
    protected function migrateServices()
    {
        $query = "SELECT * FROM services";
        $stmt = $this->legacyConnection->query($query);
        $legacyServices = $stmt->fetchAll();

        $bar = $this->output->createProgressBar(count($legacyServices));
        $bar->start();

        $migrated = 0;

        foreach ($legacyServices as $legacy) {
            try {
                $oldId = $legacy['id'];

                // Check if already exists
                $existing = Service::where('slug', $legacy['slug'])->first();
                if ($existing) {
                    $this->serviceMapping[$oldId] = $existing->id;
                    $bar->advance();
                    continue;
                }

                // Map related IDs
                $chapterId = $legacy['chapter_id'] ? ($this->chapterMapping[$legacy['chapter_id']] ?? null) : null;
                $imageId = $legacy['image_id'] ? ($this->mediaMapping[$legacy['image_id']] ?? null) : null;
                $iconId = $legacy['icon_id'] ? ($this->mediaMapping[$legacy['icon_id']] ?? null) : null;

                // Handle slug uniqueness
                $slug = $legacy['slug'];
                $counter = 1;
                while (Service::where('slug', $slug)->exists()) {
                    $slug = $legacy['slug'] . '-' . $counter;
                    $counter++;
                }

                $newService = Service::create([
                    'name' => $legacy['name'],
                    'slug' => $slug,
                    'description' => $legacy['description'] ? json_decode($legacy['description'], true) : null,
                    'image_id' => $imageId,
                    'icon_id' => $iconId,
                    'chapter_id' => $chapterId,
                    'order' => 0,
                    'is_active' => true,
                ]);

                $this->serviceMapping[$oldId] = $newService->id;
                $migrated++;
            } catch (\Exception $e) {
                $this->warn("\n⚠️  Error migrating service ID {$legacy['id']}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n   Migrated: {$migrated} services");
    }

    /**
     * Migrate Product-Service relationships
     */
    protected function migrateProductServiceRelations()
    {
        $query = "SELECT * FROM product_service";
        $stmt = $this->legacyConnection->query($query);
        $legacyRelations = $stmt->fetchAll();

        $bar = $this->output->createProgressBar(count($legacyRelations));
        $bar->start();

        $migrated = 0;
        $skipped = 0;

        foreach ($legacyRelations as $legacy) {
            try {
                $oldProductId = $legacy['product_id'];
                $oldServiceId = $legacy['service_id'];

                // Map to new IDs
                $newProductId = $this->productMapping[$oldProductId] ?? null;
                $newServiceId = $this->serviceMapping[$oldServiceId] ?? null;

                if (!$newProductId || !$newServiceId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Check if relation already exists
                $exists = DB::table('product_service')
                    ->where('product_id', $newProductId)
                    ->where('service_id', $newServiceId)
                    ->exists();

                if (!$exists) {
                    DB::table('product_service')->insert([
                        'product_id' => $newProductId,
                        'service_id' => $newServiceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $migrated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->warn("\n⚠️  Error migrating relation: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n   Migrated: {$migrated}, Skipped: {$skipped} relationships");
    }

    /**
     * Display migration summary
     */
    protected function displaySummary()
    {
        $this->info("\n📊 Migration Summary:");
        $this->table(
            ['Entity', 'Count'],
            [
                ['Media', count($this->mediaMapping)],
                ['Chapters', count($this->chapterMapping)],
                ['Products', count($this->productMapping)],
                ['Services', count($this->serviceMapping)],
            ]
        );
    }
}
