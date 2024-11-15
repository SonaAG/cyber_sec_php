# Use a base image with PHP and Apache
FROM php:8.0-apache

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Install necessary system packages and PostgreSQL development libraries
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copy application files to the container
COPY . .

# Expose port 80 to make the application accessible
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]

