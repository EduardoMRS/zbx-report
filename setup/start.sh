#!/bin/bash

# Script para instalação automatizada do Zabbix Report
# Inclui configurações de PHP e Apache específicas

# Verifica se é root
if [ "$(id -u)" -ne 0 ]; then
    echo "Este script precisa ser executado como root ou com sudo."
    exit 1
fi

# Atualiza lista de pacotes
echo "Atualizando lista de pacotes..."
apt-get update

# Instala Apache
if ! command -v apache2 &> /dev/null; then
    echo "Instalando Apache..."
    apt-get install -y apache2
    a2enmod rewrite
    systemctl enable apache2
    systemctl start apache2
else
    echo "Apache já está instalado. Verificando mod_rewrite..."
    a2enmod rewrite
    systemctl restart apache2
fi

# Instala PHP e extensões necessárias
if ! command -v php &> /dev/null; then
    echo "Instalando PHP e extensões..."
    apt-get install -y php php-cli php-common php-mbstring php-xml php-zip php-curl php-gd php-fileinfo php-gettext php-imagick php-exif php-openssl
else
    echo "PHP já está instalado. Verificando extensões..."
fi

# Configurações do PHP
echo "Configurando PHP..."
PHP_INI_PATH=$(php -i | grep "Loaded Configuration File" | awk '{print $5}')

# Descomenta extensões necessárias no php.ini
sed -i 's/;extension=zip/extension=zip/' "$PHP_INI_PATH"
sed -i 's/;extension=gd/extension=gd/' "$PHP_INI_PATH"
sed -i 's/;extension=curl/extension=curl/' "$PHP_INI_PATH"
sed -i 's/;extension=fileinfo/extension=fileinfo/' "$PHP_INI_PATH"
sed -i 's/;extension=gettext/extension=gettext/' "$PHP_INI_PATH"
sed -i 's/;extension=imagick/extension=imagick/' "$PHP_INI_PATH"
sed -i 's/;extension=exif/extension=exif/' "$PHP_INI_PATH"
sed -i 's/;extension=openssl/extension=openssl/' "$PHP_INI_PATH"

# Ajusta configurações recomendadas
sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_INI_PATH"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_INI_PATH"
sed -i 's/post_max_size = .*/post_max_size = 50M/' "$PHP_INI_PATH"
sed -i 's/max_execution_time = .*/max_execution_time = 120/' "$PHP_INI_PATH"

systemctl restart apache2

# Instala Composer
if ! command -v composer &> /dev/null; then
    echo "Instalando Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    echo "Composer já está instalado."
fi

# Instala pip (Python)
if ! command -v pip &> /dev/null; then
    echo "Instalando pip..."
    apt-get install -y python3-pip
else
    echo "pip já está instalado."
fi

# Instala Node.js e npm
if ! command -v npm &> /dev/null; then
    echo "Instalando Node.js e npm..."
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
    apt-get install -y nodejs
else
    echo "npm já está instalado."
fi

# Instala Git se não estiver instalado
if ! command -v git &> /dev/null; then
    echo "Instalando Git..."
    apt-get install -y git
else
    echo "Git já está instalado."
fi

# Clona o repositório
REPO_URL="https://github.com/ThomasJPF/Envia-Relatorio-Zabbix-UNI.git"
PROJECT_DIR="/var/www/zabbix-report"

if [ -d "$PROJECT_DIR" ]; then
    echo "O diretório do projeto já existe. Atualizando repositório..."
    cd "$PROJECT_DIR" || exit
    git pull origin master
else
    echo "Clonando repositório..."
    git clone "$REPO_URL" "$PROJECT_DIR"
    cd "$PROJECT_DIR" || exit
fi

# Configura permissões
echo "Configurando permissões..."
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
find "$PROJECT_DIR" -type d -exec chmod 755 {} \;
find "$PROJECT_DIR" -type f -exec chmod 644 {} \;

