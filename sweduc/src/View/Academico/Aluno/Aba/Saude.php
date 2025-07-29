<div role="tabpanel" class="tab-pane" id="tab_saude">
    <div class="flex flex-wrap -mx-2">
        <div class="w-full sm:w-auto p-2 ml-auto">
            <button
                type="button"
                class="btn primary-color btn-block"
                id="ficha-basica"
            >
                Ficha Básica
            </button>
        </div>

        <div class="w-full sm:w-auto p-2">
            <button
                type="button"
                class="btn primary-color btn-block"
                id="ficha-anamnese"
            >
                Ficha de Anamnese
            </button>
        </div>

        <div class="w-full sm:w-auto p-2">
            <button
                type="button"
                class="btn primary-color btn-block"
                id="ficha-sindrome-gripal"
            >
                Ficha de Sindrome Gripal
            </button>
        </div>
    </div>

    <div id="frm-ficha-basica">
        <div class="form-group">
            <label for="tiposanguineo" class="col-sm-2 control-label">Tipo sanguíneo</label>
            <div class="col-sm-3">
                <select id="tiposanguineo" name="tiposanguineo" class="form-element">
                    <option value="-1"> </option>
                    <option value="A"  <?=$this->selected($tiposanguineo == "A")?>>
                        A
                    </option>
                    <option value="B"  <?=$this->selected($tiposanguineo == "B")?>>
                        B
                    </option>
                    <option value="AB" <?=$this->selected($tiposanguineo == "AB")?>>
                        A
                    B</option>
                    <option value="O"  <?=$this->selected($tiposanguineo == "O")?>>
                        O
                    </option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="fatorrh" class="col-sm-2 control-label">
                Fator RH
            </label>

            <div class="col-sm-3">
                <select id="fatorrh" name="fatorrh" class="form-element">
                    <option value="-1" <?=$this->selected($fatorRH == "-1")?>>
                        N&atilde;o informado
                    </option>

                    <option value="Positivo" <?=$this->selected($fatorRH == "Positivo")?>>
                        Positivo
                    </option>

                    <option value="Negativo" <?=$this->selected($fatorRH == "Negativo")?>>
                        Negativo
                    </option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="pediatra" class="col-sm-2 control-label">
                Nome do pediatra
            </label>

            <div class="flex col-sm-10 col-md-6 -m-2">
                <div class="w-2/3 p-2">
                    <input
                        type="text"
                        placeholder="Nome do pediatra"
                        id="pediatra"
                        name="pediatra"
                        class="form-element"
                        value="<?=$pediatra?>"
                    >
                </div>

                <div class="w-1/3 p-2">
                    <input
                        type="text"
                        placeholder="Telefone do pediatra"
                        id="pediatra_tel"
                        name="pediatra_tel"
                        class="form-element"
                        value="<?=$pediatra_tel?>"
                    >
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="planosaude" class="col-sm-2 control-label">
                Plano de Saúde
            </label>

            <div class="flex col-sm-10 col-md-6 -m-2">
                <div class="w-2/3 p-2">
                    <input
                        type="text"
                        id="planosaude"
                        placeholder="Nome do plano de saúde"
                        name="planosaude"
                        class="form-element"
                        value="<?=$planosaude?>"
                    >
                </div>

                <div class="w-1/3 p-2">
                    <input
                        type="text"
                        placeholder="Telefone do plano"
                        id="planosaude_tel"
                        name="planosaude_tel"
                        class="form-element"
                        value="<?=$planosaude_tel?>"
                    >
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="medicamentofebre" class="col-sm-2 control-label">Medicamento para febre (com a dosagem)</label>
            <div class="col-sm-4">
                <textarea name="medicamentofebre" rows="5" id="medicamentofebre" class="form-element" ><?php echo  $medicamentofebre ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="alergiamedicamentos" class="col-sm-2 control-label">Alergia aos medicamentos</label>
            <div class="col-sm-4">
                <textarea name="alergiamedicamentos" id="alergiamedicamentos" rows="5" class="form-element" ><?php echo  $alergiamedicamentos ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="alergiaalimentos" class="col-sm-2 control-label">Alergia aos alimentos</label>
            <div class="col-sm-4">
                <textarea name="alergiaalimentos" id="alergiaalimentos" rows="5" class="form-element" ><?php echo  $alergiaalimentos ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="tratamentosmedicos" class="col-sm-2 control-label">Tratamentos médicos em realização</label>
            <div class="col-sm-4">
                <textarea name="tratamentosmedicos" id="tratamentosmedicos" rows="5" class="form-element" ><?php echo  $tratamentosmedicos ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="medicamentosregulares" class="col-sm-2 control-label">Medicamentos de uso regular</label>
            <div class="col-sm-4">
                <textarea name="medicamentosregulares" id="medicamentosregulares" rows="5" class="form-element" ><?php echo  $medicamentosregulares ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="observacoesmedicas" class="col-sm-2 control-label">Observações médicas</label>
            <div class="col-sm-4">
                <textarea name="observacoesmedicas" id="observacoesmedicas" rows="5" class="form-element" ><?php echo  $observacoesmedicas ?></textarea>
            </div>
        </div>
    </div>

    <div id="frm-ficha-anamnese"></div>
    <div id="frm-ficha-sindrome-gripal"></div>
</div>
