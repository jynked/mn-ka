# Скрипт для подготовки файлов Services CSV для загрузки на сервер
# Использование: .\prepare-services-seed.ps1

$sourcePath = "C:\Users\dsc-2\Downloads\111_extracted"
$targetPath = "storage\app\services-seed"

Write-Host "Подготовка файлов для Services CSV Seeder..." -ForegroundColor Cyan

# Проверяем наличие исходных файлов
if (-not (Test-Path "$sourcePath\services.csv")) {
    Write-Host "ОШИБКА: Файл services.csv не найден в $sourcePath" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path "$sourcePath\images")) {
    Write-Host "ОШИБКА: Папка images не найдена в $sourcePath" -ForegroundColor Red
    exit 1
}

# Создаем целевую папку
if (-not (Test-Path $targetPath)) {
    New-Item -ItemType Directory -Path $targetPath -Force | Out-Null
    Write-Host "Создана папка: $targetPath" -ForegroundColor Green
}

# Копируем CSV файл
Copy-Item "$sourcePath\services.csv" -Destination "$targetPath\services.csv" -Force
Write-Host "Скопирован services.csv" -ForegroundColor Green

# Копируем папку images
if (Test-Path "$targetPath\images") {
    Remove-Item "$targetPath\images" -Recurse -Force
}
Copy-Item "$sourcePath\images" -Destination "$targetPath\images" -Recurse -Force
Write-Host "Скопирована папка images" -ForegroundColor Green

# Создаем архив для загрузки на сервер
$archivePath = "storage\app\services-seed.zip"
if (Test-Path $archivePath) {
    Remove-Item $archivePath -Force
}

Compress-Archive -Path "$targetPath\*" -DestinationPath $archivePath -Force
Write-Host "Создан архив: $archivePath" -ForegroundColor Green

Write-Host ""
Write-Host "Подготовка завершена!" -ForegroundColor Green
Write-Host ""
Write-Host "Следующие шаги:" -ForegroundColor Cyan
Write-Host "1. Загрузите архив $archivePath на сервер" -ForegroundColor Yellow
Write-Host "2. Распакуйте его в /home/d/dsc23ytp/stroy/public_html/storage/app/" -ForegroundColor Yellow
Write-Host "3. Выполните: php artisan db:seed --class=ServicesFromCsvSeeder" -ForegroundColor Yellow
Write-Host ""
Write-Host "Или используйте команду deploy с флагом --with-seed" -ForegroundColor Yellow

