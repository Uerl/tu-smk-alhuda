#!/bin/bash
set -euo pipefail
APP=/var/www/tu/tu-laravel
cd "$APP"

mkdir -p bootstrap/cache storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views storage/app/public/bukti
chown -R admsmk:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ ! -f .env ]; then
  cp .env.example .env
  sudo -u admsmk php artisan key:generate --force
  echo "Berkas .env baru dibuat. Isi sandi database, lalu jalankan skrip ini lagi."
  exit 1
fi

sudo -u admsmk php artisan migrate --force
JUMLAH=$(sudo mysql -N -e "SELECT COUNT(*) FROM tu_alhuda.tahun_pelajaran" 2>/dev/null || echo 0)
if [ "$JUMLAH" = "0" ]; then
  sudo -u admsmk php artisan db:seed --force
fi
sudo -u admsmk php artisan storage:link || true

SOCK=$(ls /run/php/php*-fpm.sock | head -1)
cat > /etc/nginx/sites-available/tu << EOF
server {
    listen 8092;
    server_name 192.168.125.245;
    root /var/www/tu/tu-laravel/public;
    index index.php;
    client_max_body_size 8M;
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$SOCK;
    }
    location ~ /\\. {
        deny all;
    }
}
EOF
ln -sfn /etc/nginx/sites-available/tu /etc/nginx/sites-enabled/tu
nginx -t
systemctl reload nginx
chown -R www-data:www-data storage bootstrap/cache
echo "TU siap di http://192.168.125.245:8092"
