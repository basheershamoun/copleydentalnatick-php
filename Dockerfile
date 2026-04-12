FROM php:7.4-apache

RUN a2enmod rewrite

# Serve the project from /var/www/html (mounted at runtime).
# AllowOverride None makes Apache ignore the production .htaccess
# (which force-redirects HTTP -> HTTPS and would break local dev).
# We replicate the one rewrite that actually matters: route every
# non-file request through index.php.
RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html' \
    '    <Directory /var/www/html>' \
    '        Options -Indexes +FollowSymLinks' \
    '        AllowOverride None' \
    '        Require all granted' \
    '        RewriteEngine On' \
    '        RewriteCond %{REQUEST_FILENAME} !-f' \
    '        RewriteRule . /index.php [L]' \
    '    </Directory>' \
    '    ErrorLog ${APACHE_LOG_DIR}/error.log' \
    '    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf
