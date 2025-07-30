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

# Instruções da atividade 1 "Importação de notas permitindo caracteres não numéricos"

O sistema possui uma funcionalidade que permite importar notas através de um arquivo .csv,
porém é possível importar notas inválidas que quebram a execução em diversas partes do sistemas.
Deve se criar uma medida de segurança para impedir isso, alertando de algum modo ao usuário que
o arquivo contém notas inválidas e deve ser verificado, e nesse caso não prosseguir com a inserção 
das notas no banco de dados. 

obs: Existe uma exceção para a regra, notas do ensino infantil permitem caracteres não numéricos.

## Caminho para reproduzir o problema
1. Abrir o sistema no localhost, http://localhost/
2. Credenciais para login: usuario: "Suporte" senha: "senha"
3. Abrir no menu a primeira aba "Alunos"
4. Dentro da tela "Alunos | Busca", clicar em "Buscar"
5. Marcar a checkmark no primeiro aluno da listagem
6. Executar a ação "Boletim 1° Trimestre - Fundamental 1"
( O Boletim não irá carregar pois existem notas inválidas na tabela )
( Abaixo o processo que ocasionou tais notas inválidas )

7. Entrar na tela de lançamento de notas
8. Menu > Academico > Lançamento de Notas > Fundamental e Médio
9. Preencher filtros de busca com respectivamente: 
10. "2025", "Unidade 1", "qualquer opção", "1 Trimestre", "Prova de P1"
11. Clicar no botão "Importar CSV" e subir arquivo "notasInvalidas.csv"

## Arquivos e tabelas relacionadas ao problema
1. sweduc/src/View/Academico/Notas/Listar.php (raiz do problema)

### Tabelas
1. alunos_notas