#!/usr/bin/env bash
# Publica uma nova versão do SYSDAE em um servidor onde ele já foi instalado com deploy/instalar.sh.
# Rode a partir do pacote novo extraído:
#   sudo bash deploy/atualizar.sh
set -euo pipefail

[[ "$(id -u)" -eq 0 ]] || { echo "Rode com sudo: sudo bash deploy/atualizar.sh"; exit 1; }

ORIGEM="$(cd "$(dirname "$0")/.." && pwd)"
DESTINO=/var/www/sysdae
PHP_VERSAO=8.3

[[ -f "$DESTINO/.env" ]] || { echo "O SYSDAE não está instalado em $DESTINO. Use deploy/instalar.sh."; exit 1; }

passo() { echo; echo "==> $*"; }
artisan() { sudo -u www-data php "$DESTINO/artisan" "$@"; }

passo "Backup antes de atualizar"
/usr/local/bin/sysdae-backup.sh

passo "Colocando o sistema em manutenção"
artisan down --retry=60

# Se algo falhar daqui em diante, o sistema continua em manutenção para não rodar pela metade.
trap 'echo; echo "ERRO: a atualização parou. O sistema continua em manutenção."; echo "Corrija e rode de novo, ou restaure o backup de /var/backups/sysdae e depois: sudo -u www-data php $DESTINO/artisan up"' ERR

passo "Copiando a nova versão"
rsync -a --delete \
    --exclude='/.env' --exclude='/vendor/' --exclude='/storage/' \
    --exclude='/bootstrap/cache/' --exclude='/public/storage' \
    "$ORIGEM/" "$DESTINO/"

passo "Dependências PHP"
cd "$DESTINO"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

chown -R root:www-data "$DESTINO"
find "$DESTINO" -path "$DESTINO/storage" -prune -o -path "$DESTINO/bootstrap/cache" -prune -o -type d -exec chmod 755 {} +
find "$DESTINO" -path "$DESTINO/storage" -prune -o -path "$DESTINO/bootstrap/cache" -prune -o -type f -exec chmod 644 {} +
chown -R www-data:www-data "$DESTINO/storage" "$DESTINO/bootstrap/cache"
chmod 640 "$DESTINO/.env"

passo "Banco de dados e caches"
artisan migrate --force
artisan sysdae:permissoes-sincronizar
artisan optimize:clear
artisan optimize
artisan filament:optimize

# Limpa o cache de código do PHP para a versão nova valer na hora.
systemctl reload php${PHP_VERSAO}-fpm

trap - ERR
artisan up

passo "Atualização concluída"
