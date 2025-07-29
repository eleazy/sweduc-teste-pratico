# Ajustar permissões no container
docker exec sweduc-app-1 chmod 777 /app/storage

# Ajustar ownershipt no container
docker exec sweduc-app-1 chown -R www-data:www-data /app/storage
