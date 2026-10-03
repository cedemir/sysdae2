#!/usr/bin/env bash
# Primeira instalação do SYSDAE em um servidor Ubuntu 24.04 (Apache + PHP 8.3 FPM + MySQL 8).
# Segue o "Guia de publicação do SYSDAE". Rode a partir do pacote extraído:
#   sudo bash deploy/instalar.sh
#
# As respostas podem vir de variáveis de ambiente (para rodar sem perguntas):
#   DOMINIO, ADMIN_NOME, ADMIN_EMAIL, ADMIN_SENHA, HTTPS=s|n
set -euo pipefail

[[ "$(id -u)" -eq 0 ]] || { echo "Rode com sudo: sudo bash deploy/instalar.sh"; exit 1; }

ORIGEM="$(cd "$(dirname "$0")/.." && pwd)"
DESTINO=/var/www/sysdae
PHP_VERSAO=8.3
BANCO=sysdae
CREDENCIAIS=/root/sysdae-credenciais.txt

passo() { echo; echo "==> $*"; }

# Comandos do artisan rodam como o usuário do servidor web, para que caches e logs fiquem com o dono certo.
artisan() { sudo -u www-data php "$DESTINO/artisan" "$@"; }

# Define CHAVE=valor no .env (troca a linha existente, descomenta ou acrescenta).
definir() {
    local chave="$1" valor="$2" arquivo="$DESTINO/.env"
    local escapado
    escapado=$(printf '%s' "$valor" | sed -e 's/[\/&|]/\\&/g')
    if grep -qE "^${chave}=" "$arquivo"; then
        sed -i -E "s|^${chave}=.*|${chave}=${escapado}|" "$arquivo"
    elif grep -qE "^#\s*${chave}=" "$arquivo"; then
        sed -i -E "s|^#\s*${chave}=.*|${chave}=${escapado}|" "$arquivo"
    else
        printf '%s=%s\n' "$chave" "$valor" >> "$arquivo"
    fi
}

ler_env() { grep -E "^$1=" "$DESTINO/.env" 2>/dev/null | head -1 | cut -d= -f2- || true; }

senha_aleatoria() { openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 28; }

