<!-- TAB ELETIVAS -->
<div role="tabpanel" class="tab-pane" id="tab_eletivas">
    <h2 class="section-forms">
        Disciplinas Eletivas <?=$anoletivomatriculaaluno?>
    </h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th style="width:250px;">Disciplina</th>
                <th style="width:90px;"></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($eletivas as $eletiva) : ?>
                <tr>
                    <td>
                        <?= $eletiva['disciplina'] ?>
                    </td>

                    <td>
                        <input
                            type="button"
                            id="btneletiva<?= $eletiva['id']?>"
                            class="btn <?=$eletiva['elclass']?>"
                            value="<?=$eletiva['elval']?>"
                        />
                    </td>
                </tr>

                <script>
                    $('#btneletiva<?= $eletiva['id'] ?>').on('click',function(){
                        var obs = $('#obseletiva<?= $eletiva['id'] ?>').val();
                        if( $('#btneletiva<?= $eletiva['id'] ?>').val() == 'Remover aluno' ) {
                            if(confirm('Tem certeza? As notas também serão removidas.')) {
                                cadastraAlunoEletiva(
                                    <?=$eletiva['id']?>,
                                    <?=$idaluno?>,
                                    <?=$nummatricula?>,
                                    <?=$matricula->anoletivomatricula?>,
                                    obs
                                );
                            }
                        } else {
                            cadastraAlunoEletiva(
                                <?=$eletiva['id']?>,
                                <?=$idaluno?>,
                                <?=$nummatricula?>,
                                <?=$matricula->anoletivomatricula?>,
                                obs
                            );
                        }
                    });
                </script>
            <?php endforeach ?>
        </tbody>
    </table>

    <div class="hr-line-dashed"></div>
</div>
<!-- /TAB ELETIVAS -->
