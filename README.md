# Notas para rodar localhost

### Entrar no container
docker exec -it laravel_app bash
### Ajustar permissões 
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache
### Executar migrações
php artisan migrate