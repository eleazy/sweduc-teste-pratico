# Teste prático SWsistemas 

Atividades para teste prático de habilidades

São propostas duas atividades: 
1: Importação de notas permitindo caracteres não numéricos (branch: atividade-importacao-de-notas)
2? Otimização do histórico escolar (branch: atividade-otimizacao-historico-escolar)

## Instruções para rodar o sistema no localhost

### Entrar no diretório e fazer a build dos containers
cd sweduc
docker compose build
docker compose up -d

### Ajustar permissões no container
docker exec sweduc-app-1 chmod 777 /app/storage

### Ajustar ownership no container
docker exec sweduc-app-1 chown -R www-data:www-data /app/storage

### Entrar no phpmyadmin, criar nova base e importar os respectivos arquivos
http://localhost:8091/

base para atividade 1: db_atividade_importacao_de_notas.zip
base para atividade 2: db_atividade_historico_escolar.zip