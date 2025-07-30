# Teste prático SWsistemas 

São propostas duas atividades:<br>
1: Importação de notas permitindo caracteres não numéricos <br>(branch: atividade-importacao-de-notas)<br>
2: Otimização do histórico escolar <br>(branch: atividade-otimizacao-historico-escolar)<br>

## Instruções para rodar o sistema no localhost

### Entrar numa das branchs
```
git checkout atividade-importacao-de-notas
```
```
git checkout atividade-otimizacao-historico-escolar
```

### Entrar no diretório e fazer a build dos containers
```
cd sweduc
```
```
docker compose build
```
```
docker compose up -d
```

### Ajustar permissões no container
```
docker exec sweduc-app-1 chmod 777 /app/storage
```

### Ajustar ownership no container
```
docker exec sweduc-app-1 chown -R www-data:www-data /app/storage
```

### (Caso Necessário) Installar packages
```
docker exec sweduc-app-1 composer install
```

### Entrar no phpmyadmin, criar nova base e importar os respectivos arquivos
http://localhost:8091/

base para atividade 1: db_atividade_importacao_de_notas.sql.zip
base para atividade 2: db_atividade_historico_escolar.sql.zip

obs: Descomprimir os arquivos antes de importar 

### Sistema ficará disponível em 
http://localhost/