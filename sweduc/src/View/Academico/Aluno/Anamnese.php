
<section>
    <h2 class="section-forms">
        Em caso de necessidade, remover para hospital/clínica
    </h2>

    <div class="form-group">
        <label for="nome_remocao_hospital" class="col-sm-2 control-label">Nome do Hospital</label>
        <div class="col-sm-4">
            <input type="text" value="<?=$anamnese['nome_remocao_hospital']?>" name="nome_remocao_hospital" id="nome_remocao_hospital" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="telefone_remocao_hospital" class="col-sm-2 control-label">Telefone do Hospital</label>
        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['telefone_remocao_hospital']?>" name="telefone_remocao_hospital" id="telefone_remocao_hospital" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="endereco_remocao_hospital" class="col-sm-2 control-label">Endereço do Hospital</label>
        <div class="col-sm-5">
            <input type="text" value="<?=$anamnese['endereco_remocao_hospital']?>" name="endereco_remocao_hospital" id="endereco_remocao_hospital" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="planosaude_remocao_hospital" class="col-sm-2 control-label">Plano de Saúde</label>
        <div class="col-sm-3">
            <input type="text" value="<?=$anamnese['planosaude_remocao_hospital']?>" name="planosaude_remocao_hospital" id="planosaude_remocao_hospital" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="numero_planosaude_remocao_hospital" class="col-sm-2 control-label">Número do Plano de Saúde</label>
        <div class="col-sm-3">
            <input type="text" value="<?=$anamnese['numero_planosaude_remocao_hospital']?>" name="numero_planosaude_remocao_hospital" id="numero_planosaude_remocao_hospital" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="medico_remocao_hospital" class="col-sm-2 control-label">Médico</label>
        <div class="col-sm-5">
            <input type="text" value="<?=$anamnese['medico_remocao_hospital']?>" name="medico_remocao_hospital" id="medico_remocao_hospital" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="tel_medico_remocao_hospital" class="col-sm-2 control-label">Telefone do Médico</label>
        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['tel_medico_remocao_hospital']?>" name="tel_medico_remocao_hospital" id="tel_medico_remocao_hospital" class="form-control" />
        </div>
    </div>
</section>

