<div class="flex flex-wrap -m-1">
    <?php if ($idanoletivo != '-2') { // NÃO EX-ALUNOS   ?>
        <div class="p-1">
            <button
                type="button"
                data-toggle="tooltip"
                data-placement="bottom"
                title="Ficha do Aluno"
                class="btn sw-btn-primary"
                id="edita<?= $cnt; ?>"
                onclick="edita(<?= $row['as_id'] ?>, '<?=$row['am_id']?>');"
            >
                <?php echo ($usuario->autorizado('academico-alunos-editar')) ? '<i class="fa fa-edit"></i>' : '<i class="fa fa-eye"></i>'; ?>
            </button>
        </div>

        <?php if ($usuario->autorizado('academico-alunos-excluir') && $row['turmamatricula'] < 1) : ?>
            <div class="p-1">
                <input
                    type="button"
                    class="btn danger-color"
                    onclick="apaga(<?= $row['as_id'] ?>);" value=" X "
                >
            </div>
        <?php endif ?>

        <div class="p-1">
            <button
                type="button"
                data-toggle="tooltip"
                data-placement="bottom"
                title="Contatos do Aluno"
                class="btn sw-btn-primary"
                onclick="view('<?= addslashes($row['nome']) ?>', '<?= $row['dtnasc'] ?>', '<?= $row['numeroaluno'] ?>', '<?= $row['unidade'] ?>', '<?= $row['curso'] ?>', '<?= $row['serie'] ?>', '<?= $row['turma'] ?>', '<?= $row['nummatricula'] ?>',<?= $row['as_id'] ?>,<?= $row['pid'] ?>,<?= $row['status'] ?>);"
            >
                <i class="fa fa-address-book"></i>
            </button>
        </div>

        <?php if ($usuario->autorizado('financeiro-contas-a-receber-consultar') && $row['turmamatricula'] != "-1") : ?>
            <div class="p-1">
                <button
                    type="button"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    title="Área financeira do aluno"
                    class="btn sw-btn-primary"
                    onclick="ffin('<?= $row['nummatricula'] ?>', <?= $row['as_id'] ?>,<?= $row['pid'] ?>,<?= $row['uid'] ?>, <?= $row['turmamatricula'] ?>, <?= $row['am_id'] ?>);"
                >
                    <i class="fa fa-dollar-sign"></i>
                </button>
            </div>
        <?php endif ?>

        <div class="p-1">
            <button
                type="button"
                data-toggle="tooltip"
                data-placement="bottom"
                title="Enviar email"
                class="btn sw-btn-primary"
                onclick="
                    $('input:checkbox').removeAttr('checked');
                    $('#dialog-email-aluno').data('idaluno', '<?= $row['as_id'] ?>')
                                            .data('idpessoa', '<?= $row['pid'] ?>')
                                            .modal('toggle');
                "
            >
                <i class="fa fa-envelope"></i>
            </button>
        </div>

        <?php if ($usuario->autorizado('academico-matriculados-novo-ano-consultar') && $row['turmamatricula'] != "-1") : ?>
            <div class="p-1">
                <button
                    type="button"
                    class="btn sw-btn-primary text-bold"
                    onclick="medias('<?= $row['turmamatricula']; ?>', '<?= $row['anoletivomatricula']; ?>',<?= $row['as_id'] ?>,<?= $row['nummatricula'] ?>);"
                >
                    MÉDIAS
                </button>
            </div>

            <div class="p-1">
                <button
                    type="button"
                    class="btn sw-btn-primary text-bold"
                    onclick="historico(<?=$row['as_id']?>);"
                >
                    HISTÓRICO
                </button>
            </div>

            <div class="p-1">
                <button
                    type="button"
                    class="btn sw-btn-primary text-bold"
                    onclick="ocorrencias(<?= $row['as_id'] ?>, '<?= addslashes($row['nome']) ?>', '<?= $row['uid'] ?>', <?= $row['anoletivomatricula']; ?>);"
                >
                    OCORRÊNCIAS
                </button>
            </div>

            <div class="p-1">
                <button
                    type="button"
                    class="btn sw-btn-primary text-bold"
                    onclick="entrevistas(<?= $row['am_id'] ?>);"
                >
                    ENTREVISTAS
                </button>
            </div>
        <?php endif ?>
    <?php } elseif ($idanoletivo == -2) { // EX-ALUNOS ANTIGOS  ?>
        <div class="p-1">
            <button
                type="button"
                class="btn sw-btn-primary text-bold"
                id="hist<?= $row['id'] ?>"
                onclick="historicoAntigos('<?= addslashes($row['nome'])?>');"
            >
                HISTÓRICO
            </button>
        </div>
    <?php } ?>

    <div class="p-1">
        <button
            type="button"
            class="btn sw-btn-primary text-bold"
            id="hist<?= $row['id'] ?>"
            onclick="protocolo('<?= $row['as_id'] ?>', '<?= addslashes($row['nome']) ?>', '<?= $row['unidade'] ?>', '<?= $row['uid'] ?>');"
        >
            SOLICITAÇÃO
        </button>
    </div>

    <div class="p-1">
        <button
            type="button"
            class="btn sw-btn-primary text-bold"
            onclick="geraboletimnovo('1', '<?= addslashes($row['nome']) ?>', <?= $row['as_id'] ?>, '<?= $row['nummatricula'] ?>', '<?= $row['anoletivomatricula'] ?>', '<?= $row['uid'] ?>');"
        >
            BOLETIM AVALIAÇÕES
        </button>
    </div>

    <?php if ($row['rematriculaHabilitada']) : ?>
        <div class="p-1">
            <button
                type="button"
                class="btn sw-btn-primary"
                data-toggle="modal"
                data-target="#modalRematricula"
                data-aluno-id="<?=$row['as_id']?>"
                data-matricula-id="<?=$row['am_id']?>"
            >
                Kit Renovação de Matrícula
            </button>
        </div>
    <?php endif ?>

    <?php if ($usuario->autorizado('sistema-autenticacao-personificar-alunos-responsaveis')) { ?>
        <div class="p-1">
            <a
                href="/impersonate?usuarioId=<?=$row['usuario_id']?>"
                data-toggle="tooltip"
                data-placement="bottom"
                title="Entrar na conta"
                class="btn sw-btn-primary"
            >
                <i class="far fa-eye"></i>
                Entrar como usuário
            </a>
        </div>
    <?php } ?>
</div>
