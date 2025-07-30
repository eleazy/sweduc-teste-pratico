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

### (Caso Necessário) Installar packages
docker exec sweduc-app-1 composer install

### Entrar no phpmyadmin, criar nova base e importar os respectivos arquivos
http://localhost:8091/

base para atividade 1: db_atividade_importacao_de_notas.sql.zip
base para atividade 2: db_atividade_historico_escolar.sql.zip

obs: Descomprimir os arquivos antes de importar 

# Instruções da atividade 2 "Otimização do histórico escolar"

O histórico escolar dos alunos está demorando muito para abrir, cerca de 23 segundos em localhost,
e cerca de 5 minutos no servidor remoto, a proposta é que você chegue a uma solução para esse
problema de otimização. Essa lentidão se dá pois o arquivo faz muitas querys dentro de loops, porém
existe outra maneira de gerar histórico, de forma muito mais otimizada.

## Caminho para reproduzir o problema
1. Abrir o sistema no localhost, http://localhost/
2. Abrir no menu a primeira aba "Alunos"
3. Dentro da tela "Alunos | Busca", clicar em "Buscar"
4. No primeiro aluno na listagem, clicar na opção "HISTÓRICO"

## Arquivos e tabelas relacionadas ao problema
1. sweduc/public/alunos_historico_cadastra.php (raiz do problema)
2. sweduc/src/Academico/Controller/HistoricoController.php
3. sweduc/src/View/Academico/Aluno/Listar.php

### Tabelas
1. alunos_historico (principalmente)
2. cursos
3. series
4. disciplinas
5. grade