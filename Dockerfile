# Use a base image with PHP and Apache
FROM php:8.0-apache

# Set the working directory to /var/www/html (default for Apache)
WORKDIR /var/www/html

# Copy the current directory's contents into the container
COPY . .

# Install necessary extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Expose port 80 to make the application accessible
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
