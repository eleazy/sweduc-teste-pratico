#!/bin/bash

# USO:
# Digitar comando com sudo para ter permissão de escrita de arquivos Ex:
# sudo bash limpa_dados_sensiveis.sh universo_sweduc.sql

# Oculta CPFs
sed -r 's/([0-9]{3}[,.-]){2}[0-9]{3}-[0-9]{2}/111.111.111-11/' $1 > "TMP0_$1";

# Oculta CNPJs
sed -r 's/[0-9]{2}([,.-][0-9]{3}){2}[/][0-9]{4}[,.-][0-9]{2}/11.111.111-0001-11/' "TMP0_$1" > "TMP1_$1";

# Oculta RGs (Na realidade qualquer sequencia com mais de 7 digitos seguidos)
sed -r "s/[0-9]{7}/1111111/"  "TMP1_$1" > "TMP2_$1";
sed -r 's/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/teste@mail.com/Ig' "TMP2_$1" > "LIMPO_$1";

# Use essa linha apenas se desejar abreviar todos as palavras do arquivo, ocultando todos os nomes
# No caso vc acaba ocultado todas as palavras
# sed 's/\B\w*//g;s/\s/ /g' "TMP2_$1" > "TMP3_$1";



rm -rf "TMP0_$1";
rm -rf "TMP1_$1";
rm -rf "TMP2_$1";
# rm -rf "TMP3_$1";
