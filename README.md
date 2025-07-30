# Instruções da atividade 2 "Otimização do histórico escolar"

O histórico escolar dos alunos está demorando muito para abrir, cerca de 23 segundos em localhost,
e cerca de 5 minutos no servidor remoto, a proposta é que você chegue a uma solução para esse
problema de otimização. Essa lentidão se dá pois o arquivo faz muitas querys dentro de loops, porém
existe outra maneira de gerar histórico, de forma muito mais otimizada.

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