# ---------------------------------------------------------------- perguntas
[[ -n "${DOMINIO:-}" ]]     || read -rp "Domínio do sistema (ex.: sysdae.instituicao.edu.br): " DOMINIO
[[ -n "${ADMIN_NOME:-}" ]]  || read -rp "Nome do primeiro administrador: " ADMIN_NOME
[[ -n "${ADMIN_EMAIL:-}" ]] || read -rp "E-mail do primeiro administrador: " ADMIN_EMAIL
ADMIN_SENHA="${ADMIN_SENHA:-}"
while [[ ${#ADMIN_SENHA} -lt 8 ]]; do
    read -rsp "Senha do administrador (mínimo 8 caracteres): " ADMIN_SENHA; echo
done
[[ -n "${HTTPS:-}" ]] || read -rp "Ativar HTTPS (Let's Encrypt) agora? O domínio já precisa apontar para este servidor. [s/N]: " HTTPS
[[ "${HTTPS,,}" == s* ]] && HTTPS=s || HTTPS=n

# ---------------------------------------------------------------- 1. pacotes
passo "Instalando os pacotes (Apache, PHP $PHP_VERSAO FPM, MySQL)"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get upgrade -yq
apt-get install -yq apache2 mysql-server unzip git rsync openssl composer unattended-upgrades \
    php${PHP_VERSAO}-fpm php${PHP_VERSAO}-cli php${PHP_VERSAO}-mysql php${PHP_VERSAO}-mbstring \
    php${PHP_VERSAO}-xml php${PHP_VERSAO}-curl php${PHP_VERSAO}-zip php${PHP_VERSAO}-bcmath \
    php${PHP_VERSAO}-intl php${PHP_VERSAO}-gd

# O guia usa o PHP-FPM; o mod_php não pode estar ativo junto.
if dpkg -l "libapache2-mod-php${PHP_VERSAO}" 2>/dev/null | grep -q '^ii'; then
    a2dismod "php${PHP_VERSAO}" >/dev/null 2>&1 || true
    apt-get remove -yq "libapache2-mod-php${PHP_VERSAO}"
fi

# Atualizações de segurança automáticas.
cat > /etc/apt/apt.conf.d/20auto-upgrades <<'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
EOF

# ---------------------------------------------------------------- 2. firewall
passo "Firewall: liberando só SSH e web (a porta do MySQL fica fechada)"
ufw allow OpenSSH >/dev/null
ufw allow 'Apache Full' >/dev/null
ufw --force enable

# ---------------------------------------------------------------- 3. código
passo "Copiando o sistema para $DESTINO"
mkdir -p "$DESTINO"
rsync -a --delete \
    --exclude='/.env' --exclude='/vendor/' --exclude='/storage/' \
    --exclude='/bootstrap/cache/' --exclude='/public/storage' \
    "$ORIGEM/" "$DESTINO/"

mkdir -p "$DESTINO"/storage/app/{private,public} \
         "$DESTINO"/storage/framework/{cache/data,sessions,views} \
         "$DESTINO"/storage/logs "$DESTINO"/bootstrap/cache

[[ -f "$DESTINO/public/.htaccess" ]] || { echo "ERRO: public/.htaccess não veio no pacote."; exit 1; }

# ---------------------------------------------------------------- 4. banco
passo "Banco de dados MySQL"
# Reaproveita as senhas se o script já rodou antes (reinstalação).
SENHA_APP=$(ler_env DB_PASSWORD)
[[ -n "$SENHA_APP" ]] || SENHA_APP=$(senha_aleatoria)
if [[ -f /root/.sysdae-mysql.cnf ]]; then
    SENHA_BACKUP=$(grep '^password=' /root/.sysdae-mysql.cnf | cut -d= -f2-)
else
    SENHA_BACKUP=$(senha_aleatoria)
fi

mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${BANCO} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'sysdae_app'@'localhost' IDENTIFIED BY '${SENHA_APP}';
ALTER USER 'sysdae_app'@'localhost' IDENTIFIED BY '${SENHA_APP}';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES ON ${BANCO}.* TO 'sysdae_app'@'localhost';
CREATE USER IF NOT EXISTS 'sysdae_backup'@'localhost' IDENTIFIED BY '${SENHA_BACKUP}';
ALTER USER 'sysdae_backup'@'localhost' IDENTIFIED BY '${SENHA_BACKUP}';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES ON ${BANCO}.* TO 'sysdae_backup'@'localhost';
FLUSH PRIVILEGES;
SQL

# O MySQL do Ubuntu já escuta só em 127.0.0.1 e o root entra apenas pelo sudo (auth_socket).

# ---------------------------------------------------------------- 5. .env
passo "Configuração de produção (.env)"
[[ -f "$DESTINO/.env" ]] || cp "$DESTINO/.env.example" "$DESTINO/.env"

if [[ "$HTTPS" == s ]]; then URL="https://$DOMINIO"; COOKIE_SEGURO=true; else URL="http://$DOMINIO"; COOKIE_SEGURO=false; fi

definir APP_NAME SYSDAE
definir APP_ENV production
definir APP_DEBUG false
definir APP_URL "$URL"
definir APP_LOCALE pt_BR
definir APP_FALLBACK_LOCALE pt_BR
definir APP_FAKER_LOCALE pt_BR
definir APP_TIMEZONE America/Sao_Paulo
definir LOG_CHANNEL daily
definir LOG_STACK daily
definir LOG_LEVEL warning
definir LOG_DAILY_DAYS 14
definir DB_CONNECTION mysql
definir DB_HOST 127.0.0.1
definir DB_PORT 3306
definir DB_DATABASE "$BANCO"
definir DB_USERNAME sysdae_app
definir DB_PASSWORD "$SENHA_APP"
definir SESSION_DRIVER database
definir SESSION_SECURE_COOKIE "$COOKIE_SEGURO"

# ---------------------------------------------------------------- 6. dependências e permissões
passo "Instalando as dependências PHP (composer)"
cd "$DESTINO"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# Estes dois escrevem no .env e em public/, que o servidor web não pode alterar: rodam como root.
[[ -n "$(ler_env APP_KEY)" ]] || php artisan key:generate --force
php artisan storage:link --force

passo "Ajustando dono e permissões"
# O código fica só para leitura do servidor web; ele escreve apenas em storage/ e bootstrap/cache/.
chown -R root:www-data "$DESTINO"
find "$DESTINO" -type d -exec chmod 755 {} +
find "$DESTINO" -type f -exec chmod 644 {} +
chown -R www-data:www-data "$DESTINO/storage" "$DESTINO/bootstrap/cache"
chmod -R u+rwX,g+rwX,o-rwx "$DESTINO/storage" "$DESTINO/bootstrap/cache"
chmod 640 "$DESTINO/.env"

# ---------------------------------------------------------------- 7. preparar o sistema
passo "Preparando o sistema (chave, tabelas, perfis)"
artisan migrate --force
artisan db:seed --class=PerfisSeeder --force
artisan sysdae:permissoes-sincronizar

if [[ "$(mysql -N -e "SELECT COUNT(*) FROM ${BANCO}.users WHERE email = '${ADMIN_EMAIL//\'/}'")" -gt 0 ]]; then
    echo "O usuário $ADMIN_EMAIL já existe: só confirmo o perfil de administrador."
else
    artisan make:filament-user --name="$ADMIN_NOME" --email="$ADMIN_EMAIL" --password="$ADMIN_SENHA" --no-interaction
fi
artisan sysdae:perfil "$ADMIN_EMAIL" admin

artisan optimize
artisan filament:optimize

# ---------------------------------------------------------------- 8. PHP
passo "Limites de envio de arquivos (anexos e fotos)"
cat > /etc/php/${PHP_VERSAO}/fpm/conf.d/99-sysdae.ini <<'EOF'
upload_max_filesize = 12M
post_max_size = 14M
memory_limit = 256M
expose_php = Off
EOF
systemctl restart php${PHP_VERSAO}-fpm

# ---------------------------------------------------------------- 9. Apache
passo "Apache"
a2enmod rewrite headers proxy_fcgi setenvif >/dev/null
a2enconf php${PHP_VERSAO}-fpm >/dev/null

cat > /etc/apache2/sites-available/sysdae.conf <<EOF
<VirtualHost *:80>
    ServerName ${DOMINIO}
    DocumentRoot ${DESTINO}/public

    <Directory ${DESTINO}/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    ErrorLog \${APACHE_LOG_DIR}/sysdae-error.log
    CustomLog \${APACHE_LOG_DIR}/sysdae-access.log combined
</VirtualHost>
EOF

# Não mostra versão do Apache nem do sistema nas páginas de erro.
cat > /etc/apache2/conf-available/sysdae-seguranca.conf <<'EOF'
ServerTokens Prod
ServerSignature Off
EOF
a2enconf sysdae-seguranca >/dev/null

a2dissite 000-default >/dev/null 2>&1 || true
a2ensite sysdae >/dev/null
apache2ctl configtest
systemctl reload apache2

# ---------------------------------------------------------------- 10. HTTPS
if [[ "$HTTPS" == s ]]; then
    passo "HTTPS (Let's Encrypt)"
    apt-get install -yq certbot python3-certbot-apache
    certbot --apache -d "$DOMINIO" --non-interactive --agree-tos -m "$ADMIN_EMAIL" --redirect
fi

# ---------------------------------------------------------------- 11. backup
passo "Backup diário (banco e arquivos, às 2h, guarda 14 dias)"
cat > /root/.sysdae-mysql.cnf <<EOF
[client]
user=sysdae_backup
password=${SENHA_BACKUP}
EOF
chmod 600 /root/.sysdae-mysql.cnf

cat > /usr/local/bin/sysdae-backup.sh <<EOF
#!/usr/bin/env bash
set -euo pipefail

DESTINO=/var/backups/sysdae
DATA=\$(date +%F)

mkdir -p "\$DESTINO"
chmod 700 "\$DESTINO"

mysqldump --defaults-extra-file=/root/.sysdae-mysql.cnf \\
    --single-transaction --no-tablespaces --routines ${BANCO} \\
    | gzip > "\$DESTINO/banco-\$DATA.sql.gz"

tar -czf "\$DESTINO/arquivos-\$DATA.tar.gz" -C ${DESTINO}/storage/app private public

# guarda 14 dias
find "\$DESTINO" -type f -mtime +14 -delete
EOF
chmod 700 /usr/local/bin/sysdae-backup.sh

echo "0 2 * * * root /usr/local/bin/sysdae-backup.sh" > /etc/cron.d/sysdae-backup
chmod 644 /etc/cron.d/sysdae-backup
/usr/local/bin/sysdae-backup.sh

# ---------------------------------------------------------------- resumo
cat > "$CREDENCIAIS" <<EOF
SYSDAE - credenciais geradas na instalação ($(date '+%d/%m/%Y %H:%M'))
Endereço:              $URL/admin
Administrador:         $ADMIN_EMAIL
MySQL (sistema):       sysdae_app / $SENHA_APP
MySQL (backup):        sysdae_backup / $SENHA_BACKUP
Guarde estas senhas em local seguro e apague este arquivo depois.
EOF
chmod 600 "$CREDENCIAIS"

passo "Instalação concluída"
echo "Acesse: $URL/admin  (login: $ADMIN_EMAIL)"
echo "Senhas do MySQL geradas: $CREDENCIAIS (só o root lê)"
echo "Backups: /var/backups/sysdae"
[[ "$HTTPS" == s ]] || echo "Sem HTTPS por enquanto. Quando o domínio apontar para o servidor: sudo HTTPS=s bash deploy/instalar.sh"
echo "Confira a lista do item 11 do guia (Conferência depois de publicar)."
