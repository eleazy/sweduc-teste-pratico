# Instruções da atividade 2 "Otimização do histórico escolar"

O histórico escolar dos alunos está apresentando um tempo de carregamento excessivo — cerca de 23 segundos em ambiente local e aproximadamente 5 minutos no servidor remoto.

A principal causa dessa lentidão é a execução de um grande número de consultas ao banco de dados dentro de loops. No entanto, existe uma abordagem alternativa, muito mais eficiente, para gerar o histórico escolar, que evita essas consultas repetitivas e melhora significativamente a performance. A proposta é revisar e otimizar o código atual para resolver esse problema de forma definitiva.

## Caminho para reproduzir o problema
1. Abrir o sistema no localhost, http://localhost/
2. Credenciais para login: usuario: "Suporte" senha: "senha"
3. Abrir no menu a primeira aba "Alunos"
4. Dentro da tela "Alunos | Busca", clicar em "Buscar"
5. No primeiro aluno na listagem, clicar na opção "HISTÓRICO"

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