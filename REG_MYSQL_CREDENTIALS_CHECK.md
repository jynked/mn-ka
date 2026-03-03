# Проверка учетных данных MySQL на REG хостинге

## Проблема
```
ERROR 1045 (28000): Access denied for user 'u3384357_lag_crm'@'localhost'
```

Это означает, что имя пользователя или пароль неверны.

## Решение

### Способ 1: Проверьте учетные данные в .env файле Laravel

```bash
cd ~/mn-ka.ru

# Посмотрите настройки базы данных
cat .env | grep DB_

# Должны быть примерно такие строки:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=u3384357_lag_crm
# DB_USERNAME=правильный_пользователь
# DB_PASSWORD=правильный_пароль
```

### Способ 2: Используйте учетные данные из .env

```bash
cd ~/mn-ka.ru

# Получите данные из .env
DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d ' "')
DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d ' "')
DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d ' "')

# Импортируйте используя эти данные
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
SET FOREIGN_KEY_CHECKS=0;
EOF

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < ~/dsc23ytp_lag_crm.sql

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
SET FOREIGN_KEY_CHECKS=1;
EOF
```

### Способ 3: Используйте правильный формат имени пользователя REG

На REG хостинге имя пользователя базы данных обычно имеет формат:
- `u3384357_lag_crm` - это может быть неправильно
- Обычно формат: `u3384357_имя_базы` или просто `u3384357_*`

Проверьте в панели управления REG:
1. Зайдите в раздел "Базы данных MySQL"
2. Посмотрите список баз данных и пользователей
3. Используйте точное имя пользователя и пароль оттуда

### Способ 4: Подключение через phpMyAdmin

1. Зайдите в панель управления REG
2. Найдите phpMyAdmin в разделе "Базы данных"
3. Зайдите в phpMyAdmin (обычно автоматически подключается с правильными учетными данными)
4. Выберите базу данных
5. Выполните:
   ```sql
   SET FOREIGN_KEY_CHECKS=0;
   ```
6. Вкладка "Импорт" → загрузите файл
7. После импорта:
   ```sql
   SET FOREIGN_KEY_CHECKS=1;
   ```

### Способ 5: Проверьте через Laravel Artisan

```bash
cd ~/mn-ka.ru

# Попробуйте подключиться через Laravel
php artisan tinker --execute="
try {
    DB::connection()->getPdo();
    echo 'Подключение успешно!';
    echo PHP_EOL . 'Database: ' . DB::connection()->getDatabaseName();
} catch (Exception \$e) {
    echo 'Ошибка: ' . \$e->getMessage();
}
"
```

## Типичные проблемы на REG хостинге

1. **Имя пользователя не совпадает с именем базы** - проверьте точное имя в панели
2. **Пароль содержит спецсимволы** - используйте кавычки в команде
3. **Хост не localhost** - некоторые хостинги используют другой хост (например, `localhost:3306` или IP)

## Правильный синтаксис команды с паролем

Если пароль содержит спецсимволы, используйте:

```bash
# С явным указанием пароля в командной строке (менее безопасно, но работает)
mysql -u username -p'password' database_name

# Или используйте переменные окружения
export MYSQL_PWD='password'
mysql -u username database_name
```

## Альтернатива: Импорт через скрипт PHP

Создайте временный PHP скрипт для импорта:

```php
<?php
// import_db.php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
];

$sqlFile = __DIR__.'/dsc23ytp_lag_crm.sql';

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}",
        $db['username'],
        $db['password']
    );
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "Импорт успешно завершен!\n";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
```

Запустите:
```bash
php import_db.php
```