<section>
    <h2 class="section-forms">
        Pessoas autorizadas a retirar o(a) aluno(a) da escola
    </h2>

    <div class="form-group">
        <label for="nome_1_retirada_autorizada" class="col-sm-2 control-label">Nome / Parentesco / RG</label>
        <div class="col-sm-3">
            <input type="text" value="<?=$anamnese['nome_1_retirada_autorizada']?>" name="nome_1_retirada_autorizada" id="nome_1_retirada_autorizada" class="form-control" placeholder="Nome do parente" />
        </div>

        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['parentesco_1_retirada_autorizada']?>" name="parentesco_1_retirada_autorizada" id="parentesco_1_retirada_autorizada" class="form-control" placeholder="Grau de parentesco" />
        </div>

        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['rg_1_retirada_autorizada']?>" name="rg_1_retirada_autorizada" id="rg_1_retirada_autorizada" class="form-control" placeholder="RG" />
        </div>
    </div>

    <div class="form-group">
        <label for="nome_2_retirada_autorizada" class="col-sm-2 control-label">Nome / Parentesco / RG</label>
        <div class="col-sm-3">
            <input type="text" value="<?=$anamnese['nome_2_retirada_autorizada']?>" name="nome_2_retirada_autorizada" id="nome_2_retirada_autorizada" class="form-control" placeholder="Nome do parente" />
        </div>

        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['parentesco_2_retirada_autorizada']?>" name="parentesco_2_retirada_autorizada" id="parentesco_2_retirada_autorizada" class="form-control" placeholder="Grau de parentesco" />
        </div>

        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['rg_2_retirada_autorizada']?>" name="rg_2_retirada_autorizada" id="rg_2_retirada_autorizada" class="form-control" placeholder="RG" />
        </div>
    </div>

    <div>
        <div class="pb-4">
            <strong class="text-gray-700">
                Em caso de emergência, não localizando os pais, contatar
            </strong>
        </div>

        <div class="form-group">
            <label for="contato_emergencia_1_nome" class="col-sm-2 control-label">
                1º Contato de emergencia
            </label>

            <div class="col-sm-3">
                <input type="text" value="<?=$anamnese['contato_emergencia_1_nome']?>" name="contato_emergencia_1_nome" id="contato_emergencia_1_nome" class="form-control" placeholder="Nome" />
            </div>

            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['contato_emergencia_1_parentesco']?>" name="contato_emergencia_1_parentesco" id="contato_emergencia_1_parentesco" class="form-control" placeholder="Parentesco" />
            </div>

            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['contato_emergencia_1_contato']?>" name="contato_emergencia_1_contato" id="contato_emergencia_1_contato" class="form-control" placeholder="Tel" />
            </div>
        </div>

        <div class="form-group">
            <label for="contato_emergencia_2_nome" class="col-sm-2 control-label">
                2º Contato de emergencia
            </label>

            <div class="col-sm-3">
                <input type="text" value="<?=$anamnese['contato_emergencia_2_nome']?>" name="contato_emergencia_2_nome" id="contato_emergencia_2_nome" class="form-control" placeholder="Nome" />
            </div>

            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['contato_emergencia_2_parentesco']?>" name="contato_emergencia_2_parentesco" id="contato_emergencia_2_parentesco" class="form-control" placeholder="Parentesco" />
            </div>

            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['contato_emergencia_2_contato']?>" name="contato_emergencia_2_contato" id="contato_emergencia_2_contato" class="form-control" placeholder="Tel" />
            </div>
        </div>

        <div class="form-group">
            <label for="contato_emergencia_3_nome" class="col-sm-2 control-label">
                3º Contato de emergencia
            </label>

            <div class="col-sm-3">
                <input type="text" value="<?=$anamnese['contato_emergencia_3_nome']?>" name="contato_emergencia_3_nome" id="contato_emergencia_3_nome" class="form-control" placeholder="Nome" />
            </div>

            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['contato_emergencia_3_parentesco']?>" name="contato_emergencia_3_parentesco" id="contato_emergencia_3_parentesco" class="form-control" placeholder="Parentesco" />
            </div>

            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['contato_emergencia_3_contato']?>" name="contato_emergencia_3_contato" id="contato_emergencia_3_contato" class="form-control" placeholder="Tel" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="conducao_retirada_autorizada" class="col-sm-2 control-label">Condução</label>
        <div class="col-sm-5">
            <input type="text" value="<?=$anamnese['conducao_retirada_autorizada']?>" name="conducao_retirada_autorizada" id="conducao_retirada_autorizada" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label for="aluno_autorizado_retirada_autorizada" class="col-sm-2 control-label">O aluno é autorizado a</label>
        <div class="col-sm-5">
            <input type="text" value="<?=$anamnese['aluno_autorizado_retirada_autorizada']?>" name="aluno_autorizado_retirada_autorizada" id="aluno_autorizado_retirada_autorizada" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Aluno mora com</label>
        <div class="col-sm-1" style="padding-top: 7px;">
            <input type="radio" class="hidden" <?=($anamnese['aluno_mora_com_retirada_autorizada'] == 'Pai') ? 'checked="checked"' : ''?> name="aluno_mora_com_retirada_autorizada" id="aluno_mora_com_retirada_autorizada_pai" value="Pai" />
            <label for="aluno_mora_com_retirada_autorizada_pai"><span></span>Pai</label>
        </div>

        <div class="col-sm-1" style="padding-top: 7px;">
            <input type="radio" class="hidden" <?=($anamnese['aluno_mora_com_retirada_autorizada'] == 'Mãe') ? 'checked="checked"' : ''?> name="aluno_mora_com_retirada_autorizada" id="aluno_mora_com_retirada_autorizada_mae" value="Mãe" />
            <label for="aluno_mora_com_retirada_autorizada_mae"><span></span>Mãe</label>
        </div>

        <div class="col-sm-1" style="padding-top: 7px;">
            <input type="radio" class="hidden" <?=($anamnese['aluno_mora_com_retirada_autorizada'] == 'Pais') ? 'checked="checked"' : ''?> name="aluno_mora_com_retirada_autorizada" id="aluno_mora_com_retirada_autorizada_pais" value="Pais" />
            <label for="aluno_mora_com_retirada_autorizada_pais"><span></span>Pais</label>
        </div>

        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['outros_aluno_mora_com_retirada_autorizada']?>" name="outros_aluno_mora_com_retirada_autorizada" id="aluno_mora_com_retirada_autorizada_outro" class="form-control" placeholder="Outro" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">Com quem fica o aluno durante o dia</label>
        <div class="col-sm-5" style="padding-top: 9px;">
            <input type="text" value="<?=$anamnese['quem_fica_retirada_autorizada']?>" name="quem_fica_retirada_autorizada" id="quem_fica_retirada_autorizada" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label id="resp_pedag_retirada_autorizada" class="control-label col-sm-2">Responsável pedagógico pelo aluno perante a escola</label>
        <div class="col-sm-5" style="padding-top: 9px;">
            <input type="text" value="<?=$anamnese['resp_pedag_retirada_autorizada']?>" name="resp_pedag_retirada_autorizada" id="resp_pedag_retirada_autorizada" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label id="parentesco_resp_pedag_retirada_autorizada" class="control-label col-sm-2">Grau de parentesco</label>
        <div class="col-sm-5">
            <input type="text" value="<?=$anamnese['parentesco_resp_pedag_retirada_autorizada']?>" name="parentesco_resp_pedag_retirada_autorizada" id="parentesco_resp_pedag_retirada_autorizada" class="form-control" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">Tem irmãos</label>
        <div class="col-sm-1" style="padding-top: 9px;">
            <input type="radio" class="hidden" <?=($anamnese['tem_irmao_retirada_autorizada'] == '1') ? 'checked="checked"' : ''?> name="tem_irmao_retirada_autorizada" id="tem_irmao_retirada_autorizada_sim" value="1" />
            <label for="tem_irmao_retirada_autorizada_sim"><span></span>Sim</label>
        </div>

        <div class="col-sm-1" style="padding-top: 9px;">
            <input type="radio" class="hidden" <?=($anamnese['tem_irmao_retirada_autorizada'] == '0') ? 'checked="checked"' : ''?> name="tem_irmao_retirada_autorizada" id="tem_irmao_retirada_autorizada_nao" value="0" />
            <label for="tem_irmao_retirada_autorizada_nao"><span></span>Não</label>
        </div>

        <div id="has-brother" style="display: <?=($anamnese['tem_irmao_retirada_autorizada']) ? 'block' : 'none'?>;">
            <div class="col-sm-2">
                <input type="text" value="<?=$anamnese['quant_irmao_retirada_autorizada']?>" placeholder="Quantos?" name="quant_irmao_retirada_autorizada" id="quant_irmao_retirada_autorizada" class="form-control">
            </div>

            <div class="col-sm-4">
                <input type="text" value="<?=$anamnese['pos_familiar_retirada_autorizada']?>" style="width: 38%; margin-right: 5px;" placeholder="1º, 2º, 3º filho etc..." name="pos_familiar_retirada_autorizada" id="pos_familiar_retirada_autorizada" class="form-control pull-left">
                <p class="form-control-static pull-left">Posição familiar da criança</p>
            </div>
        </div>
    </div>

    <div id="has-brother-cont" style="display: <?=($anamnese['tem_irmao_retirada_autorizada']) ? 'block' : 'none'?>;">
        <div class="form-group">
            <label class="control-label col-sm-2">Se tiver, estuda na escola</label>
            <div class="col-sm-1" style="padding-top: 9px;">
                <input type="radio" class="hidden" <?=($anamnese['irmao_estuda_retirada_autorizada'] == '1') ? 'checked="checked"' : ''?> name="irmao_estuda_retirada_autorizada" id="irmao_estuda_retirada_autorizada_sim" value="1" />
                <label for="irmao_estuda_retirada_autorizada_sim"><span></span>Sim</label>
            </div>

            <div class="col-sm-1" style="padding-top: 9px;">
                <input type="radio" class="hidden" <?=($anamnese['irmao_estuda_retirada_autorizada'] == '0') ? 'checked="checked"' : ''?> name="irmao_estuda_retirada_autorizada" id="irmao_estuda_retirada_autorizada_nao" value="0" />
                <label for="irmao_estuda_retirada_autorizada_nao"><span></span>Não</label>
            </div>

            <div id="brother-is-student" style="display: <?=($anamnese['irmao_estuda_retirada_autorizada']) ? 'block' : 'none'?>;">
                <div class="col-sm-2">
                    <input type="text" value="<?=$anamnese['turma_irmao_retirada_autorizada']?>" placeholder="Turma" name="turma_irmao_retirada_autorizada" id="turma_irmao_retirada_autorizada" class="form-control">
                </div>

                <div class="col-sm-3">
                    <input type="text" value="<?=$anamnese['nome_turma_irmao_retirada_autorizada']?>" placeholder="Nome da turma" name="nome_turma_irmao_retirada_autorizada" id="nome_turma_irmao_retirada_autorizada" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">O aluno pratica algum esporte</label>
        <div class="col-sm-1" style="padding-top: 17px;">
            <input type="radio" class="hidden" <?=($anamnese['esporte_retirada_autorizada'] == '1') ? 'checked="checked"' : ''?> name="esporte_retirada_autorizada" id="esporte_retirada_autorizada_sim" value="1" />
            <label for="esporte_retirada_autorizada_sim"><span></span>Sim</label>
        </div>

        <div class="col-sm-1" style="padding-top: 17px;">
            <input type="radio" class="hidden" <?=($anamnese['esporte_retirada_autorizada'] == '0') ? 'checked="checked"' : ''?> name="esporte_retirada_autorizada" id="esporte_retirada_autorizada_nao" value="0" />
            <label for="esporte_retirada_autorizada_nao"><span></span>Não</label>
        </div>

        <div id="has-sports" style="display: <?=($anamnese['esporte_retirada_autorizada']) ? 'block' : 'none'?>;">
            <div class="col-sm-5" style="padding-top: 12px;">
                <input type="text" value="<?=$anamnese['qual_esporte_retirada_autorizada']?>" placeholder="Quais?" name="qual_esporte_retirada_autorizada" id="qual_esporte_retirada_autorizada" class="form-control">
            </div>
        </div>
    </div>
