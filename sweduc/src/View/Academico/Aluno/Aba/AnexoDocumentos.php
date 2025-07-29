<?php

use App\Model\Config\DocumentoRematricula;
use App\Academico\Model\DocumentoMatriculaEnviado;

$documentos = DocumentoRematricula::orderBy('documento')->get();
$documentosEnviados = DocumentoMatriculaEnviado::where('matricula_id', $idmatricula)
    ->whereNull('substituido_em')
    ->get();

?>

<div role="tabpanel" class="tab-pane" id="tab_anexodocumentos">
    <input
        id="doc-upload"
        type="file"
        name="doc-upload"
        style="display: none !important;"
        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />

    <section>
        <h2 class="section-forms">Documentos Cadastrados</h2>

        <div class="docs-list">
            <?php foreach ($documentos as $documento) : ?>
                <div class="doc-wrapper">
                    <div class="doc-title">
                        <h3><?= $documento->documento ?></h3>
                    </div>

                    <div class="doc-obrigatoriedade">
                        <p class="weak">Obrigatorio:</p>
                        <p class="strong"><?= $documento->obrigatoriedade ? 'Sim' : 'Não' ?></p>
                    </div>

                    <div class="doc-anexado" id="doc-anexado-<?= $documento->id ?>">
                        <?php if ($documentosEnviados->where('documento_rematricula_id', $documento->id)->isNotEmpty()) : ?>
                            <p class="weak" style="color: #007bff;">
                                <?= $documentosEnviados->where('documento_rematricula_id', $documento->id)->first()->arquivo_nome ?>
                            </p>
                            <div class="upload-file" data-idmatricula="<?= $idmatricula ?>" data-docid="<?= $documento->id ?>">
                                <i class="fa fa-file-upload"></i>
                                <p class="weak">Substituir Arquivo</p>
                            </div>
                            <div class="download-file" data-docenviado="<?= $documentosEnviados->where('documento_rematricula_id', $documento->id)->first()->documento_file_id ?>">
                                <i class="fa fa-download"></i>
                                <p class="weak">Baixar Arquivo</p>
                            </div>

                        <?php else : ?>
                            <div class="upload-file" data-idmatricula="<?= $idmatricula ?>" data-docid="<?= $documento->id ?>">
                                <i class="fa fa-file-upload"></i>
                                <p class="weak">Fazer Upload</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </section>
</div>

<script>
    let currentUploadDiv = null;

    function uploadFile(event) {
        currentUploadDiv = event.currentTarget;
        document.getElementById('doc-upload').click();
    }
    document.querySelectorAll('.upload-file').forEach(item => {
        item.addEventListener('click', event => uploadFile(event));
    });

    document.getElementById('doc-upload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) {
            alert("No file selected");
            return;
        }

        if (file && currentUploadDiv) {
            const matriculaId = currentUploadDiv.dataset.idmatricula;
            const docId = currentUploadDiv.dataset.docid;

            const formData = new FormData();
            formData.append('file', file);

            const load = document.querySelector('#loading-screen')
            load.style.display = 'flex';

            fetch(`/api/v1/academico/matriculas/${matriculaId}/docs/${docId}`, {
                    method: 'POST',
                    headers: {
                        'X-File-Name': file.name,
                    },
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) throw new Error("Erro ao enviar o arquivo");
                    return response.json();
                })
                .then(data => {
                    const docFileId = data.documento_file_id;

                    const div = document.querySelector('#doc-anexado-' + docId);
                    div.value = '';
                    div.innerHTML = `
                        <div class="doc-anexado">
                            <p class="weak" style="color: #007bff;"> ${file.name} </p>
                            <div class="upload-file" data-idmatricula="${matriculaId}" data-docid="${docId}" onclick="uploadFile(event)">
                                <i class="fa fa-file-upload"></i>
                                <p class="weak">Substituir Arquivo</p>
                            </div>
                            <div class="download-file" data-docenviado="${docFileId}" onclick="downloadFile(event)">
                                <i class="fa fa-download"></i>
                                <p class="weak">Baixar Arquivo</p>
                            </div>
                        </div>`;
                })
                .catch(err => {
                    currentUploadDiv.innerHTML = `<i class="fa fa-times-circle"></i><p class="weak">Falha no envio</p>`;
                    alert(err.message);
                })
                .finally(() => {
                    load.style.display = 'none';
                });
        }
    });

    function downloadFile(event) {
        const docId = event.currentTarget.dataset.docenviado;
        const load = document.querySelector('#loading-screen')
        load.style.display = 'flex';

        fetch(`/api/v1/academico/matriculas/docs/${docId}`, {
                method: 'GET',
            })
            .then(response => {
                if (!response.ok) throw new Error("Erro ao baixar o arquivo");
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = docId;
                document.body.appendChild(a);
                a.click();
                a.remove();
            })
            .catch(err => {
                alert(err.message);
            })
            .finally(() => {
                load.style.display = 'none';
            });
    }
    document.querySelectorAll('.download-file').forEach(item => {
        item.addEventListener('click', event => downloadFile(event));
    });
</script>

<style>
    .docs-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }

    .doc-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 15px;
        width: 30vw;
    }

    .doc-wrapper:hover {
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        scale: 1.02;
        transition: all 0.3s ease;
    }


    .doc-title h3 {
        font-size: 18px;
        color: #333;
        opacity: 0.9;
    }

    .doc-obrigatoriedade {
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .doc-anexado {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
    }

    .doc-obrigatoriedade .weak,
    .doc-anexado .weak {
        font-size: 14px;
        color: #666;
    }

    .doc-obrigatoriedade .strong,
    .doc-anexado .strong {
        font-size: 16px;
        color: #333;
        opacity: 0.9;
    }

    .doc-anexado .upload-file,
    .doc-anexado .download-file {
        color: #007bff;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .doc-anexado .upload-file:hover,
    .doc-anexado .download-file:hover {
        text-decoration: underline;
    }

    .doc-anexado .upload-file p,
    .doc-anexado .download-file p {
        margin: 0;
        font-size: 14px;
        color: #007bff;
        cursor: pointer;
    }

    .doc-anexado .upload-file:hover p,
    .doc-anexado .download-file:hover p {
        text-decoration: underline;
    }

    .doc-anexado .upload-file:hover i,
    .doc-anexado .download-file:hover i {
        color: #0056b3;
    }

    .doc-anexado .upload-file:hover p,
    .doc-anexado .download-file:hover p {
        color: #0056b3;
    }
</style>
