<div role="tabpanel" class="tab-pane active" id="tab_alunos">
    <h2 class="section-forms rounded">
        Foto
    </h2>

    <div class="sm:flex -m-2">
        <div class="p-2">
            <?php if ($idpermissoes > 0) : ?>
                <?=$this->insert('Academico/Aluno/UploadPicture', ['pessoaId' => $aluno->idpessoa, 'fotoUrl' => $fotoUrl])?>
            <?php endif ?>
        </div>

        <div class="p-2">
            <?php if (!empty($numeroaluno)) : ?>
                <h3>
                    Número do Aluno: <?=$numeroaluno?>
                </h3>
            <?php endif ?>
        </div>
    </div>

    <div class="clearfix"></div>

    <section>
        <h2 class="section-forms rounded">
            Dados pessoais
        </h2>

        <div class="form-group">
            <label for="nomeLabel" name="nomeLabel" id="nomeLabel" class="col-sm-3 col-md-2 control-label">Nome *</label>
            <div class="col-sm-6">
                <input placeholder="Digite o nome do(a) aluno(a)" required="true" type="text" name="nome" id="nome" class="form-element" value="<?=$nome ?? ''?>" />
            </div>
        </div>

        <div class="form-group">
            <label for="idsexo" class="col-sm-3 col-md-2 control-label">Sexo</label>
            <div class="col-sm-3">
                <select name="idsexo" class="form-element">
                    <?php foreach ($sexos as $sexo) : ?>
                        <option
                            value="<?=$sexo->id?>"
                            <?=$this->selected($sexo->id == ($idsexo ?? ''))?>
                        >
                            <?=$sexo->titulo?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="raca" class="col-sm-3 col-md-2 control-label">Raça</label>
            <div class="col-sm-3">
                <select name="raca" class="form-element">
                    <?php
                    if ($raca == null || $raca == 0 || empty($idaluno)) {
                        echo '<option value="0">--Selecione uma Raça--</option>';
                        echo '<option value="1">Branco(a)</option>';
                        echo '<option value="2">Negro(a)</option>';
                        echo '<option value="3"> Amarelo(a)</option>';
                        echo '<option value="4"> Pardo(a) </option>';
                        echo '<option value="5"> Indígena </option>';
                        echo '<option value="6"> Não Informado </option>';
                        echo ' </select>';
                    } else {
                        switch ($raca) {
                            case 1:
                                echo '<option value=' . $raca . '>Branco(a)</option>';
                                echo '<option value="2"> Negro(a) </option>';
                                echo '<option value="3"> Amarelo(a) </option>';
                                echo '<option value="4"> Pardo(a) </option>';
                                echo '<option value="5"> Indígena </option>';
                                echo '<option value="6"> Não Informado </option>';
                                echo ' </select>';
                                break;
                            case 2:
                                echo '<option value=' . $raca . '>Negro(a)</option>';
                                echo '<option value="1"> Branco(a) </option>';
                                echo '<option value="3"> Amarelo(a) </option>';
                                echo '<option value="4"> Pardo(a) </option>';
                                echo '<option value="5"> Indígena </option>';
                                echo '<option value="6"> Não Informado </option>';
                                echo ' </select>';
                                break;
                            case 3:
                                echo '<option value=' . $raca . '>Amarelo(a)</option>';
                                echo '<option value="1"> Branco(a) </option>';
                                echo '<option value="2"> Negro(a)</option>';
                                echo '<option value="4"> Pardo(a)</option>';
                                echo '<option value="5"> Indígena </option>';
                                echo '<option value="6"> Não Informado </option>';
                                echo ' </select>';
                                break;
                            case 4:
                                echo '<option value=' . $raca . '>Pardo(a)</option>';
                                echo '<option value="1"> Branco(a) </option>';
                                echo '<option value="2"> Negro(a) </option>';
                                echo '<option value="3"> Amarelo(a) </option>';
                                echo '<option value="5"> Indígena </option>';
                                echo '<option value="6"> Não Informado </option>';
                                echo ' </select>';
                                break;
                            case 5:
                                echo '<option value=' . $raca . '>Indígena</option>';
                                echo '<option value="1"> Branco(a) </option>';
                                echo '<option value="2"> Negro(a) </option>';
                                echo '<option value="3"> Amarelo(a) </option>';
                                echo '<option value="4"> Pardo(a) </option>';
                                echo '<option value="6"> Não Informado </option>';
                                echo ' </select>';
                                break;
                            case 6:
                                echo '<option value=' . $raca . '> Não Informado </option>';
                                echo '<option value="1">Branco(a)</option>';
                                echo '<option value="2"> Negro(a) </option>';
                                echo '<option value="3"> Amarelo(a) </option>';
                                echo '<option value="4"> Pardo(a) </option>';
                                echo '<option value="5"> Indígena </option>';
                                echo ' </select>';
                                break;
                        }
                    }




                    ?>


                    </select>
            </div>
        </div>

        <div class="form-group">
            <label for="datanascimento" class="col-sm-3 col-md-2 control-label">
                Data de Nascimento
            </label>

            <div class="col-sm-3">
                <input
                    type="date"
                    class="form-element"
                    id="datanascimento"
                    name="datanascimento"
                    value="<?=$datanascimento ?? ''?>"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="idpaisnascimento" class="col-sm-3 col-md-2 control-label">País</label>
            <div class="col-sm-3">
                <select name="idpaisnascimento" id="idpaisnascimento" class="form-element">
                    <?php foreach ($paises as $pais) : ?>
                        <option
                            value="<?=$pais->cod_pais?>"
                            <?=$this->selected(empty($idpaisnascimento) && $pais->titulo == 'BRASIL')?>
                            <?=$this->selected(!empty($idpaisnascimento) && $pais->cod_pais == $idpaisnascimento)?>
                        >
                            <?=$pais->titulo?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="idestadonascimento" class="col-sm-3 col-md-2 control-label">Estado</label>
            <div class="col-sm-3">
                <select name="idestadonascimento"  id="idestadonascimento" class="form-element">
                    <option value="-1" selected="selected">Selecione</option>
                    <?php
                    $query1 = "SELECT * FROM estados WHERE cod_pais = COALESCE(NULLIF('{$idpaisnascimento}', ''), (SELECT cod_pais FROM paises WHERE nom_pais = 'BRASIL')) ORDER BY cod_pais ASC, sgl_estado ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        ?>
                        <option
                            value="<?=$row1['id']?>"
                            <?=$this->selected($row1['id'] == $idestadonascimento)?>
                        >
                            <?=$row1['sgl_estado']?> - <?=$row1['nom_estado']?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="idcidadenascimento" class="col-sm-3 col-md-2 control-label">Cidade</label>
            <div class="col-sm-3">
                <select name="idcidadenascimento" data-toggle="tooltip" data-placement="bottom" title="Escolha o estado para carregar as cidades" id="idcidadenascimento" class="form-element">
                    <?php
                    if ($idaluno > 0) {
                        $query1 = "SELECT * FROM cidades WHERE cod_estado = (SELECT cod_estado FROM cidades WHERE id = {$idcidadenascimento}) ORDER BY nom_cidade ASC ";
                        $result1 = mysql_query($query1);
                        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                            $selected = ($row1['id'] == $idcidadenascimento) ? 'selected="selected"' : '';
                            echo '<option value="' . $row1['id'] . '" ' . $selected . '>' . $row1['nom_cidade'] . '</option>';
                        }
                    } else {
                        echo '<option value="-1" selected="selected">Selecione</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="idestadocivil" class="col-sm-3 col-md-2 control-label">Estado civil</label>
            <div class="col-sm-3">
                <select name="idestadocivil"  id="idestadocivil" class="form-element">
                    <?php
                    $query1 = "SELECT * FROM estadocivil ORDER BY id ASC";
                    $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                        ?>
                        <option
                            value="<?=$row1['id']?>"
                            <?=$this->selected($row1['id'] == $idestadocivil)?>
                        >
                            <?=$row1['estadocivil']?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="profissao-aluno" class="col-sm-3 col-md-2 control-label">
                Profissão
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Digite a profissão do(a) aluno(a)"
                    name="profissao"
                    id="profissao-aluno"
                    class="form-element"
                    value="<?=$profissao ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="cpf" class="col-sm-3 col-md-2 control-label">
                CPF
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Digite o CPF do(a) aluno(a)"
                    name="cpf"
                    id="cpf"
                    class="form-element"
                    data-mask="999.999.999-99"
                    value="<?=$cpf ?? ''?>"
                />
                <span id="msgcpf" style="color:#f00;">
            </div>
        </div>

        <div class="form-group">
            <label for="rg" class="col-sm-3 col-md-2 control-label">
                RG
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Digite o RG do aluno"
                    name="rg"
                    id="rg"
                    class="form-element"
                    value="<?=$rg ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="orgaoexp" class="col-sm-3 col-md-2 control-label">
                Orgão expedidor
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Orgão expedidor do RG"
                    name="orgaoexp"
                    id="orgaoexp"
                    class="form-element"
                    value="<?=$orgaoexp ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="rg_data_expedicao" class="col-sm-3 col-md-2 control-label">
                Data de expedição
            </label>

            <div class="col-sm-3">
                <input
                    type="date"
                    class="form-element"
                    id="rg_data_expedicao"
                    name="rg_data_expedicao"
                    value="<?=$aluno->pessoa->rg_expedido_em ?? ''?>"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="registromec" class="col-sm-3 col-md-2 control-label">
                Registro MEC / INEP
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Registro MEC do(a) aluno(a)"
                    name="registromec"
                    id="registromec"
                    class="form-element"
                    value="<?=$registromec ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2">
                <label class="switch-toggle" for="filho-de-funcionario">
                    <input
                        id="filho-de-funcionario"
                        name="filho-de-funcionario"
                        type="checkbox"
                        <?=$this->checked($aluno->filho_funcionario)?>
                    >
                    Filho de funcionário
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2">
                <label class="switch-toggle" for="contrato_desempenho">
                    <input
                        id="contrato_desempenho"
                        name="contrato_desempenho"
                        type="checkbox"
                        <?=$this->checked($aluno->contrato_desempenho)?>
                    >
                    Contrato de Desempenho
                </label>
            </div>
        </div>

        <?php isset($nome_original) ? $is_nome_social = true : $is_nome_social = false ?>
        <?php isset($nome_original) ? $is_nome_social = true : $is_nome_social = false ?>

        <div class="form-group">
            <div class="col-sm-offset-2">
                <label class="switch-toggle" for="aluno-inclusao">
                    <input
                        id="aluno-inclusao"
                        name="aluno-inclusao"
                        type="checkbox"
                        <?=$this->checked($aluno->aluno_inclusao)?>
                    >
                    Aluno de inclusão
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2">
                <label class="switch-toggle" for="is_nome_social">
                    <input
                        id="is_nome_social"
                        name="is_nome_social"
                        type="checkbox"
                        <?=$this->checked($is_nome_social)?>
                        onclick="desbloqueiaNomeSocial('nome-original');"
                    >
                    Usar Nome Social?
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="nomeoriginalLabel" class="col-sm-3 col-md-2 control-label">
                Nome da Certidão de Nascimento
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Nome da Certidão de Nascimento do(a) aluno(a)"
                    name="nome-original"
                    id="nome-original"
                    class="form-element"
                    value="<?=$nome_original ?? ''?>"
                    <?=isset($nome_original) ? '' : 'disabled'?>
                />
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms rounded">
            Documentos
        </h2>

        <div class="flex flex-wrap -m-2">
            <div class="w-full sm:w-1/2 p-2">
                <label for="documentosentregues">
                    Documentos pendentes
                </label>

                <select id="documentosentregues" name="documentosentregues[]" multiple="multiple" size="8" class="form-element">
                    <?php foreach ($documentosRequeridos as $documento) : ?>
                        <option value="<?=$documento->id?>">
                            <?=$documento->documento?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="w-full sm:w-1/2 p-2">
                <table class="table table-striped table-responsive table-hover">
                    <thead>
                        <tr>
                            <th>Documentos Entregues</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentosEntregues as $documento) : ?>
                            <tr id="row_<?=$documento->pivot->id?>">
                                <td style="vertical-align: middle;">
                                    <?=$documento->documento?>
                                </td>
                                <td class="text-center">
                                    <?php if (in_array($alunos[0], $arraydo4)) { ?>
                                        <input
                                            type="button"
                                            value=" X "
                                            class="btn danger-color"
                                            onclick="removeLigacao('alunos_documentos',<?=$documento->pivot->id?>);"
                                        />
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms rounded">
            Contatos
        </h2>

        <div class="font-bold text-gray-800 text-center py-2 my-3">
            <span class="px-4 py-2 bg-yellow-200 rounded inline-block">
                <i class="fa fa-exclamation-circle mr-1"></i>
                As alterações de email e telefone só serão gravadas ao clicar em salvar no final do formulário.
            </span>
        </div>

        <div class="flex flex-wrap -m-2">
            <div class="w-full md:w-1/2 p-2">
                <table class="w-full bg-gray-200 rounded">
                    <thead>
                        <tr>
                            <th class="p-3">
                                Email
                            </th>

                            <th class="p-3 text-center">
                                Primário
                            </th>

                            <th class="p-3 text-center">
                                Opções
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach (($emails ?? [['email' => '', 'primario' => true]]) as $row1) : ?>
                            <tr>
                                <td class="p-3">
                                    <input
                                        type="text"
                                        name="emails[]"
                                        class="form-element"
                                        value="<?=$row1['email']?>"
                                        onchange="this.parentNode.parentNode.querySelector('input[name=\'email-primario\']').value = this.value"
                                    />
                                </td>

                                <td class="p-3 text-center">
                                    <input
                                        type="radio"
                                        name="email-primario"
                                        <?php if ($row1['primario']) :?>
                                            checked="checked"
                                        <?php endif ?>
                                        value="<?=$row1['email']?>"
                                    >
                                </td>

                                <td class="p-3 text-center">
                                    <button
                                        type="button"
                                        data-toggle="tooltip"
                                        data-placement="right"
                                        title="Remover"
                                        class="sw-btn sw-btn-danger whitespace-nowrap mr-1"
                                        onclick="this.parentNode.parentNode.remove()"
                                    >
                                        <i class="fa fa-trash-alt"></i>
                                        <span class="hidden sm:inline">Remover</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach ?>

                        <tr class="email-template">
                            <td class="p-3">
                                <button
                                    type="button"
                                    class="sw-btn sw-btn-primary whitespace-nowrap"
                                    onclick="cloneEmailRow(this)"
                                >
                                    <i class="fa fa-plus"></i>
                                    <span class="hidden sm:inline ml-1">Adicionar</span>
                                </button>

                                <input
                                    type="text"
                                    name="emails[]"
                                    class="form-element email-template-item hidden"
                                    value=""
                                    onchange="this.parentNode.parentNode.querySelector('input[name=\'email-primario\']').value = this.value"
                                />
                            </td>

                            <td class="p-3 text-center">
                                <input
                                    type="radio"
                                    name="email-primario"
                                    class="email-template-item hidden"
                                    value=""
                                >
                            </td>

                            <td class="p-3 text-center">
                                <button
                                    type="button"
                                    data-toggle="tooltip"
                                    data-placement="right"
                                    title="Remover"
                                    class="sw-btn sw-btn-danger whitespace-nowrap email-template-item hidden"
                                    style="margin-top: 2px;"
                                    onclick="this.parentNode.parentNode.remove()"
                                >
                                    <i class="fa fa-trash-alt mr-1"></i>
                                    <span class="hidden sm:inline">Remover</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="w-full md:w-1/2 p-2">
                <table class="w-full bg-gray-200 rounded">
                    <thead>
                        <tr>
                            <th class="p-3">
                                Telefone
                            </th>

                            <th class="p-3 text-center">
                                Tipo
                            </th>

                            <th class="p-3 text-center">
                                Opções
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach (($telefones ?? [['telefone' => '', 'idtipotel' => -1]]) as $row1) : ?>
                            <tr>
                                <td class="p-3">
                                    <input
                                        type="text"
                                        name="telefone[]"
                                        class="form-element"
                                        value="<?=$row1['telefone']?>"
                                    />
                                </td>

                                <td class="p-3 text-center">
                                    <select name="tipotelefone[]" class="form-element">
                                        <option value="-1">N&atilde;o informado</option>
                                        <?php foreach ($tipoTel as $tipo) : ?>
                                            <option
                                                value="<?=$tipo['id']?>"
                                                <?=$this->selected($tipo['id'] == $row1['idtipotel'])?>
                                            >
                                                <?=$tipo['tipotel']?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </td>

                                <td class="p-3 text-center">
                                    <button
                                        type="button"
                                        data-toggle="tooltip"
                                        data-placement="right"
                                        title="Remover"
                                        class="sw-btn sw-btn-danger whitespace-nowrap mr-1"
                                        onclick="this.parentNode.parentNode.remove()"
                                    >
                                        <i class="fa fa-trash-alt"></i>
                                        <span class="hidden sm:inline">Remover</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach ?>

                        <tr class="telefone-template">
                            <td class="p-3">
                                <button
                                    type="button"
                                    class="sw-btn sw-btn-primary whitespace-nowrap"
                                    onclick="cloneTelefoneRow(this)"
                                >
                                    <i class="fa fa-plus"></i>
                                    <span class="hidden sm:inline ml-1">Adicionar</span>
                                </button>

                                <input
                                    type="text"
                                    name="telefone[]"
                                    class="form-element telefone-template-item hidden"
                                />
                            </td>

                            <td class="p-3 text-center">
                                <select name="tipotelefone[]" class="form-element telefone-template-item hidden">
                                    <option value="-1">N&atilde;o informado</option>
                                    <?php foreach ($tipoTel as $tipo) : ?>
                                        <option value="<?=$tipo['id']?>">
                                            <?=$tipo['tipotel']?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </td>

                            <td class="p-3 text-center">
                                <button
                                    type="button"
                                    data-toggle="tooltip"
                                    data-placement="right"
                                    title="Remover"
                                    class="sw-btn sw-btn-danger whitespace-nowrap telefone-template-item hidden"
                                    style="margin-top: 2px;"
                                    onclick="this.parentNode.parentNode.remove()"
                                >
                                    <i class="fa fa-trash-alt mr-1"></i>
                                    <span class="hidden sm:inline">Remover</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms rounded">
            Dados Residenciais
        </h2>

        <div class="form-group">
            <label for="cep" class="col-sm-3 col-md-2 control-label">
                CEP
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="CEP do endereço"
                    data-mask="99999-999"
                    name="cep"
                    id="cep"
                    class="form-element"
                    value="<?=$cep ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="logradouro" class="col-sm-3 col-md-2 control-label">
                Logradouro
            </label>

            <div class="col-sm-4">
                <input
                    type="text"
                    placeholder="Endereço do(a) aluno(a)"
                    name="logradouro"
                    id="logradouro"
                    class="form-element"
                    value="<?=$logradouro ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="numero" class="col-sm-3 col-md-2 control-label">
                Número
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Número do endereço"
                    name="numero"
                    id="numero"
                    class="form-element"
                    value="<?=$numero ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="complemento" class="col-sm-3 col-md-2 control-label">
                Complemento
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Complemento do endereço"
                    name="complemento"
                    id="complemento"
                    class="form-element"
                    value="<?=$complemento ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="complemento" class="col-sm-3 col-md-2 control-label">
                Bairro
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    placeholder="Bairro do(a) aluno(a)"
                    name="bairro"
                    id="bairro"
                    class="form-element"
                    value="<?=$bairro ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="idestado" class="col-sm-3 col-md-2 control-label">
                Estado
            </label>

            <div class="col-sm-3">
                <select name="idestado"  id="idestado" class="form-element">
                    <option value="-1" <?=$this->selected(empty($aluno->pessoa->estado))?>>
                        Selecione
                    </option>

                    <?php foreach ($estados as $estado) : ?>
                        <option
                            value="<?=$estado->id?>"
                            <?=$this->selected(!empty($aluno->pessoa->estado->id) && $estado->id == $aluno->pessoa->estado->id)?>
                        >
                            <?=$estado->sgl_estado?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="idcidade" class="col-sm-3 col-md-2 control-label">
                Cidade
            </label>

            <div class="col-sm-3">
                <select name="idcidade"  id="idcidade" class="form-element">
                    <option value="" selected="selected">Selecione</option>
                    <?php if ($idaluno > 0) : ?>
                        <?php foreach ($cidades as $cidade) : ?>
                            <option
                                value="<?=$cidade->id?>"
                                <?=$this->selected(!empty($aluno->pessoa->cidade) && $cidade == $aluno->pessoa->cidade)?>
                            >
                                <?=$cidade->nom_cidade?>
                            </option>
                        <?php endforeach ?>
                    <?php endif ?>
                </select>
            </div>
        </div>
    </section>

    <?php if ($aluno) : ?>
        <section>
            <h2 class="rounded section-forms">
                Usuário do sistema
            </h2>

            <input type="hidden" name="nfse" id="nfse1" value="1" checked="checked" />

            <div class="form-group">
                <label for="usuarioaluno" class="col-sm-3 col-md-2 control-label">
                    Usuário
                </label>

                <div class="col-sm-3">
                    <input
                        type="text"
                        disabled="true"
                        class="form-element"
                        id="usuarioaluno"
                        name="usuarioaluno"
                        value="<?=$login ?? ''?>"
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="senhaaluno" class="col-sm-3 col-md-2 control-label">
                    Senha
                </label>

                <div class="col-sm-3">
                    <input
                        type="password"
                        disabled="true"
                        class="form-element"
                        id="senhaaluno"
                        name="senhaaluno"
                        value="<?=$senha ?? ''?>"
                    />
                </div>
            </div>

            <?php if ($idaluno && empty($login) && empty($senha)) : ?>
            <div id="aviso-usuario-nao-criado" class="row">
                <div class="form-group">
                    <span class="col-sm-3"></span>

                    <div class="col-sm-6">
                        <small style="display:block; color: red;">Não existe credencial de usuário no sistema</small>
                        <button type="button" class="btn primary-color" onclick="gerarUsuario()">
                            Criar usuário
                        </button>
                    </div>
                </div>
            </div>
            <?php endif ?>
        </section>
    <?php endif ?>

    <section>
        <h2 class="section-forms rounded">
            Observações
        </h2>

        <textarea
            type="text"
            placeholder="Observações do(a) aluno(a)"
            name="observacoes"
            id="observacoes"
            class="form-element"
            style="min-height: 120px;"
        ><?=$aluno->observacoes ?? ''?></textarea>
    </section>
</div>

<script>
$("#idestado").change(function () {
    var cidadeElement = document.getElementById('idcidade');
    cidadeElement.disabled = true;

    $.ajax({
        url: "dao/estados.php",
        type: "POST",
        data: {
            action: "recebeCidades",
            idestado: $('#idestado :selected').val()
        },
        context: jQuery('#idcidade'),
        beforeSend: bloqueiaUI,
        complete: $.unblockUI,
        success: function (data) {
            this.html('<option value="-1">Selecione</option>');
            this.append(data);
            cidadeElement.disabled = false;
        }
    });
});

$('#idpaisnascimento').change(function () {
    let paisId = $('#idpaisnascimento').val();
    $.getJSON('/dao/localidades.php', { tipo: 'estados', cod_pais: paisId }, function (data) {
        let placeholder = $("#idestadonascimento option:first").html()
        $("#idestadonascimento").empty()
        $("#idestadonascimento").append()
        $('#idestadonascimento').append('<option selected disabled>'+placeholder+'</option>');
        data.forEach(element => {
            $('#idestadonascimento').append('<option value="'+element.id+'">'+element.sigla+' - '+ element.nome +'</option>');
        });
    })
})

// Adiciona Mutaion Observer para manter os documentos selecionados porque outras operações podem alterar o select
let documentosSelecionados = [];

function storeSelectedOptions(selectElement) {
    documentosSelecionados = Array.from(selectElement.selectedOptions).map(option => option.value);
}

function restoreSelectedOptions(selectElement) {
    for (let option of selectElement.options) {
        option.selected = documentosSelecionados.includes(option.value);
    }
}

document.getElementById('documentosentregues').addEventListener('change', function () {
    storeSelectedOptions(this);
});

let documentosentregues = document.getElementById('documentosentregues');

let observer = new MutationObserver(function (mutationsList, observer) {
    for (let mutation of mutationsList) {
        if (mutation.type === 'childList') {
            restoreSelectedOptions(documentosentregues);
        }
    }
});

observer.observe(documentosentregues, { childList: true });

function cloneEmailRow(element) {
    const cloner = element;
    const row = cloner.parentNode.parentNode;
    const clonedRow = row.cloneNode(true);
    const tbody = row.parentNode;

    for (const item of row.querySelectorAll('.email-template-item')) {
        item.classList.remove('email-template-item', 'hidden');
    }

    row.classList.remove('email-template');
    cloner.remove();

    tbody.appendChild(clonedRow);
}

function cloneTelefoneRow(element) {
    const cloner = element;
    const row = cloner.parentNode.parentNode;
    const clonedRow = row.cloneNode(true);
    const tbody = row.parentNode;

    for (const item of row.querySelectorAll('.telefone-template-item')) {
        item.classList.remove('telefone-template-item', 'hidden');
    }

    row.classList.remove('telefone-template');
    cloner.remove();

    tbody.appendChild(clonedRow);
}

function desbloqueiaNomeSocial( origem ) {
        if($('#'+origem).hasAttr('disabled')) $('#'+origem).removeAttr('disabled');
        else $('#'+origem).attr('disabled','disabled');
    }

function mudarTexto() {
  var x = document.getElementById("nomeLabel");
  if (x.innerHTML === "Nome Social *") {
    x.innerHTML = "Nome *";
  } else {
    x.innerHTML = "Nome Social *";
  }
}
</script>