</section>

<section>
    <h2 class="section-forms">
        Vida Escolar
    </h2>

    <div class="form-group">
        <label class="col-sm-2 control-label">Até a presente data, estudou em</label>
        <div class="col-sm-2" style="padding-top: 15px;">
            <input type="radio" class="hidden" <?=($anamnese['quant_outros_colegios_ve'] == '1') ? 'checked="checked"' : ''?> name="quant_outros_colegios_ve" id="outros_colegios_ve_1" value="1" />
            <label for="outros_colegios_ve_1"><span></span>Um colégio</label>
        </div>

        <div class="col-sm-2" style="padding-top: 15px;">
            <input type="radio" class="hidden" <?=($anamnese['quant_outros_colegios_ve'] == '2') ? 'checked="checked"' : ''?> name="quant_outros_colegios_ve" id="outros_colegios_ve_2" value="2" />
            <label for="outros_colegios_ve_2"><span></span>Dois colégios</label>
        </div>

        <div class="col-sm-2" style="padding-top: 15px;">
            <input type="radio" class="hidden" <?=($anamnese['quant_outros_colegios_ve'] == 'v') ? 'checked="checked"' : ''?> name="quant_outros_colegios_ve" id="outros_colegio_ve_v" value="v" />
            <label for="outros_colegio_ve_v"><span></span>Vários</label>
        </div>
    </div>

    <div class="form-group">
        <label for="nome_outras_escolas_ve" class="col-sm-2 control-label">Cite o nome das instituições de ensino</label>
        <div class="col-sm-10" style="padding-top: 9px;">
            <input value="<?=$anamnese['nome_outras_escolas_ve']?>" type="text" name="nome_outras_escolas_ve" id="nome_outras_escolas_ve" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="dificuldade_materias_ve" class="col-sm-2 control-label">Apresenta dificuldades em algumas matérias? Quais?</label>
        <div class="col-sm-10" style="padding-top: 9px;">
            <input type="text" value="<?=$anamnese['dificuldade_materias_ve']?>" class="form-control" name="dificuldade_materias_ve" id="dificuldade_materias_ve" />
        </div>
    </div>

    <div class="form-group">
        <label for="opiniao_dificuldade_ve" class="col-sm-2 control-label">Na sua opinião, o que poderia ter contribuído para essas dificuldades</label>
        <div class="col-sm-10">
            <textarea name="opiniao_dificuldade_ve" id="opiniao_dificuldade_ve" class="form-control" rows="4"><?=$anamnese['opiniao_dificuldade_ve']?></textarea>
        </div>
    </div>
