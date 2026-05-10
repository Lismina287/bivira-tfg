# Usar Imagen MoodleHQ como base
FROM moodlehq/moodle-php-apache:8.2

# Para instalar git y las herramientas necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Descargar el código de Moodle durante la construcción de la imagen
# para que venga con el Moodle dentro
RUN git clone -b MOODLE_403_STABLE git://git.moodle.org/moodle.git /var/www/html

# Ajustar permisos para que Apache pueda trabajar
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Exponer el puerto 80
EXPOSE 80

# Activar módulo SSL y configuración BiViR@
RUN a2enmod ssl && a2dissite default-ssl
