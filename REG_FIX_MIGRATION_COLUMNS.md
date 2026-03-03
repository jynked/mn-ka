# Исправление проблемы с существующими колонками в миграции

## Проблема
Колонки `protected` и `is_trash` уже существуют в таблице `folders`, но значения не установлены.

## Решение 1: Проверка и установка значений (рекомендуется)

Выполните на сервере:

```bash
cd ~/mn-ka.ru

# Проверьте, какие колонки уже есть
php artisan tinker --execute="
\$columns = DB::select('SHOW COLUMNS FROM folders');
foreach(\$columns as \$col) {
    echo \$col->Field . PHP_EOL;
}
"
```

Если колонки `protected` и `is_trash` есть, просто установите значения вручную:

```bash
php artisan tinker --execute="
DB::table('folders')->whereIn('id', [1, 2, 3, 4])->update(['protected' => true]);
DB::table('folders')->where('id', 4)->update(['is_trash' => true]);
DB::table('folders')->whereIn('id', [1, 2, 3])->update(['is_trash' => false]);
echo 'Значения установлены';
"
```

Затем отметьте миграцию как выполненную:

```bash
php artisan tinker --execute="
DB::table('migrations')->insert([
    'migration' => '2025_11_08_171010_add_protected_to_folders_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
echo 'Миграция отмечена как выполненная';
"
```

## Решение 2: Удалить колонки и запустить миграцию заново

```bash
php artisan tinker --execute="
Schema::table('folders', function (\$table) {
    \$table->dropColumn(['protected', 'is_trash']);
});
echo 'Колонки удалены';
"

# Теперь запустите миграцию снова
php artisan migrate
```

## Решение 3: Изменить миграцию для проверки существования колонок

Измените файл миграции, чтобы он проверял существование колонок:

```php
public function up(): void
{
    // Проверяем, существует ли колонка
    if (!Schema::hasColumn('folders', 'protected')) {
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('protected')->default(false)->after('position');
            $table->boolean('is_trash')->default(false)->after('protected');
        });
    }
    
    // Устанавливаем значения (всегда выполняем)
    DB::table('folders')->whereIn('id', [1, 2, 3, 4])->update([
        'protected' => true
    ]);
    DB::table('folders')->where('id', 4)->update(['is_trash' => true]);
    DB::table('folders')->whereIn('id', [1, 2, 3])->update(['is_trash' => false]);
}
```