</section>

<section>
    <h2 class="section-forms">
        Informações Importantes
    </h2>

    <?php $problema_info_imp = explode(',', $anamnese['problema_info_imp'])?>
    <div class="form-group">
        <label class="control-label col-sm-2">O aluno apresenta algum problema de</label>
        <div class="col-sm-2" style="padding-top: 15px;">
            <input type="checkbox" <?=(in_array('Visão', array_map('trim', $problema_info_imp))) ? 'checked="checked"' : ''?> name="problema_info_imp[]" id="problema_info_imp_v" value="Visão" />
            <label for="problema_info_imp_v"><span></span>Visão</label>
        </div>

        <div class="col-sm-2" style="padding-top: 15px;">
            <input type="checkbox" <?=(in_array('Audição', array_map('trim', $problema_info_imp))) ? 'checked="checked"' : ''?> name="problema_info_imp[]" id="problema_info_imp_a" value="Audição" />
            <label for="problema_info_imp_a"><span></span>Audição</label>
        </div>

        <div class="col-sm-2" style="padding-top: 15px;">
            <input type="checkbox" <?=(in_array('o', array_map('trim', $problema_info_imp))) ? 'checked="checked"' : ''?> name="problema_info_imp[]" id="problema_info_imp_o" value="o" />
            <label for="problema_info_imp_o"><span></span>Outros</label>
        </div>

        <div id="others-disease" style="display: <?=(in_array('o', array_map('trim', $problema_info_imp))) ? 'block' : 'none'?>;">
            <div class="col-sm-4" style="padding-top: 11px;">
                <input type="text" value="<?=$anamnese['outro_problema_info_imp']?>" placeholder="Quais" class="form-control" name="outro_problema_info_imp" id="outro_problema_info_imp" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php $doencas_cronicas_info_imp = explode(',', $anamnese['doencas_cronicas_info_imp'])?>
        <label class="control-label col-sm-2">Doenças Crônicas</label>
        <div class="col-sm-2">
            <input type="checkbox" <?=(in_array('Asma', array_map('trim', $doencas_cronicas_info_imp))) ? 'checked="checked"' : ''?> name="doencas_cronicas_info_imp[]" id="doencas_cronicas_info_imp_asma" value="Asma" />
            <label for="doencas_cronicas_info_imp_asma"><span></span>Asma</label>
        </div>

        <div class="col-sm-2">
            <input type="checkbox" <?=(in_array('Bronquite', array_map('trim', $doencas_cronicas_info_imp))) ? 'checked="checked"' : ''?> name="doencas_cronicas_info_imp[]" id="doencas_cronicas_info_imp_bronquite" value="Bronquite" />
            <label for="doencas_cronicas_info_imp_bronquite"><span></span>Bronquite</label>
        </div>

        <div class="col-sm-2">
            <input type="checkbox" <?=(in_array('Diabete', array_map('trim', $doencas_cronicas_info_imp))) ? 'checked="checked"' : ''?> name="doencas_cronicas_info_imp[]" id="doencas_cronicas_info_imp_diabete" value="Diabete" />
            <label for="doencas_cronicas_info_imp_diabete"><span></span>Diabete</label>
        </div>

        <div class="col-sm-2">
            <input type="checkbox" <?=(in_array('Epilepsia', array_map('trim', $doencas_cronicas_info_imp))) ? 'checked="checked"' : ''?> name="doencas_cronicas_info_imp[]" id="doencas_cronicas_info_imp_epilepsia" value="Epilepsia" />
            <label for="doencas_cronicas_info_imp_epilepsia"><span></span>Epilepsia</label>
        </div>

        <div class="col-sm-2">
            <input type="checkbox" <?=(in_array('Hemofilia', array_map('trim', $doencas_cronicas_info_imp))) ? 'checked="checked"' : ''?> name="doencas_cronicas_info_imp[]" id="doencas_cronicas_info_imp_hemofilia" value="Hemofilia" />
            <label for="doencas_cronicas_info_imp_hemofilia"><span></span>Hemofilia</label>
        </div>

        <div class="col-sm-2" style="margin-top: 15px;">
            <input type="checkbox" <?=(in_array('Hipertensão', array_map('trim', $doencas_cronicas_info_imp))) ? 'checked="checked"' : ''?> name="doencas_cronicas_info_imp[]" id="doencas_cronicas_info_imp_hipertensao" value="Hipertensão" />
            <label for="doencas_cronicas_info_imp_hipertensao"><span></span>Hipertensão</label>
        </div>

        <div class="col-sm-8" style="margin-top: 10px;">
            <input type="text" value="<?=$anamnese['outro_doencas_cronicas_info_imp']?>" placeholder="Caso haja outras doenças crônicas, coloque aqui..." class="form-control" name="outro_doencas_cronicas_info_imp" id="outro_doencas_cronicas_info_imp" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">Alergias</label>
        <div class="col-sm-1" style="padding-top: 9px;">
            <input type="radio" class="hidden" <?=($anamnese['alergias_info_imp'] == '1') ? 'checked="checked"' : ''?> name="alergias_info_imp" id="alergias_info_imp_sim" value="1" />
            <label for="alergias_info_imp_sim"><span></span>Sim</label>
        </div>

        <div class="col-sm-1" style="padding-top: 9px;">
            <input type="radio" class="hidden" <?=($anamnese['alergias_info_imp'] == '0') ? 'checked="checked"' : ''?> name="alergias_info_imp" id="alergias_info_imp_nao" value="0" />
            <label for="alergias_info_imp_nao"><span></span>Não</label>
        </div>

        <div id="outras-alergias" style="display: <?=($anamnese['alergias_info_imp'] == '1') ? 'block' : 'none'?>;">
            <div class="col-sm-4">
                <input type="text" value="<?=$anamnese['outro_alergias_info_imp']?>" placeholder="Quais" class="form-control" name="outro_alergias_info_imp" id="outro_alergias_info_imp" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">O aluno já foi ou está sendo atendido por algum destes profissionais</label>
        <div class="col-sm-10">
            <div class="row">
                <div class="col-sm-2">
                    <p class="form-control-static"><b>Fonoaudiólogo</b></p>
                </div>

                <div class="col-sm-10">
                    <input type="text" value="<?=$anamnese['fono_info_imp']?>" placeholder="Período" class="form-control" name="fono_info_imp" id="fono_info_imp" />
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-sm-2">
                    <p class="form-control-static"><b>Psicólogo</b></p>
                </div>

                <div class="col-sm-10">
                    <input type="text" value="<?=$anamnese['psi_info_imp']?>" placeholder="Período" class="form-control" name="psi_info_imp" id="psi_info_imp" />
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-sm-2">
                    <p class="form-control-static"><b>Psicopedagogo</b></p>
                </div>

                <div class="col-sm-10">
                    <input type="text" value="<?=$anamnese['psicopedagogo_info_imp']?>" placeholder="Período" class="form-control" name="psicopedagogo_info_imp" id="psicopedagogo_info_imp" />
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-sm-2">
                    <p class="form-control-static"><b>Neurologista</b></p>
                </div>

                <div class="col-sm-10">
                    <input type="text" value="<?=$anamnese['neuro_info_imp']?>" placeholder="Período" class="form-control" name="neuro_info_imp" id="neuro_info_imp" />
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-sm-2">
                    <p class="form-control-static"><b>Outro</b></p>
                </div>

                <div class="col-sm-10">
                    <input type="text" value="<?=$anamnese['outro_especialista_info_imp']?>" placeholder="Período" class="form-control" name="outro_especialista_info_imp" id="outro_especialista_info_imp" />
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">O aluno precisou fazer algum exame específico</label>
        <div class="col-sm-1" style="padding-top: 18px;">
            <input type="radio" class="hidden" <?=($anamnese['fez_exame_info_imp'] == '1') ? 'checked="checked"' : ''?> name="fez_exame_info_imp" id="fez_exame_info_imp_sim" value="1" />
            <label for="fez_exame_info_imp_sim"><span></span>Sim</label>
        </div>

        <div class="col-sm-1" style="padding-top: 18px;">
            <input type="radio" class="hidden" <?=($anamnese['fez_exame_info_imp'] == '0') ? 'checked="checked"' : ''?> name="fez_exame_info_imp" id="fez_exame_info_imp_nao" value="0" />
            <label for="fez_exame_info_imp_nao"><span></span>Não</label>
        </div>

        <div id="fez-exames" style="display: <?=($anamnese['fez_exame_info_imp'] == '1') ? 'block' : 'none'?>;">
            <div class="col-sm-8" style="padding-top: 11px;">
                <input type="text" value="<?=$anamnese['outro_exame_info_imp']?>" placeholder="Especifique o exame e o resultado" class="form-control" name="outro_exame_info_imp" id="outro_exame_info_imp" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="tratamento_info_imp" class="control-label col-sm-2">Caso o aluno esteja fazendo algum tratamento atualmente, indique</label>
        <div class="col-sm-10" style="padding-top: 18px;">
            <input type="text" value="<?=$anamnese['tratamento_info_imp']?>" placeholder="Especifique aqui" class="form-control" name="tratamento_info_imp" id="tratamento_info_imp" />
        </div>
    </div>

    <div class="form-group">
        <label for="nome_espec_tratamento_info_imp" class="control-label col-sm-2">Nome do especialista</label>
        <div class="col-sm-8">
            <input type="text" value="<?=$anamnese['nome_espec_tratamento_info_imp']?>" placeholder="Nome do especialista" class="form-control" name="nome_espec_tratamento_info_imp" id="nome_espec_tratamento_info_imp" />
        </div>

        <div class="col-sm-2">
            <input type="text" value="<?=$anamnese['tel_espec_tratamento_info_imp']?>" placeholder="Telefone" class="form-control" name="tel_espec_tratamento_info_imp" id="tel_espec_tratamento_info_imp" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2">O aluno está tomando algum medicamento pelo especialista</label>
        <div class="col-sm-1" style="padding-top: 22px;">
            <input type="radio" class="hidden" <?=($anamnese['toma_medicamento_info_imp'] == '1') ? 'checked="checked"' : ''?> name="toma_medicamento_info_imp" id="toma_medicamento_info_imp_sim" value="1" />
            <label for="toma_medicamento_info_imp_sim"><span></span>Sim</label>
        </div>

        <div class="col-sm-1" style="padding-top: 22px;">
            <input type="radio" class="hidden" <?=($anamnese['toma_medicamento_info_imp'] == '0') ? 'checked="checked"' : ''?> name="toma_medicamento_info_imp" id="toma_medicamento_info_imp_nao" value="0" />
            <label for="toma_medicamento_info_imp_nao"><span></span>Não</label>
        </div>

        <div id="quais-medicamentos" style="display: <?=($anamnese['toma_medicamento_info_imp'] == '1') ? 'block' : 'none'?>;">
            <div class="col-sm-8" style="padding-top: 18px;">
                <input type="text" value="<?=$anamnese['medicamento_especialista_info_imp']?>" placeholder="Quais" class="form-control" name="medicamento_especialista_info_imp" id="medicamento_especialista_info_imp" />
            </div>
        </div>
    </div>