# Pergunta sobre o caminho de acesso
echo "Configuração do acesso:"
echo "1. Acesso pela raiz do domínio (ex: http://zabbix-report.local)"
echo "2. Acesso por subpasta (ex: http://zabbix-report.local/pasta)"
read -p "Escolha a opção (1 ou 2): " ACCESS_OPTION

if [ "$ACCESS_OPTION" == "2" ]; then
    read -p "Digite o nome da subpasta (sem barras no início/fim): " SUBFOLDER
    DOCUMENT_ROOT="$PROJECT_DIR/public"
    ALIAS_PATH="/$SUBFOLDER"
else
    DOCUMENT_ROOT="$PROJECT_DIR/public"
    ALIAS_PATH=""
fi

# Configuração do Apache para o Zabbix Report
echo "Configurando Apache para Zabbix Report..."
APACHE_CONF="/etc/apache2/sites-available/zabbix-report.conf"

if [ "$ACCESS_OPTION" == "2" ]; then
    cat > "$APACHE_CONF" <<EOL
<VirtualHost *:80>
    ServerName zabbix-report.local
    DocumentRoot /var/www/html
    
    Alias $ALIAS_PATH "$DOCUMENT_ROOT"
    <Directory "$DOCUMENT_ROOT">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        FallbackResource $ALIAS_PATH/index.php
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/zabbix-report_error.log
    CustomLog \${APACHE_LOG_DIR}/zabbix-report_access.log combined
</VirtualHost>
EOL
else
    cat > "$APACHE_CONF" <<EOL
<VirtualHost *:80>
    ServerName zabbix-report.local
    ServerAlias $(hostname -I | awk '{print $1}')
    DocumentRoot $DOCUMENT_ROOT
    
    <Directory $DOCUMENT_ROOT>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/zabbix-report_error.log
    CustomLog \${APACHE_LOG_DIR}/zabbix-report_access.log combined
</VirtualHost>
EOL
fi

# Ativa o site e desativa o default
a2ensite zabbix-report.conf
a2dissite 000-default.conf
systemctl restart apache2

# Executa composer update
echo "Executando composer update..."
sudo -u www-data composer install --working-dir="$PROJECT_DIR"
sudo -u www-data composer update --working-dir="$PROJECT_DIR"

# Configura a cron job automática
echo "Configurando cron job automática..."
CRON_JOB="0 8 * * * /usr/bin/php $PROJECT_DIR/cron/auto-send-mail.php > $PROJECT_DIR/storage/logs/cron.log 2>&1"
CRON_TEMP_FILE="/tmp/crontab_temp"

# Verifica se a cron job já existe
if ! crontab -u www-data -l | grep -q "auto-send-mail.php"; then
    echo "Adicionando cron job para execução diária às 8h..."
    (crontab -u www-data -l 2>/dev/null; echo "$CRON_JOB") | crontab -u www-data -
    
    # Cria o diretório de logs se não existir
    mkdir -p "$PROJECT_DIR/storage/logs"
    chown www-data:www-data "$PROJECT_DIR/storage/logs"
    chmod 755 "$PROJECT_DIR/storage/logs"
    
    echo "Cron job configurada com sucesso!"
else
    echo "Cron job já existe, mantendo configuração atual."
fi

echo "Instalação concluída com sucesso!"
echo "--------------------------------"
if [ "$ACCESS_OPTION" == "2" ]; then
    echo "Acesse o Zabbix Report em: http://zabbix-report.local/$SUBFOLDER"
    echo "Ou via IP: http://$(hostname -I | awk '{print $1}')/$SUBFOLDER"
else
    echo "Acesse o Zabbix Report em: http://zabbix-report.local"
    echo "Ou via IP: http://$(hostname -I | awk '{print $1}')"
fi
echo "Diretório do projeto: $PROJECT_DIR"
echo "Configuração do Apache: $APACHE_CONF"
echo "Configuração inicial: http://zabbix-report.local/$SUBFOLDER/setup/"
echo "Configuração inicial: http://$(hostname -I | awk '{print $1}')/$SUBFOLDER/setup/"