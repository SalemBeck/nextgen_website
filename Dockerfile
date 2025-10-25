FROM php:8.2-apache

# Install mysqli (this is the missing part)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy your project files into the container
COPY . /var/www/html/

# Optional: enable Apache rewrite module (if you use .htaccess)
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
