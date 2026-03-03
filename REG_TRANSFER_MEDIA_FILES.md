# Перенос медиа файлов (фото) с beget на reg.ru

## Проблема
Фото из `public/upload` не перенеслись при клонировании из Git (они не хранятся в репозитории).

## Решение

### Вариант 1: Через rsync (рекомендуется, если есть SSH доступ к обоим серверам)

```bash
# С локальной машины или с reg.ru (если есть доступ к beget)
rsync -avz --progress \
  user@beget.ru:~/stroy/public_html/public/upload/ \
  user@reg.ru:/var/www/u3384357/data/www/mn-ka.ru/public/upload/

# Или если выполняете с reg.ru и есть доступ к beget:
rsync -avz --progress \
  user@beget.ru:~/stroy/public_html/public/upload/ \
  /var/www/u3384357/data/www/mn-ka.ru/public/upload/
```

### Вариант 2: Через SCP (с локальной машины или beget)

```bash
# С beget или локальной машины
scp -r user@beget.ru:~/stroy/public_html/public/upload/* \
  user@reg.ru:/var/www/u3384357/data/www/mn-ka.ru/public/upload/

# Или если файлов много, создайте архив на beget:
# На beget:
cd ~/stroy/public_html/public
tar -czf upload.tar.gz upload/

# Затем скопируйте архив:
scp user@beget.ru:~/stroy/public_html/public/upload.tar.gz user@reg.ru:~/

# На reg.ru распакуйте:
cd /var/www/u3384357/data/www/mn-ka.ru/public
tar -xzf ~/upload.tar.gz
rm ~/upload.tar.gz
```

### Вариант 3: Через панели управления (File Manager)

1. **На beget:**
   - Зайдите в File Manager
   - Перейдите в `public/upload/`
   - Выберите все файлы/папки
   - Архивируйте (ZIP/TAR.GZ)
   - Скачайте архив на локальную машину

2. **На reg.ru:**
   - Зайдите в File Manager
   - Загрузите архив в `public/upload/`
   - Распакуйте архив

### Вариант 4: Через wget/curl (если upload доступен по HTTP)

Если на beget файлы доступны через веб, можно скачать:

```bash
# На reg.ru
cd /var/www/u3384357/data/www/mn-ka.ru/public/upload

# Скачайте файлы (если есть прямой доступ)
wget -r -np -nH --cut-dirs=2 https://beget-site.ru/upload/
```

---

## Полная команда для переноса (рекомендуется)

### На beget (создайте архив):

```bash
ssh user@beget.ru

cd ~/stroy/public_html/public
tar -czf ~/upload_backup.tar.gz upload/

# Проверьте размер
ls -lh ~/upload_backup.tar.gz
```

### С локальной машины или beget (копирование):

```bash
# Скопируйте архив
scp user@beget.ru:~/upload_backup.tar.gz user@reg.ru:~/
```

### На reg.ru (распаковка):

```bash
cd /var/www/u3384357/data/www/mn-ka.ru/public

# Распакуйте архив
tar -xzf ~/upload_backup.tar.gz

# Проверьте
ls -la upload/ | head -20

# Установите права
chmod -R 755 upload/

# Удалите архив
rm ~/upload_backup.tar.gz
```

---

## Проверка после переноса

```bash
# На reg.ru
cd /var/www/u3384357/data/www/mn-ka.ru

# Проверьте количество файлов
find public/upload -type f | wc -l

# Проверьте размер
du -sh public/upload/

# Проверьте права
ls -la public/upload/ | head -10
```

---

## Важно

1. **Права доступа:** После переноса установите правильные права:
   ```bash
   chmod -R 755 public/upload/
   ```

2. **Структура папок:** Убедитесь, что структура папок сохранилась (если есть подпапки)

3. **Проверка в базе данных:** После переноса файлов может потребоваться обновить пути в базе данных, если они отличаются

---

## Если файлов очень много

Для больших объемов используйте rsync с опциями:

```bash
rsync -avz --progress --partial \
  user@beget.ru:~/stroy/public_html/public/upload/ \
  user@reg.ru:/var/www/u3384357/data/www/mn-ka.ru/public/upload/
```

Опция `--partial` позволяет продолжить прерванную передачу.

