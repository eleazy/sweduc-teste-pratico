<?php

declare(strict_types=1);

namespace App\Sistema\Documento;

use App\Academico\Model\Matricula;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;

class DocumentoAlunosRenderer
{
    public function loopMatricula(string &$docData, array $context)
    {
        $matricula = Matricula::where('idaluno', $context['alunoId'])
            ->where('nummatricula', $context['nummatricula'])
            ->sole();

        $docData = preg_replace_callback_array(array_merge(
            $this->alunoVars($matricula),
            $this->matriculaVars($matricula),
            $this->turmaVars($matricula),
            $this->anamneseVars($matricula),
            $this->responsaveisVars($matricula),
            $this->historicoVars($matricula),
            $this->mensalidadeVars($matricula),
        ), $docData);
    }

    private function alunoVars(Matricula $matricula)
    {
        $aluno = $matricula->aluno;

        return [
            '/%ALUNO_EVOLUCIONAL%/' => fn() => (string) $aluno->evolucional_ref,
        ];
    }

    private function matriculaVars(Matricula $matricula)
    {
        return [
            '/%MATRICULA_SITUACAO%/' => fn() => (string) $matricula->statusTexto,
            '/%MATRICULA_SITUACAO_DATA%/' => fn() => self::dateLocal($matricula->datastatus),
            '/%MATRICULA_DATA%/' => fn() => self::dateLocal($matricula->datamatricula),
            '/%MATRICULA_NUMERO%/' => fn() => $matricula->nummatricula,
            '/%MATRICULA_PRESENCIAL%/' => fn() => $matricula->presencial ? 'Presencial' : 'À distancia',
            '/%MATRICULA_ANUIDADE%/' => fn() => self::moneyFormat($matricula->valorAnuidade),
            '/%MATRICULA_ANUIDADE_LIQUIDO%/' => fn() => self::moneyFormat(($matricula->valorAnuidade - $matricula->bolsa)),
            '/%MATRICULA_BOLSA%/' => fn() => self::moneyFormat($matricula->bolsa),
            '/%MATRICULA_BOLSA_PERCENTUAL%/' => fn() => $matricula->bolsapercentual,
            '/%MATRICULA_QTD_PARCELAS%/' => fn() => $matricula->qtdparcelas,
            '/%MATRICULA_VALOR_PARCELAS%/' => fn() => self::moneyFormat((($matricula->valorAnuidade - $matricula->bolsa) / ($matricula->qtdparcelas ?: 1))),
            '/%MATRICULA_VALOR_PARCELAS_DESC_(\d+)%/' => function ($desc) use ($matricula) {
                $valParcelas = ($matricula->valorAnuidade - $matricula->bolsa) / ($matricula->qtdparcelas ?: 1);
                return self::moneyFormat($valParcelas - ($valParcelas * ($desc[1] / 100)));
            },
            '/%MATRICULA_VALORBRUTO_PARCELAS%/' => fn() => self::moneyFormat((($matricula->valorAnuidade) / ($matricula->qtdparcelas ?: 1))),
        ];
    }

    private function mensalidadeVars(Matricula $matricula)
    {
        $mat = $matricula['id'];
        $query = "SELECT FI.valor
                  FROM alunos_fichaitens FI
                  INNER JOIN alunos_fichafinanceira F ON (FI.idalunos_fichafinanceira = F.id)
                  INNER JOIN alunos_matriculas AM ON (F.idaluno = AM.idaluno)
                  WHERE
                  AM.id = $mat
                  AND F.situacao = 0
                  AND F.datavencimento > '" . (1 + date("Y")) . "-01-01'
                  AND F.datavencimento < '" . (1 + date("Y")) . "-02-01'
                  AND ( FI.eventofinanceiro LIKE '%ensalidade%'
                  OR FI.eventofinanceiro LIKE '%ensino%')";
        $result = mysql_query($query);
        $row = mysql_fetch_object($result);

        $query2 = "SELECT F.bolsapercentual
                  FROM alunos_fichaitens FI
                  INNER JOIN alunos_fichafinanceira F ON (FI.idalunos_fichafinanceira = F.id)
                  INNER JOIN alunos_matriculas AM ON (F.idaluno = AM.idaluno)
                  WHERE
                  AM.id = $mat
                  AND F.situacao = 0
                  AND F.datavencimento > '" . (1 + date("Y")) . "-01-01'
                  AND F.datavencimento < '" . (1 + date("Y")) . "-02-01'
                  AND ( FI.eventofinanceiro LIKE '%ensalidade%'
                  OR FI.eventofinanceiro LIKE '%ensino%')";
        $result2 = mysql_query($query2);
        $row2 = mysql_fetch_object($result2);

        $query3 = "SELECT E.razaosocial
                  FROM alunos_matriculas AM
                  INNER JOIN empresas E ON (AM.idempresa = E.id)
                  WHERE AM.id = $mat";
        $result3 = mysql_query($query3);
        $row3 = mysql_fetch_object($result3);

        $query4 = "SELECT C.nomeb
                  FROM alunos_fichaitens FI
                  INNER JOIN alunos_fichafinanceira F ON (FI.idalunos_fichafinanceira = F.id)
                  INNER JOIN contasbanco C ON (F.idcontasbanco = C.id)
                  INNER JOIN alunos_matriculas AM ON (F.idaluno = AM.idaluno)
                  WHERE
                  AM.id = $mat
                  AND F.situacao = 0
                  AND F.datavencimento > '" . (1 + date("Y")) . "-01-01'
                  AND F.datavencimento < '" . (1 + date("Y")) . "-02-01'
                  AND ( FI.eventofinanceiro LIKE '%ensalidade%'
                  OR FI.eventofinanceiro LIKE '%ensino%')";
        $result4 = mysql_query($query4);
        $row4 = mysql_fetch_object($result4);

        return [
            '/%MATRICULA_VALORBRUTO_MENSALIDADE%/' => fn() => self::moneyFormat($row->valor),
            '/%MATRICULA_VALORBRUTO_BOLSA_PERCENTUAL%/' => fn() => (($row2->bolsapercentual) . ' %'),
            '/%MATRICULA_VALOR_BRUTO_MENSALIDADE_ESPERADO_MES_ANO(\d+)_(\d+)%/' => function ($params) use ($matricula) {
                $mes = $params[1];
                $ano = $params[2];
                $query = "SELECT FI.valor - F.bolsa AS valorEsperado
                          FROM alunos_fichaitens FI
                          INNER JOIN alunos_fichafinanceira F ON (FI.idalunos_fichafinanceira = F.id)
                          INNER JOIN alunos_matriculas AM ON (F.idaluno = AM.idaluno)
                          WHERE
                          AM.id = $matricula->id
                          AND F.situacao IN (0,1,6,5)
                          AND F.datavencimento >= '$ano-$mes-01'
                          AND F.datavencimento <= '$ano-$mes-31'
                          AND (
                            LOWER(FI.eventofinanceiro) LIKE '%ensalidade%'
                            OR LOWER(FI.eventofinanceiro) LIKE '%ensino%'
                        )";
                $result = mysql_query($query);
                $row = mysql_fetch_object($result);
                return self::moneyFormat($row->valorEsperado);
            },
            '/%MATRICULA_EMPRESA%/' => fn() => $row3->razaosocial,
            '/%MATRICULA_BANCO_BOLETO%/' => fn() => $row4->nomeb,
            '/%LINK_BOLETO_ASAAS@(\d+)%/' => function ($params) {
                $linkBoletoQ = "
                    SELECT ac.link_boleto
                    FROM asaas_cobrancas ac
                    JOIN alunos_fichafinanceira af ON af.id = ac.id_alunos_fichafinanceira
                    WHERE af.titulo = ?
                ";
                $linkBoleto = DB::select($linkBoletoQ, [$params[1]]);

                if (empty($linkBoleto)) {
                    return 'Link do boleto não encontrado';
                }

                $linkBoleto = $linkBoleto[0]->link_boleto;
                return $linkBoleto;
            },
        ];
    }

