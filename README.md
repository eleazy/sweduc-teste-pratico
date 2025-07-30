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