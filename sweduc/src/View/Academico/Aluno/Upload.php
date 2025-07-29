<div id="profile-picture-upload" class="text-center">
    <label for="profile-picture-file" class="block">
        <input
            id="profile-picture-file"
            data-pessoa-id="<?=$pessoaId?>"
            onchange="profilePictureSelected(event)"
            type="file"
            style="display: none;"
            accept="image/*"
        >

        <span class="sw-btn green-color profile-picture-upload-stage whitespace-nowrap">
            <i class="fa fa-upload mr-1"></i>
            Selecionar imagem
        </span>

        <button
            id="profile-picture-upload-send"
            onclick="profilePictureUploadSend()"
            type="button"
            class="sw-btn sw-btn-primary profile-picture-upload-stage hidden"
        >
            <i class="fa fa-check mr-1"></i>
            Enviar
        </button>

        <button
            id="profile-picture-upload-abort"
            onclick="profilePictureUploadAbort()"
            type="button"
            class="sw-btn sw-btn-warning profile-picture-upload-stage hidden"
        >
            <i class="fa fa-exclamation-circle mr-1"></i>
            Descartar
        </button>
    </label>

    <small>
        Tamanho máximo 1Mb e formato jpeg/png
    </small>
</div>

<script>
    function toggleUploadStage() {
        for(const stage of document.querySelectorAll('.profile-picture-upload-stage')) {
            stage.classList.toggle('hidden');
        }
    }

    function profilePictureSelected(event) {
        toggleUploadStage();
        const reader = new FileReader();

        reader.onload = function (event) {
            const profilePicture = document.querySelector('#profile-picture-img');
            profilePicture.dataset['oldsrc'] = profilePicture.src;
            profilePicture.src = event.target.result;
        };

        reader.readAsDataURL(event.currentTarget.files[0]);
    }

    function profilePictureUploadSend() {
        const img = document.getElementById('profile-picture-file');
        const xhr = new XMLHttpRequest();
        const upload = new FormData();

        upload.append('img', img.files[0]);
        xhr.open('POST', '/api/v1/perfil/' + img.dataset.pessoaId + '/img');
        xhr.onreadystatechange = profilePictureUploadFinished();
        xhr.send(upload);
    }

    function profilePictureUploadAbort() {
        toggleUploadStage();
        const profilePicture = document.querySelector('#profile-picture-img');
        profilePicture.src = profilePicture.dataset['oldsrc'];
        document.getElementById('profile-picture-file').value = null;
    }

    function profilePictureUploadFinished() {
        toggleUploadStage();
        document.getElementById('profile-picture-file').value = null;
        criaAlerta('success', 'Imagem de perfil enviada');
    }
</script>