    private function turmaVars(Matricula $matricula)
    {
        $bolsa = 0;

        try {
            if ($matricula->bolsa) {
                $bolsa = 1 - (($matricula->valorAnuidade - $matricula->bolsa) / $matricula->valorAnuidade);
            }

            if ($matricula->bolsapercentual) {
                $bolsa = $matricula->bolsapercentual / 100;
            }
        } catch (\DivisionByZeroError) {
            //
        }

        $anuidade = $matricula->turma->serie->proxima->valorAnuidade;
        $desconto = $anuidade * $bolsa;
        $anuidadeLiquida = $anuidade - $desconto;

        return [
            '/%TURMA_ENTRADA%/' => fn() => $matricula->turma->entrada,
            '/%TURMA_SAIDA%/' => fn() => $matricula->turma->saida,
            '/%TURMA_CURSO%/' => fn() => $matricula->turma->serie->curso->curso,
            '/%TURMA_SERIE%/' => fn() => $matricula->turma->serie->serie,
            '/%TURMA_SERIE_ANUIDADE%/' => fn() => self::moneyFormat($matricula->turma->serie->valorAnuidade),
            '/%TURMA_SERIE_PRIMEIRA_PARCELA_ANUIDADE%/' => fn() => self::moneyFormat($matricula->turma->serie->primeira_parcela_anuidade),
            '/%TURMA_SERIE_MENSALIDADE%/' => fn() => self::moneyFormat($matricula->turma->serie->valorAnuidade / 12),
            '/%TURMA_SERIE_MENSALIDADE_DESC_(\d+)%/' => function ($desc) use ($matricula) {
                $valParcelas = $matricula->turma->serie->valorAnuidade / 12;
                return self::moneyFormat($valParcelas - ($valParcelas * ($desc[1] / 100)));
            },
            '/%CARTAO_RESPOSTA_(\d+)_(\d+)%/' => function ($params) {
                $rows = $params[1];
                $cols = $params[2];

                $html = '<div style="display: flex; align-items: center; margin-bottom:20px;">
                    <div>
                        <p>Certo</p>
                        <div style="width: 20px; height: 20px; background-color: black;"></div>
                    </div>
                    <div style="margin-left: 30px;">
                        <p>Errado</p>
                        <div style="display: flex;">
                            <div style="width: 20px; height: 20px; border: 1px solid black; text-align: center;">X</div>
                            <div style="width: 20px; height: 20px; border: 1px solid black; text-align: center;">✔️</div>
                            <div style="width: 20px; height: 20px; border: 1px solid black; text-align: center;">●</div>
                        </div>
                    </div>
                </div>';

                $html .= '<div style="display: flex; flex-wrap: wrap; justify-content: space-between;">';

                for ($i = 1; $i <= $rows; $i++) {
                    // Limita as linhas em 20, inicia uma nova tabela a cada 20 linhas
                    if ($i == 1 || ($i - 1) % 20 == 0) {
                        if ($i > 1) {
                            $html .= '</table></div>';
                        }
                        $html .= '<div style="margin-right: 20px;"><table border="1" cellspacing="0" cellpadding="5" style="margin: 0 auto; border-collapse: collapse; border-color: black;">';
                    }

                    $bgColor = $i % 2 == 0 ? 'style="background-color: lightgray;"' : '';
                    $html .= "<tr $bgColor>";
                    $html .= '<td>' . $i . '</td>';
                    for ($j = 0; $j < $cols; $j++) {
                        $html .= '<td>' . chr(65 + $j) . '</td>';
                    }
                    $html .= '</tr>';
                }

                $html .= '</table></div>';

                $html .= '</div>';

                return $html;
            },

            '/%TURMA_PROX_CURSO%/' => fn() => $matricula->turma->serie->proxima->curso->curso,
            '/%TURMA_PROX_SERIE%/' => fn() => $matricula->turma->serie->proxima->serie,
            '/%TURMA_PROX_SERIE_ANUIDADE_BRUTA%/' => fn() => self::moneyFormat($matricula->turma->serie->proxima->valorAnuidade + $matricula->turma->serie->proxima->primeira_parcela_anuidade),
            '/%TURMA_PROX_SERIE_ANUIDADE_CBOLSA%/' => fn() => self::moneyFormat($anuidadeLiquida + $matricula->turma->serie->proxima->primeira_parcela_anuidade),
            '/%TURMA_PROX_SERIE_ANUIDADE%/' => fn() => self::moneyFormat($matricula->turma->serie->proxima->valorAnuidade),
            '/%TURMA_PROX_SERIE_PRIMEIRA_PARCELA_ANUIDADE%/' => fn() => self::moneyFormat($matricula->turma->serie->proxima->primeira_parcela_anuidade),
            '/%TURMA_PROX_SERIE_MENSALIDADE%/' => fn() => self::moneyFormat($matricula->turma->serie->proxima->valorAnuidade / 12),
            '/%TURMA_PROX_SERIE_MENSALIDADE_DESC_(\d+)%/' => function ($desc) use ($matricula) {
                $valParcelas = $matricula->turma->serie->proxima->valorAnuidade / 12;
                return self::moneyFormat($valParcelas - ($valParcelas * ($desc[1] / 100)));
            },

            '/%TURMA_PROX_SERIE_MENSALIDADE_CBOLSA%/' => fn() => self::moneyFormat($anuidadeLiquida / 12),
            '/%TURMA_PROX_SERIE_MENSALIDADE_CBOLSA_DESC_(\d+)%/' => function ($desc) use ($anuidadeLiquida) {
                $valParcelas = $anuidadeLiquida / 12;
                return self::moneyFormat($valParcelas - ($valParcelas * ($desc[1] / 100)));
            },
        ];
    }

