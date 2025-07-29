<div role="tabpanel" class="tab-pane" id="tab_dadosadicionais">
    <section>
        <h2 class="section-forms">Intercâmbio</h2>

        <div class="form-group">
            <label for="rneci" class="col-sm-2 control-label">
                RNECI Estrangeiro
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    id="rneci"
                    name="rneci"
                    class="form-element"
                    value="<?=$rneci ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="dataexpedicaointercambio" class="col-sm-2 control-label">
                Data de Expedição
            </label>

            <div class="col-sm-3">
                <input
                    type="date"
                    name="dataexpedicaointercambio"
                    id="dataexpedicaointercambio"
                    class="form-element"
                    value="<?=$aluno->dataexpedicao ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="datavalidacaointercambio" class="col-sm-2 control-label">
                Data de Validação
            </label>

            <div class="col-sm-3">
                <input
                    type="date"
                    name="datavalidacaointercambio"
                    id="datavalidacaointercambio"
                    class="form-element"
                    value="<?=$aluno->dataexpedicao ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="corraca" class="col-sm-2 control-label">
                Cor/Raça
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="corraca"
                    class="form-element"
                    value="<?=$aluno->raca ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="numcarteira" class="col-sm-2 control-label">
                Nº Carteira
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="numcarteira"
                    id="numcarteira"
                    class="form-element"
                    value="<?=$numerocarteira ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="validade-carteira" class="col-sm-2 control-label">
                Validade
            </label>

            <div class="col-sm-3">
                <input
                    type="date"
                    name="validade"
                    id="validade-carteira"
                    class="form-element"
                    value="<?=$aluno->validade ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="estadointercambio" class="col-sm-2 control-label">
                Estado
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="estadointercambio"
                    id="estadointercambio"
                    class="form-element"
                    value="<?=$estadointercambio ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="cidadeintercambio" class="col-sm-2 control-label">
                Cidade
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="cidadeintercambio"
                    id="cidadeintercambio"
                    class="form-element"
                    value="<?=$cidadeintercambio ?? ''?>"
                />
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms">Situação Militar</h2>

        <div class="form-group">
            <label for="situacaomilitar" class="col-sm-2 control-label">
                Situação militar
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="situacaomilitar"
                    id="situacaomilitar"
                    class="form-element"
                    value="<?=$situacaomilitar ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="regiaomilitar" class="col-sm-2 control-label">
                Região militar
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="regiaomilitar"
                    class="form-element"
                    value="<?=$regiaomilitar ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="certificadomilitar" class="col-sm-2 control-label">
                Nº do Certificado Militar
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="certificadomilitar"
                    id="certificadomilitar"
                    class="form-element"
                    value="<?=$certificadomilitar ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="dscertificadomilitar" class="col-sm-2 control-label">
                DS Certificado Militar
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="dscertificadomilitar"
                    id="dscertificadomilitar"
                    class="form-element"
                    value="<?=$dscertificadomilitar ?? ''?>"
                />
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms">
            Trabalho
        </h2>

        <div class="form-group">
            <label for="trabalhoempresa" class="col-sm-2 control-label">
                Empresa
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="trabalhoempresa"
                    id="trabalhoempresa"
                    class="form-element"
                    value="<?=$empresatrabalho ?? ''?>"
                />
            </div>
        </div>

        <div class="form-group">
            <label for="trabalhotelefone" class="col-sm-2 control-label">
                Telefone
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="trabalhotelefone"
                    id="trabalhotelefone"
                    class="form-element"
                    value="<?=$telefonetrabalho ?? ''?>"
                />
            </div>
        </div>
    </section>

    <section>
        <h2 class="section-forms">
            Título Eleitoral
        </h2>

        <div class="form-group">
            <label for="tituloinscricao" class="col-sm-2 control-label">
                Nº Inscrição
            </label>

            <div class="col-sm-3">
                <input
                    type="text"
                    name="tituloinscricao"
                    id="tituloinscricao"
                    class="form-element"
                    value="<?=$te_numero ?? ''?>"
                />
            </div>
        </div>
    </section>
</div>