</section>

<section>
    <h2 class="section-forms">
        Outras Informações
    </h2>

    <div class="form-group">
        <label for="outras_infos" class="col-sm-2 control-label">Escreva mais alguma coisa sobre seu filho, que nos permita conhecê-lo melhor</label>
        <div class="col-sm-10">
            <textarea name="outras_infos" id="outras_infos" class="form-control" rows="4"><?=$anamnese['outras_infos']?></textarea>
        </div>
    </div>
</section>

<script type="text/javascript">
$(function () {
    $('input[name="tem_irmao_retirada_autorizada"]').on('click', function () {
        var valChecked = $(this).val();
        if (valChecked == '1') {
            $('#has-brother').css('display', 'block');
            $('#has-brother-cont').css('display', 'block');
        } else {
            $('#has-brother').css('display', 'none');
            $('#has-brother-cont').css('display', 'none');
            $('#quant_irmao_retirada_autorizada').val('');
            $('#pos_familiar_retirada_autorizada').val('');
            $('input[name="irmao_estuda_retirada_autorizada"]').prop('checked', false);
        }
    });

    $('input[name="irmao_estuda_retirada_autorizada"]').on('click', function () {
        var valChecked = $(this).val();
        if (valChecked == '1') {
            $('#brother-is-student').css('display', 'block');
        } else {
            $('#brother-is-student').css('display', 'none');
            $('#turma_irmao_retirada_autorizada').val('');
            $('#nome_turma_irmao_retirada_autorizada').val('');
        }
    });

    $('input[name="esporte_retirada_autorizada"]').on('click', function () {
        var valChecked = $(this).val();
        if (valChecked == '1') {
            $('#has-sports').css('display', 'block');
        } else {
            $('#has-sports').css('display', 'none');
            $('#qual_esporte_retirada_autorizada').val('');
        }
    });

    $('#problema_info_imp_o').on('click', function () {
        if (this.checked) {
            $('#others-disease').css('display', 'block');
        } else {
            $('#others-disease').css('display', 'none');
            $('#outro_problema_info_imp').val('');
        }
    });

    $('input[name="alergias_info_imp"]').on('click', function () {
        var valChecked = $(this).val();
        if (valChecked == '1') {
            $('#outras-alergias').css('display', 'block');
        } else {
            $('#outras-alergias').css('display', 'none');
            $('#outro_alergias_info_imp').val('');
        }
    });

    $('input[name="fez_exame_info_imp"]').on('click', function () {
        var valChecked = $(this).val();
        if (valChecked == '1') {
            $('#fez-exames').css('display', 'block');
        } else {
            $('#fez-exames').css('display', 'none');
            $('#outro_exame_info_imp').val('');
        }
    });

    $('input[name="toma_medicamento_info_imp"]').on('click', function () {
        var valChecked = $(this).val();
        if (valChecked == '1') {
            $('#quais-medicamentos').css('display', 'block');
        } else {
            $('#quais-medicamentos').css('display', 'none');
            $('#medicamento_especialista_info_imp').val('');
        }
    });

    $('#enviar-ficha-anamnese').on('click', function () {
        $.ajax({
            url: 'dao/anamnese.php',
            type: 'POST',
            data: $('#frm-cad-anamnese').serialize(),
            success: function (data) {
                var resultado = data.split('|');
                criaAlerta(resultado[0], resultado[1]);
            }
        });
    });

    $('#imprime-anamnese').on('click', function () {
        window.open('ficha_anamnese_aluno.php?idaluno=<?=$idaluno?>', '_blank');
    });
});
</script>