    private function anamneseVars(Matricula $matricula)
    {
        $anamnese = $matricula->aluno->anamnese;

        $listaProblemas = preg_replace('/(^|,)o/', '', $anamnese->problema_info_imp ?? '');
        $problemas = join(', ', array_filter([
            trim($listaProblemas),
            !empty($anamnese->outro_problema_info_imp) ? $anamnese->outro_problema_info_imp : ''
        ]));

        return [
            '/%ANAMNESE_NOME_HOSPITAL%/' => fn() => $anamnese->nome_remocao_hospital,
            '/%ANAMNESE_TELEFONE_HOSPITAL%/' => fn() => $anamnese->telefone_remocao_hospital,
            '/%ANAMNESE_ENDEREÇO_HOSPITAL%/' => fn() => $anamnese->endereco_remocao_hospital,
            '/%ANAMNESE_PLANO_SAUDE%/' => fn() => $anamnese->planosaude_remocao_hospital,
            '/%ANAMNESE_NUMERO_PLANO_SAUDE%/' => fn() => $anamnese->numero_planosaude_remocao_hospital,
            '/%ANAMNESE_MEDICO%/' => fn() => $anamnese->medico_remocao_hospital,
            '/%ANAMNESE_TELEFONE_MEDICO%/' => fn() => $anamnese->tel_medico_remocao_hospital,
            '/%ANAMNESE_RESPONSAVEL_1_NOME%/' => fn() => $anamnese->nome_1_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_1_PARENTESCO%/' => fn() => $anamnese->parentesco_1_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_1_RG%/' => fn() => $anamnese->rg_1_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_2_NOME%/' => fn() => $anamnese->nome_2_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_2_PARENTESCO%/' => fn() => $anamnese->parentesco_2_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_2_RG%/' => fn() => $anamnese->rg_2_retirada_autorizada,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_1_NOME%/' => fn() => $anamnese->contato_emergencia_1_nome,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_1_PARENTESCO%/' => fn() => $anamnese->contato_emergencia_1_parentesco,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_1_CONTATO%/' => fn() => $anamnese->contato_emergencia_1_contato,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_2_NOME%/' => fn() => $anamnese->contato_emergencia_2_nome,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_2_PARENTESCO%/' => fn() => $anamnese->contato_emergencia_2_parentesco,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_2_CONTATO%/' => fn() => $anamnese->contato_emergencia_2_contato,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_3_NOME%/' => fn() => $anamnese->contato_emergencia_3_nome,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_3_PARENTESCO%/' => fn() => $anamnese->contato_emergencia_3_parentesco,
            '/%ANAMNESE_CONTATO_EMERGENCIAL_3_CONTATO%/' => fn() => $anamnese->contato_emergencia_3_contato,
            '/%ANAMNESE_TRANSPORTE_ESCOLAR_AUTORIZADO%/' => fn() => $anamnese->conducao_retirada_autorizada ? 'Sim' : 'Não',
            '/%ANAMNESE_TRANSPORTE_ESCOLAR_NOME%/' => fn() => $anamnese->conducao_retirada_autorizada,
            '/%ANAMNESE_TRANSPORTE_ESCOLAR_RESPONSAVEL%/' => fn() => $anamnese->transporte_escolar_nome,
            '/%ANAMNESE_TRANSPORTE_ESCOLAR_TELEFONE%/' => fn() => $anamnese->transporte_escolar_telefone,
            '/%ANAMNESE_ALUNO_AUTORIZADO_A%/' => fn() => $anamnese->aluno_autorizado_retirada_autorizada,
            '/%ANAMNESE_MORA_COM%/' => fn() => $anamnese->outros_aluno_mora_com_retirada_autorizada ?: $anamnese->aluno_mora_com_retirada_autorizada,
            '/%ANAMNESE_QUEM_FICA_DURANTE_O_DIA%/' => fn() => $anamnese->quem_fica_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_PEDAGOGICO%/' => fn() => $anamnese->resp_pedag_retirada_autorizada,
            '/%ANAMNESE_RESPONSAVEL_PEDAGOGICO_GRAU_DE_PARENTESCO%/' => fn() => $anamnese->parentesco_resp_pedag_retirada_autorizada,
            '/%ANAMNESE_IRMAOS%/' => fn() => ($anamnese->quant_irmao_retirada_autorizada > 0)
                ? "Sim ($anamnese->quant_irmao_retirada_autorizada irmão" . ($anamnese->quant_irmao_retirada_autorizada > 1 ? 's' : '') . ")"
                : 'Não',
            '/%ANAMNESE_POS_FAM%/' => fn() => $anamnese->pos_familiar_retirada_autorizada,
            '/%ANAMNESE_IRMAO_ESTUDA%/' => fn() => $anamnese->irmao_estuda_retirada_autorizada ? 'Sim' : 'Não',
            '/%ANAMNESE_PRATICA_ALGUM_ESPORTE%/' => fn() => $anamnese->esporte_retirada_autorizada > 0 ? "Sim ($anamnese->qual_esporte_retirada_autorizada)" : 'Não',
            '/%ANAMNESE_ESTUDOU_EM%/' => fn() => $anamnese->quant_outros_colegios_ve == 'v' ? 'Vários' : $anamnese->quant_outros_colegios_ve,
            '/%ANAMNESE_NOME_DAS_INSTITUIÇÕES_DE_ENSINO%/' => fn() => $anamnese->nome_outras_escolas_ve,
            '/%ANAMNESE_DIFICULDADES_MATÉRIAS%/' => fn() => $anamnese->dificuldade_materias_ve,
            '/%ANAMNESE_OPNIAO_DIFICULDADES_MATÉRIAS%/' => fn() => $anamnese->opiniao_dificuldade_ve,
            '/%ANAMNESE_PROBLEMAS%/' => fn() => $problemas,
            '/%ANAMNESE_DOENCAS_CRONICAS%/' => fn() => join(', ', array_filter([$anamnese->doencas_cronicas_info_imp, $anamnese->outro_doencas_cronicas_info_imp])),
            '/%ANAMNESE_ALERGIAS%/' => fn() => $anamnese->alergias_info_imp ? "Sim ({$anamnese->outro_alergias_info_imp})" : 'Não',
            '/%ANAMNESE_PERIODO_FONOAUDIOLOGO%/' => fn() => $anamnese->fono_info_imp,
            '/%ANAMNESE_PERIODO_PSICOLOGO%/' => fn() => $anamnese->psi_info_imp,
            '/%ANAMNESE_PERIODO_PSICOPEDAGOGO%/' => fn() => $anamnese->psicopedagogo_info_imp,
            '/%ANAMNESE_PERIODO_NEUROLOGISTA%/' => fn() => $anamnese->neuro_info_imp,
            '/%ANAMNESE_PERIODO_OUTRO_PROFISSIONAL%/' => fn() => $anamnese->outro_especialista_info_imp,
            '/%ANAMNESE_PERIODO_FEZ_EXAME_ESPECIFICO%/' => fn() => $anamnese->fez_exame_info_imp ? 'Sim' : 'Não',
            '/%ANAMNESE_PERIODO_EXAME_ESPECIFICO%/' => fn() => $anamnese->outro_exame_info_imp,
            '/%ANAMNESE_PERIODO_OUTRO_TRATAMENTO%/' => fn() => $anamnese->tratamento_info_imp,
            '/%ANAMNESE_PERIODO_OUTRO_TRATAMENTO_NOME%/' => fn() => $anamnese->nome_espec_tratamento_info_imp,
            '/%ANAMNESE_PERIODO_OUTRO_TRATAMENTO_CONTATO%/' => fn() => $anamnese->tel_espec_tratamento_info_imp,
            '/%ANAMNESE_TOMA_MEDICAMENTOS%/' => fn() => $anamnese->toma_medicamento_info_imp ? $anamnese->medicamento_especialista_info_imp : 'Não',
            '/%ANAMNESE_POSSUI_DEFICIENCIA%/' => fn() => $anamnese->deficiencia_info_imp ? 'Sim' : 'Não',
            '/%ANAMNESE_OUTRAS_INFOS%/' => fn() => $anamnese->outras_infos,
        ];
    }

