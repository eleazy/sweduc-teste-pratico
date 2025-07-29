<div id="profile-picture-upload" class="text-center">
    <label for="profile-picture-file" class="block">
        <div class="pb-4">
            <img
                id="profile-picture-img"
                src="<?=$fotoUrl?>?w=200&fit=fill"
                class="rounded w-full mx-auto"
                style="width: 200px;height: auto;object-fit: contain;"
                onerror="this.src = ''"
            />
        </div>

        <input
            id="profile-picture-file"
            name="profile-picture-file"
            <?php if (!empty($pessoaId)) : ?>
                data-pessoa-id="<?=$pessoaId?>"
                onchange="profilePictureSelected()"
            <?php else : ?>
                onchange="updateProfilePictureThumb()"
            <?php endif ?>
            type="file"
            style="display: none;"
            accept="image/*"
        >

        <span class="sw-btn green-color profile-picture-upload-stage whitespace-nowrap">
            <i class="fa fa-upload mr-1"></i>
            Selecionar imagem
        </span>

        <div class="flex">
            <button
                id="profile-picture-upload-send"
                <?php if (!empty($pessoaId)) : ?>
                    onclick="profilePictureUploadSend()"
                <?php endif ?>
                type="button"
                class="sw-btn sw-btn-primary profile-picture-upload-stage whitespace-nowrap mr-1 hidden"
            >
                <i class="fa fa-check mr-1"></i>
                Enviar
            </button>

            <button
                id="profile-picture-upload-abort"
                onclick="profilePictureUploadAbort()"
                type="button"
                class="sw-btn sw-btn-warning profile-picture-upload-stage whitespace-nowrap hidden"
            >
                <i class="fa fa-exclamation-circle mr-1"></i>
                Descartar
            </button>
        </div>
    </label>

    <small>
        Tamanho máximo 1Mb e formato jpeg/png
    </small>
</div>

<script>
    function updateProfilePictureThumb() {
        const reader = new FileReader();
        const file = document.getElementById('profile-picture-file').files[0];

        reader.onload = function (event) {
            const profilePicture = document.querySelector('#profile-picture-img');
            profilePicture.dataset['oldsrc'] = profilePicture.src;
            profilePicture.src = event.target.result;

            document.querySelector('#profile-picture-file').name = 'profile-picture-file';
        };

        reader.readAsDataURL(file);
    }

    function toggleUploadStage() {
        for(const stage of document.querySelectorAll('.profile-picture-upload-stage')) {
            stage.classList.toggle('hidden');
        }
    }

    function profilePictureSelected() {
        toggleUploadStage();
        updateProfilePictureThumb();
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
