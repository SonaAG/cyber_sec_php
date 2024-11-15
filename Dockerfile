# Use a base image with PHP and Apache
FROM php:8.0-apache

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Copy application files to the container
COPY . .

# Update Apache configuration to serve the public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Install necessary extensions for PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Set permissions for the public folder
RUN chmod -R 755 /var/www/html/public

# Expose port 80 for the application
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
