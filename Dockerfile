FROM php:8.0-slim

WORKDIR /var/www/html

VOLUME ["/var/www/html"]

COPY . /var/www/html

EXPOSE 80

CMD ["service", "nginx", "restart"]  # Or adjust for Apache
