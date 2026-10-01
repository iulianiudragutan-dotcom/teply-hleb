#!/usr/bin/env bash
set -euo pipefail
src=${1:?Укажите путь к распакованной папке teply-hleb}
dest=/var/www/teply-hleb
test -f "$src/index.php"
test -f "$src/server/api.php"
test -f "$dest/server/config.php"
while IFS= read -r -d '' file; do php -l "$file"; done < <(find "$src" -name '*.php' -print0)
stamp=$(date +%Y%m%d-%H%M%S)
backup="/root/teply-hleb-backup-$stamp"
mkdir -m 700 "$backup"
cp -a "$dest/." "$backup/"
trap 'echo "Ошибка: резервная копия находится в $backup" >&2' ERR
while IFS= read -r -d '' file; do
  mv "$file" "$backup/$(basename "$file")"
done < <(find "$dest/server" -maxdepth 1 -name 'config.php.before-restore.*' -print0)
# Credentials and uploaded images are never overwritten.
tar -C "$src" --exclude='./server/config.php' --exclude='./uploads' -cf - . | tar -C "$dest" -xf -
php -l "$dest/server/api.php"
echo "Обновление установлено. Резервная копия: $backup"
curl --max-time 15 -sS -o /dev/null -w 'Каталог API: HTTP %{http_code}\n' 'http://150.241.124.248/server/api.php?route=products'
