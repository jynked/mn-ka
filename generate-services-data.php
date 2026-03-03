<?php
/**
 * Скрипт для генерации PHP файла с данными из CSV
 * Запуск: php generate-services-data.php
 */

$csvPath = 'C:\Users\dsc-2\Downloads\111_extracted\services.csv';
$outputPath = __DIR__ . '/database/seeders/ServicesData.php';

if (!file_exists($csvPath)) {
    die("CSV файл не найден: {$csvPath}\n");
}

echo "Чтение CSV файла...\n";
$handle = fopen($csvPath, 'r');

if ($handle === false) {
    die("Не удалось открыть CSV файл\n");
}

// Пропускаем заголовок
fgetcsv($handle, 0, ';');

$data = [];
$count = 0;

while (($row = fgetcsv($handle, 0, ';')) !== false) {
    if (count($row) >= 15) {
        $data[] = $row;
        $count++;
    }
}

fclose($handle);

echo "Прочитано строк: {$count}\n";
echo "Генерация PHP файла...\n";

$phpContent = "<?php\n";
$phpContent .= "/**\n";
$phpContent .= " * Данные услуг из CSV файла\n";
$phpContent .= " * Сгенерировано автоматически из services.csv\n";
$phpContent .= " * Дата генерации: " . date('Y-m-d H:i:s') . "\n";
$phpContent .= " */\n\n";
$phpContent .= "return " . var_export($data, true) . ";\n";

file_put_contents($outputPath, $phpContent);

echo "✅ Данные сохранены в: {$outputPath}\n";
echo "Размер файла: " . number_format(filesize($outputPath) / 1024, 2) . " KB\n";