    private function responsaveisVars(Matricula $matricula): array
    {
        $responsaveisCache = null;
        $responsaveis = function () use ($matricula, $responsaveisCache) {
            $matricula->aluno->loadMissing('responsaveis');
            return $responsaveisCache ??= $matricula->aluno->responsaveis;
        };

        $pai = fn(string $var, $default = '') => self::dotGet($responsaveis()->where('idparentesco', 1)->first(), $var, $default);
        $mae = fn(string $var, $default = '') => self::dotGet($responsaveis()->where('idparentesco', 2)->first(), $var, $default);
        $respFin = fn(string $var, $default = '') => self::dotGet($responsaveis()->where('respfin', true)->first(), $var, $default);
        $respFinSec = fn(string $var, $default = '') => self::dotGet($responsaveis()->where('respfin2', true)->first(), $var, $default);
        $respPedag = fn(string $var, $default = '') => self::dotGet($responsaveis()->where('resppedag', true)->first(), $var, $default);

        return [
            '/%PAI_NOME%/' => fn() => $pai('pessoa.nome'),
            '/%ALUNO_FILIACAO_NOME_DO_PAI%/' => fn() => $pai('pessoa.nome'),
            '/%PAI_DATA_NASCIMENTO%/' => fn() => $pai('pessoa.datanascimento') ? self::dateLocal($pai('pessoa.datanascimento')) : '',
            '/%PAI_CPF%/' => fn() => $pai('pessoa.cpf'),
            '/%PAI_EST_CIV%/' => fn() => $pai('pessoa.estadocivil'),
            '/%PAI_RG_NUMERO%/' => fn() => $pai('pessoa.rg'),
            '/%PAI_RG_ORGAO%/' => fn() => $pai('pessoa.orgaoexp'),
            '/%PAI_RG_DATA_EXP%/' => fn() => $pai('pessoa.rg_expedido_em') ? self::dateLocal($pai('pessoa.rg_expedido_em')) : '',
            '/%PAI_ENDERECO_LOGRADOURO%/' => fn() => $pai('pessoa.logradouro'),
            '/%PAI_ENDERECO_NUMERO%/' => fn() => $pai('pessoa.numero'),
            '/%PAI_ENDERECO_COMPLEMENTO%/' => fn() => $pai('pessoa.complemento'),
            '/%PAI_ENDERECO_BAIRRO%/' => fn() => $pai('pessoa.bairro'),
            '/%PAI_ENDERECO_MUNICIPIO%/' => fn() => $pai('pessoa.cidade.nom_cidade'),
            '/%PAI_ENDERECO_UF%/' => fn() => $pai('pessoa.estado.sgl_estado'),
            '/%PAI_ENDERECO_CEP%/' => fn() => $pai('pessoa.cep'),
            '/%PAI_PROFISSAO%/' => fn() => $pai('pessoa.profissao'),
            '/%PAI_EMPRESA%/' => fn() => $pai('pessoa.empresa'),
            '/%PAI_EMPRESA_CEP%/' => fn() => $pai('pessoa.trabalho_cep'),
            '/%PAI_EMPRESA_BAIRRO%/' => fn() => $pai('pessoa.trabalho_bairro'),
            '/%PAI_EMPRESA_ENDERECO%/' => fn() => $pai('pessoa.trabalho_logradouro'),
            '/%PAI_EMPRESA_NUMERO%/' => fn() => $pai('pessoa.trabalho_numero'),
            '/%PAI_EMPRESA_COMPLEMENTO%/' => fn() => $pai('pessoa.trabalho_complemento'),
            '/%PAI_EMPRESA_TEL%/' => fn() => $pai('pessoa.trabalho_tel'),
            '/%PAI_NACIONALIDADE%/' => fn() => $pai('pessoa.paisNascimento.nacionalidade') ?: $pai('pessoa.paisNascimento.nom_pais'),
            '/%PAI_EMAIL%/' => fn() => $pai('pessoa.email') ?: ($pai('pessoa.emails') ? $pai('pessoa.emails')->first() : ''),
            '/%PAI_EMAILS%/' => fn() => $pai('pessoa.emails', null) ? $pai('pessoa.emails', null)->pluck('email')->join(', ') : '',
            '/%PAI_TELEFONE%/' => fn() => $pai('pessoa.telefones', null) ? $pai('pessoa.telefones', null)->first() : '',
            '/%PAI_TELEFONES%/' => fn() => $pai('pessoa.telefones', null) ? $pai('pessoa.telefones', null)->pluck('telefone')->join(', ') : '',
            '/%PAI_CELULAR%/' => fn() => $pai('pessoa.telefones', null) ? $pai('pessoa.telefones', null)->where('idtipotel', 1)->first() : '',
            '/%PAI_CELULARES%/' => fn() => $pai('pessoa.telefones', null) ? $pai('pessoa.telefones', null)->where('idtipotel', 1)->pluck('telefone')->join(', ') : '',

            '/%MAE_NOME%/' => fn() => $mae('pessoa.nome'),
            '/%ALUNO_FILIACAO_NOME_DA_MAE%/' => fn() => $mae('pessoa.nome'),
            '/%MAE_DATA_NASCIMENTO%/' => fn() => $mae('pessoa.datanascimento') ? self::dateLocal($mae('pessoa.datanascimento')) : '',
            '/%MAE_CPF%/' => fn() => $mae('pessoa.cpf'),
            '/%MAE_EST_CIV%/' => fn() => $mae('pessoa.estadocivil'),
            '/%MAE_RG_NUMERO%/' => fn() => $mae('pessoa.rg'),
            '/%MAE_RG_ORGAO%/' => fn() => $mae('pessoa.orgaoexp'),
            '/%MAE_RG_DATA_EXP%/' => fn() => $mae('pessoa.rg_expedido_em') ? self::dateLocal($mae('pessoa.rg_expedido_em')) : '',
            '/%MAE_ENDERECO_LOGRADOURO%/' => fn() => $mae('pessoa.logradouro'),
            '/%MAE_ENDERECO_NUMERO%/' => fn() => $mae('pessoa.numero'),
            '/%MAE_ENDERECO_COMPLEMENTO%/' => fn() => $mae('pessoa.complemento'),
            '/%MAE_ENDERECO_BAIRRO%/' => fn() => $mae('pessoa.bairro'),
            '/%MAE_ENDERECO_MUNICIPIO%/' => fn() => $mae('pessoa.cidade.nom_cidade'),
            '/%MAE_ENDERECO_UF%/' => fn() => $mae('pessoa.estado.sgl_estado'),
            '/%MAE_ENDERECO_CEP%/' => fn() => $mae('pessoa.cep'),
            '/%MAE_PROFISSAO%/' => fn() => $mae('pessoa.profissao'),
            '/%MAE_EMPRESA%/' => fn() => $mae('pessoa.empresa'),
            '/%MAE_EMPRESA_CEP%/' => fn() => $mae('pessoa.trabalho_cep'),
            '/%MAE_EMPRESA_BAIRRO%/' => fn() => $mae('pessoa.trabalho_bairro'),
            '/%MAE_EMPRESA_ENDERECO%/' => fn() => $mae('pessoa.trabalho_logradouro'),
            '/%MAE_EMPRESA_NUMERO%/' => fn() => $mae('pessoa.trabalho_numero'),
            '/%MAE_EMPRESA_COMPLEMENTO%/' => fn() => $mae('pessoa.trabalho_complemento'),
            '/%MAE_EMPRESA_TEL%/' => fn() => $mae('pessoa.trabalho_tel'),
            '/%MAE_NACIONALIDADE%/' => fn() => $mae('pessoa.paisNascimento.nacionalidade') ?: $mae('pessoa.paisNascimento.nom_pais'),
            '/%MAE_EMAIL%/' => fn() => $mae('pessoa.email') ?: ($mae('pessoa.emails') ? $mae('pessoa.emails')->first() : ''),
            '/%MAE_EMAILS%/' => fn() => $mae('pessoa.emails', null) ? $mae('pessoa.emails', null)->pluck('email')->join(', ') : '',
            '/%MAE_TELEFONE%/' => fn() => $mae('pessoa.telefones', null) ? $mae('pessoa.telefones', null)->first() : '',
            '/%MAE_TELEFONES%/' => fn() => $mae('pessoa.telefones', null) ? $mae('pessoa.telefones', null)->pluck('telefone')->join(', ') : '',
            '/%MAE_CELULAR%/' => fn() => $mae('pessoa.telefones', null) ? $mae('pessoa.telefones', null)->where('idtipotel', 1)->first() : '',
            '/%MAE_CELULARES%/' => fn() => $mae('pessoa.telefones', null) ? $mae('pessoa.telefones', null)->where('idtipotel', 1)->pluck('telefone')->join(', ') : '',

            '/%CONTRATANTE_NOME%/' => fn() => $respFin('pessoa.nome'),
            '/%CONTRATANTE_DATA_NASCIMENTO%/' => fn() => $respFin('pessoa.datanascimento') ? self::dateLocal($respFin('pessoa.datanascimento')) : '',
            '/%CONTRATANTE_CPF%/' => fn() => $respFin('pessoa.cpf'),
            '/%CONTRATANTE_ESTADOCIVIL%/' => fn() => $respFin('pessoa.estadocivil'),
            '/%CONTRATANTE_RG%/' => fn() => $respFin('pessoa.rg'),
            '/%CONTRATANTE_RG_ORGAO%/' => fn() => $respFin('pessoa.orgaoexp'),
            '/%CONTRATANTE_RG_DATA_EXP%/' => fn() => $respFin('pessoa.rg_expedido_em') ? self::dateLocal($respFin('pessoa.rg_expedido_em')) : '',
            '/%CONTRATANTE_ENDERECO_LOGRADOURO%/' => fn() => $respFin('pessoa.logradouro'),
            '/%CONTRATANTE_ENDERECO_NUMERO%/' => fn() => $respFin('pessoa.numero'),
            '/%CONTRATANTE_ENDERECO_COMPLEMENTO%/' => fn() => $respFin('pessoa.complemento'),
            '/%CONTRATANTE_ENDERECO_BAIRRO%/' => fn() => $respFin('pessoa.bairro'),
            '/%CONTRATANTE_ENDERECO_MUNICIPIO%/' => fn() => $respFin('pessoa.cidade.nom_cidade'),
            '/%CONTRATANTE_ENDERECO_UF%/' => fn() => $respFin('pessoa.estado.sgl_estado'),
            '/%CONTRATANTE_ENDERECO_CEP%/' => fn() => $respFin('pessoa.cep'),
            '/%CONTRATANTE_PARENTESCO%/' => fn() => $respFin('pessoa.parentesco'),
            '/%CONTRATANTE_PROFISSAO%/' => fn() => $respFin('pessoa.profissao'),
            '/%CONTRATANTE_EMPRESA%/' => fn() => $respFin('pessoa.empresa'),
            '/%CONTRATANTE_EMPRESA_CEP%/' => fn() => $respFin('pessoa.trabalho_cep'),
            '/%CONTRATANTE_EMPRESA_BAIRRO%/' => fn() => $respFin('pessoa.trabalho_bairro'),
            '/%CONTRATANTE_EMPRESA_ENDERECO%/' => fn() => $respFin('pessoa.trabalho_logradouro'),
            '/%CONTRATANTE_EMPRESA_NUMERO%/' => fn() => $respFin('pessoa.trabalho_numero'),
            '/%CONTRATANTE_EMPRESA_COMPLEMENTO%/' => fn() => $respFin('pessoa.trabalho_complemento'),
            '/%CONTRATANTE_EMPRESA_TEL%/' => fn() => $respFin('pessoa.trabalho_tel'),
            '/%CONTRATANTE_NACIONALIDADE%/' => fn() => $respFin('pessoa.paisNascimento.nacionalidade') ?: $respFin('pessoa.paisNascimento.nom_pais'),
            '/%CONTRATANTE_EMAIL%/' => fn() => $respFin('pessoa.email') ?: ($respFin('pessoa.emails') ? $respFin('pessoa.emails')->first() : ''),
            '/%CONTRATANTE_EMAILS%/' => fn() => $respFin('pessoa.emails', null) ? $respFin('pessoa.emails', null)->pluck('email')->join(', ') : '',
            '/%CONTRATANTE_TELEFONE%/' => fn() => $respFin('pessoa.telefones', null) ? $respFin('pessoa.telefones', null)->first() : '',
            '/%CONTRATANTE_TELEFONES%/' => fn() => $respFin('pessoa.telefones', null) ? $respFin('pessoa.telefones', null)->pluck('telefone')->join(', ') : '',
            '/%CONTRATANTE_CELULAR%/' => fn() => $respFin('pessoa.telefones', null) ? $respFin('pessoa.telefones', null)->where('idtipotel', 1)->first() : '',
            '/%CONTRATANTE_CELULARES%/' => fn() => $respFin('pessoa.telefones', null) ? $respFin('pessoa.telefones', null)->where('idtipotel', 1)->pluck('telefone')->join(', ') : '',

            '/%CONTRATANTE2_NOME%/' => fn() => $respFinSec('pessoa.nome'),
            '/%CONTRATANTE2_DATA_NASCIMENTO%/' => fn() => $respFinSec('pessoa.datanascimento') ? self::dateLocal($respFinSec('pessoa.datanascimento')) : '',
            '/%CONTRATANTE2_CPF%/' => fn() => $respFinSec('pessoa.cpf'),
            '/%CONTRATANTE2_ESTADOCIVIL%/' => fn() => $respFinSec('pessoa.estadocivil'),
            '/%CONTRATANTE2_RG%/' => fn() => $respFinSec('pessoa.rg'),
            '/%CONTRATANTE2_RG_ORGAO%/' => fn() => $respFinSec('pessoa.orgaoexp'),
            '/%CONTRATANTE2_RG_DATA_EXP%/' => fn() => $respFinSec('pessoa.rg_expedido_em') ? self::dateLocal($respFinSec('pessoa.rg_expedido_em')) : '',
            '/%CONTRATANTE2_ENDERECO_LOGRADOURO%/' => fn() => $respFinSec('pessoa.logradouro'),
            '/%CONTRATANTE2_ENDERECO_NUMERO%/' => fn() => $respFinSec('pessoa.numero'),
            '/%CONTRATANTE2_ENDERECO_COMPLEMENTO%/' => fn() => $respFinSec('pessoa.complemento'),
            '/%CONTRATANTE2_ENDERECO_BAIRRO%/' => fn() => $respFinSec('pessoa.bairro'),
            '/%CONTRATANTE2_ENDERECO_MUNICIPIO%/' => fn() => $respFinSec('pessoa.cidade.nom_cidade'),
            '/%CONTRATANTE2_ENDERECO_UF%/' => fn() => $respFinSec('pessoa.estado.sgl_estado'),
            '/%CONTRATANTE2_ENDERECO_CEP%/' => fn() => $respFinSec('pessoa.cep'),
            '/%CONTRATANTE2_PARENTESCO%/' => fn() => $respFinSec('pessoa.parentesco'),
            '/%CONTRATANTE2_PROFISSAO%/' => fn() => $respFinSec('pessoa.profissao'),
            '/%CONTRATANTE2_EMPRESA%/' => fn() => $respFinSec('pessoa.empresa'),
            '/%CONTRATANTE2_EMPRESA_CEP%/' => fn() => $respFinSec('pessoa.trabalho_cep'),
            '/%CONTRATANTE2_EMPRESA_BAIRRO%/' => fn() => $respFinSec('pessoa.trabalho_bairro'),
            '/%CONTRATANTE2_EMPRESA_ENDERECO%/' => fn() => $respFinSec('pessoa.trabalho_logradouro'),
            '/%CONTRATANTE2_EMPRESA_NUMERO%/' => fn() => $respFinSec('pessoa.trabalho_numero'),
            '/%CONTRATANTE2_EMPRESA_COMPLEMENTO%/' => fn() => $respFinSec('pessoa.trabalho_complemento'),
            '/%CONTRATANTE2_EMPRESA_TEL%/' => fn() => $respFinSec('pessoa.trabalho_tel'),
            '/%CONTRATANTE2_NACIONALIDADE%/' => fn() => $respFinSec('pessoa.paisNascimento.nacionalidade') ?: $respFinSec('pessoa.paisNascimento.nom_pais'),
            '/%CONTRATANTE2_EMAIL%/' => fn() => $respFinSec('pessoa.email') ?: ($respFinSec('pessoa.emails') ? $respFinSec('pessoa.emails')->first() : ''),
            '/%CONTRATANTE2_EMAILS%/' => fn() => $respFinSec('pessoa.emails', null) ? $respFinSec('pessoa.emails', null)->pluck('email')->join(', ') : '',
            '/%CONTRATANTE2_TELEFONE%/' => fn() => $respFinSec('pessoa.telefones', null) ? $respFinSec('pessoa.telefones', null)->first() : '',
            '/%CONTRATANTE2_TELEFONES%/' => fn() => $respFinSec('pessoa.telefones', null) ? $respFinSec('pessoa.telefones', null)->pluck('telefone')->join(', ') : '',
            '/%CONTRATANTE2_CELULAR%/' => fn() => $respFinSec('pessoa.telefones', null) ? $respFinSec('pessoa.telefones', null)->where('idtipotel', 1)->first() : '',
            '/%CONTRATANTE2_CELULARES%/' => fn() => $respFinSec('pessoa.telefones', null) ? $respFinSec('pessoa.telefones', null)->where('idtipotel', 1)->pluck('telefone')->join(', ') : '',

            '/%RESPONSAVEL_FINANCEIRO_NOME%/' => fn() => $respFin('pessoa.nome'),
            '/%RESPONSAVEL_FINANCEIRO_RG_NUMERO%/' => fn() => $respFin('pessoa.rg'),
            '/%RESPONSAVEL_FINANCEIRO_RG_ORGAO%/' => fn() => $respFin('pessoa.orgaoexp'),
            '/%RESPONSAVEL_FINANCEIRO_RG_DATA_EXP%/' => fn() => $respFin('pessoa.rg_expedido_em') ? self::dateLocal($respFin('pessoa.rg_expedido_em')) : '',
            '/%RESPONSAVEL_FINANCEIRO_CPF%/' => fn() => $respFin('pessoa.cpf'),
            '/%RESPONSAVEL_FINANCEIRO_NASCIMENTO%/' => fn() => $respFin('pessoa.datanascimento') ? self::dateLocal($respFin('pessoa.datanascimento')) : '',
            '/%RESPONSAVEL_FINANCEIRO_PARENTESCO%/' => fn() => $respFin('parentesco.parentesco'),
            '/%RESPONSAVEL_FINANCEIRO_ESTADO_CIVIL%/' => fn() => $respFin('pessoa.estadocivil'),
            '/%RESPONSAVEL_FINANCEIRO_NACIONALIDADE%/' => fn() => $respFin('pessoa.paisNascimento.nacionalidade') ?: $respFin('pessoa.paisNascimento.nom_pais'),
            '/%RESPONSAVEL_FINANCEIRO_PROFISSAO%/' => fn() => $respFin('pessoa.profissao'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA%/' => fn() => $respFin('pessoa.empresa'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA_CEP%/' => fn() => $respFin('pessoa.trabalho_cep'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA_BAIRRO%/' => fn() => $respFin('pessoa.trabalho_bairro'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA_ENDERECO%/' => fn() => $respFin('pessoa.trabalho_logradouro'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA_NUMERO%/' => fn() => $respFin('pessoa.trabalho_numero'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA_COMPLEMENTO%/' => fn() => $respFin('pessoa.trabalho_complemento'),
            '/%RESPONSAVEL_FINANCEIRO_EMPRESA_TEL%/' => fn() => $respFin('pessoa.trabalho_tel'),
            '/%RESPONSAVEL_FINANCEIRO_TELEFONE%/' => fn() => $respFin('pessoa.telefones') ? $respFin('pessoa.telefones')->first() : '',
            '/%RESPONSAVEL_FINANCEIRO_CELULAR%/' => fn() => $respFin('pessoa.celulares') ? $respFin('pessoa.celulares')->first() : '',
            '/%RESPONSAVEL_FINANCEIRO_EMAIL%/' => fn() => $respFin('pessoa.email') ?: ($respFin('pessoa.emails') ? $respFin('pessoa.emails')->first() : ''),
            '/%RESPONSAVEL_FINANCEIRO_CEP%/' => fn() => $respFin('pessoa.cep'),
            '/%RESPONSAVEL_FINANCEIRO_ESTADO%/' => fn() => $respFin('pessoa.estado.sgl_estado'),
            '/%RESPONSAVEL_FINANCEIRO_CIDADE%/' => fn() => $respFin('pessoa.cidade'),
            '/%RESPONSAVEL_FINANCEIRO_ENDERECO%/' => fn() => $respFin('pessoa.endereco'),
            '/%RESPONSAVEL_FINANCEIRO_NOME_LOGIN_SENHA%/' => function () use ($matricula) {
                $loginResp = "<table border='0' width='90%' style='margin-left:auto;margin-right:auto;'>";
                $loginResp .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Nome</div></td>";
                $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Login</div></td>";
                $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Senha</div></td>";
                $loginResp .= "</tr>";

                $queryRPS = "SELECT nome, login, senha FROM pessoas, responsaveis, usuarios WHERE responsaveis.idpessoa=pessoas.id AND responsaveis.idpessoa=usuarios.idpessoa AND responsaveis.idaluno=" . $matricula->idaluno . " AND (responsaveis.respfin=1 OR responsaveis.respfin2=1)  GROUP BY nome ORDER BY nome ASC ";
                $resultRPS = mysql_query($queryRPS);
                while ($rowRPS = mysql_fetch_array($resultRPS, MYSQL_ASSOC)) {
                    $loginResp .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRPS['nome'] . "</div></td>";
                    $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRPS['login'] . "</div></td>";
                    $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRPS['senha'] . "</div></td>";
                    $loginResp .= "</tr>";
                }
                $loginResp .= "</table>";
                return $loginResp;
            },

            '/%RESPONSAVEL_PEDAGOGICO_NOME%/' => fn() => $respPedag('pessoa.nome'),
            '/%RESPONSAVEL_PEDAGOGICO_RG_NUMERO%/' => fn() => $respPedag('pessoa.rg'),
            '/%RESPONSAVEL_PEDAGOGICO_RG_ORGAO%/' => fn() => $respPedag('pessoa.orgaoexp'),
            '/%RESPONSAVEL_PEDAGOGICO_RG_DATA_EXP%/' => fn() => $respPedag('pessoa.rg_expedido_em') ? self::dateLocal($respPedag('pessoa.rg_expedido_em')) : '',
            '/%RESPONSAVEL_PEDAGOGICO_CPF%/' => fn() => $respPedag('pessoa.cpf'),
            '/%RESPONSAVEL_PEDAGOGICO_NASCIMENTO%/' => fn() => $respPedag('pessoa.datanascimento') ? self::dateLocal($respPedag('pessoa.datanascimento')) : '',
            '/%RESPONSAVEL_PEDAGOGICO_PARENTESCO%/' => fn() => $respPedag('parentesco.parentesco'),
            '/%RESPONSAVEL_PEDAGOGICO_ESTADO_CIVIL%/' => fn() => $respPedag('pessoa.estadocivil'),
            '/%RESPONSAVEL_PEDAGOGICO_NACIONALIDADE%/' => fn() => $respPedag('pessoa.paisNascimento.nacionalidade') ?: $respPedag('pessoa.paisNascimento.nom_pais'),
            '/%RESPONSAVEL_PEDAGOGICO_PROFISSAO%/' => fn() => $respPedag('pessoa.profissao'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA%/' => fn() => $respPedag('pessoa.empresa'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA_CEP%/' => fn() => $respPedag('pessoa.trabalho_cep'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA_BAIRRO%/' => fn() => $respPedag('pessoa.trabalho_bairro'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA_ENDERECO%/' => fn() => $respPedag('pessoa.trabalho_logradouro'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA_NUMERO%/' => fn() => $respPedag('pessoa.trabalho_numero'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA_COMPLEMENTO%/' => fn() => $respPedag('pessoa.trabalho_complemento'),
            '/%RESPONSAVEL_PEDAGOGICO_EMPRESA_TEL%/' => fn() => $respPedag('pessoa.trabalho_tel'),

            '/%RESPONSAVEL_PEDAGOGICO_TELEFONE%/' => fn() => $respPedag('pessoa.telefones') ? $respPedag('pessoa.telefones')->first() : '',
            '/%RESPONSAVEL_PEDAGOGICO_CELULAR%/' => fn() => $respPedag('pessoa.celulares') ? $respPedag('pessoa.celulares')->first() : '',
            '/%RESPONSAVEL_PEDAGOGICO_EMAIL%/' => fn() => $respPedag('pessoa.email') ?: ($respPedag('pessoa.emails') ? $respPedag('pessoa.emails')->first() : ''),
            '/%RESPONSAVEL_PEDAGOGICO_CEP%/' => fn() => $respPedag('pessoa.cep'),
            '/%RESPONSAVEL_PEDAGOGICO_ESTADO%/' => fn() => $respPedag('pessoa.estado.sgl_estado'),
            '/%RESPONSAVEL_PEDAGOGICO_CIDADE%/' => fn() => $respPedag('pessoa.cidade'),
            '/%RESPONSAVEL_PEDAGOGICO_ENDERECO%/' => fn() => $respPedag('pessoa.endereco'),
            '/%RESPONSAVEL_PEDAGOGICO_NOME_LOGIN_SENHA%/' => function () use ($matricula) {
                $loginResp = "<table border='0' width='90%' style='margin-left:auto;margin-right:auto;'>";
                $loginResp .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Nome</div></td>";
                $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Login</div></td>";
                $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>Senha</div></td>";
                $loginResp .= "</tr>";

                $queryRPS = "SELECT nome, login, senha FROM pessoas, responsaveis, usuarios WHERE responsaveis.idpessoa=pessoas.id AND responsaveis.idpessoa=usuarios.idpessoa AND responsaveis.idaluno=" . $matricula->idaluno . " AND responsaveis.resppedag=1 GROUP BY nome ORDER BY nome ASC ";
                $resultRPS = mysql_query($queryRPS);
                while ($rowRPS = mysql_fetch_array($resultRPS, MYSQL_ASSOC)) {
                    $loginResp .= "<tr><td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRPS['nome'] . "</div></td>";
                    $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRPS['login'] . "</div></td>";
                    $loginResp .= "<td style='text-align:center;padding:2px 0px;border-bottom:1px solid #000;'><div style='overflow: hidden; word-wrap: break-word;'>" . $rowRPS['senha'] . "</div></td>";
                    $loginResp .= "</tr>";
                }
                $loginResp .= "</table>";
                return $loginResp;
            },
        ];
    }

    private function historicoVars(Matricula $matricula)
    {
        return [
            '/%HISTORICO\[(.*)\]%/' => function ($match) use ($matricula) {
                $id = $matricula->idaluno;
                $anosHistorico = $match[1];

                return "<span
                        class=\"historico\"
                        data-id=\"$id\"
                        data-anos-historico=\"$anosHistorico\"
                    >
                        Carregando o histórico...
                    </span>";
            },

            '/%HISTORICO_FIXO\[(.*)\]%/' => function ($match) use ($matricula) {
                $id = $matricula->idaluno;

                // Pega grupo de captura do regex
                $params = explode(',', $match[1]);

                // Parametro de frequência
                $frequencia = $params[0];

                // Parametros de anos do histórico
                $anosHistoricoParams = array_slice($params, 1);
                $anosHistorico = implode(',', $anosHistoricoParams);

                return "
                    <span
                        class=\"historico\"
                        data-id=\"$id\"
                        data-anos-historico=\"$anosHistorico\"
                        data-ocultar-carga-horaria=\"true\"
                        data-frequencia=\"$frequencia\"
                    >
                        Carregando o histórico...
                    </span>
                ";
            },
        ];
    }

    private static function dateLocal($val, $fmt = 'L')
    {
        return Carbon::parse($val)->locale('pt_BR')->isoFormat($fmt);
    }

    private static function moneyFormat($value)
    {
        return money_format(
            '%.2n',
            (float) $value
        );
    }

    /**
     * Acesso recursivo à propriedades de objetos via notação por ponto
     *
     * @param object $object objeto a ser navegado
     * @param string $accessor caminho do objeto
     * @param mixed $default valor padrão caso não exista valor nessa propriedade
     * @return mixed
     */
    private static function dotGet(?object $object, string $accessor, mixed $default)
    {
        if (empty($object)) {
            return $default;
        }

        return array_reduce(
            explode('.', $accessor),
            fn($carry, $item) => $carry->$item ?? null,
            $object
        ) ?? $default;
    }
}
