# Загрузка SQL файла на REG хостинг для импорта

## Способ 1: Через SFTP/SCP (рекомендуется)

### На Windows (PowerShell или CMD):

```powershell
# Используя scp (если установлен OpenSSH)
scp "C:\Users\ВашеИмя\Загрузки\dsc23ytp_lag_crm.sql" u3384357@server37.hosting.reg.ru:~/

# Или используя FileZilla/WinSCP (GUI)
# 1. Откройте FileZilla/WinSCP
# 2. Подключитесь к server37.hosting.reg.ru
# 3. Загрузите файл в домашнюю директорию
```

### На Linux/Mac:

```bash
scp ~/Загрузки/dsc23ytp_lag_crm.sql u3384357@server37.hosting.reg.ru:~/
# или
scp ~/Downloads/dsc23ytp_lag_crm.sql u3384357@server37.hosting.reg.ru:~/
```

## Способ 2: Через панель управления REG

1. Зайдите в панель управления REG хостингом
2. Найдите "Файловый менеджер" или "File Manager"
3. Перейдите в домашнюю директорию или `mn-ka.ru`
4. Загрузите файл через веб-интерфейс

## Способ 3: Через SSH и cat (для небольших файлов)

Если файл небольшой, можно скопировать содержимое:

```bash
# На локальной машине откройте файл
# Скопируйте его содержимое

# На сервере создайте файл:
nano ~/dsc23ytp_lag_crm.sql
# Вставьте содержимое, сохраните (Ctrl+O, Enter, Ctrl+X)
```

## Способ 4: Через wget/curl (если файл доступен по URL)

```bash
# Если файл загружен на временный хостинг или облако
wget https://example.com/dsc23ytp_lag_crm.sql -O ~/dsc23ytp_lag_crm.sql

# Или curl
curl -o ~/dsc23ytp_lag_crm.sql https://example.com/dsc23ytp_lag_crm.sql
```

## После загрузки файла

После загрузки файла на сервер выполните импорт:

```bash
# Проверьте, что файл загружен
ls -lh ~/dsc23ytp_lag_crm.sql

# Импортируйте
DB_USER="u3384357_lag_crm"
DB_PASS="g7F1TeRRp5&y"
DB_NAME="u3384357_lag_crm"

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
SET FOREIGN_KEY_CHECKS=0;
EOF

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < ~/dsc23ytp_lag_crm.sql

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
SET FOREIGN_KEY_CHECKS=1;
EOF

echo "Импорт завершен!"
```

## Проверка размера файла

Если файл очень большой, может понадобиться увеличить лимиты MySQL:

```bash
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
SET GLOBAL max_allowed_packet=1073741824;  -- 1GB
SET GLOBAL net_read_timeout=600;
SET GLOBAL net_write_timeout=600;
EOF
```

