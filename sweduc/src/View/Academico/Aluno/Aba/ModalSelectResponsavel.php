<div
    id="modal-selecionar-responsavel"
    class="fixed inset-0 overflow-y-auto hidden"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    onclick="d.getElementById('modal-selecionar-responsavel').classList.toggle('hidden')">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-scroll shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-20"
            onclick="event.stopPropagation()">
            <div class="modal-box-search">
                <div class='modal-box-header'>
                    <h4> Selecionar Responsável </h4>
                    <input
                        type="text"
                        id="searchRespInput"
                        class="search-input"
                        placeholder="Buscar responsavel..."
                        oninput="buscarResponsaveis(true)">
                </div>
                <div class="search-results"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const d = document;

    let isLoading = false;
    let currentPage = 1;
    const resultadoResponsaveis = d.querySelector('.search-results');
    //window.selectedResponsavel = []; // Store selected responsavel IDs

    function buscarResponsaveis(isNewSearch = false) {
        if (isLoading) return;
        isLoading = true;

        if (isNewSearch) {
            currentPage = 1;
            resultadoResponsaveis.innerHTML = '';
        }

        const searchText = d.getElementById('searchRespInput').value;

        fetch(`academico/responsaveis-autocomplete?term=${encodeURIComponent(searchText)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.length == 0 && currentPage == 1) {
                    resultadoResponsaveis.innerHTML = '<p class="no-results">Nenhum responsavel encontrado.</p>';
                    return;
                }

                data.forEach((responsavel) => {
                    const div = d.createElement('div');
                    div.id = responsavel.value;
                    div.innerHTML = `
                        <div class="responsavel-info info-row">
                            <p class="search-item">${responsavel.label}</p>
                        </div>
                    `;
                    div.addEventListener('click', () => selectItem(responsavel));
                    resultadoResponsaveis.appendChild(div);
                });

                currentPage++;
            })
            .catch(error => console.error("Error fetching courses:", error))
            .finally(() => {
                isLoading = false;
            });
    }

    const selectItem = (responsavel) => {
        const input = d.querySelector('#resp_pessoa_id');
        input.value = responsavel.value;
        const p = d.querySelector('#selected-responsavel');
        p.textContent = responsavel.label;

        const assocs = d.querySelectorAll('.assoc');
        assocs.forEach(assoc => {
            assoc.classList.remove('hidden');
        });

        const novoRespForm = d.querySelector('.novoresp');
        if (novoRespForm) {
            novoRespForm.classList.add('hidden');
        }

        const modal = document.getElementById('modal-selecionar-responsavel');
        modal.classList.toggle('hidden');
        window.clickedResponsavel = responsavel.id;
    };

    resultadoResponsaveis.addEventListener('scroll', () => {
        const scrollPosition = resultadoResponsaveis.scrollTop + resultadoResponsaveis.clientHeight;
        const scrollHeight = resultadoResponsaveis.scrollHeight;

        if (scrollPosition >= scrollHeight - 10) {
            buscarResponsaveis();
        }
    });
</script>

<style>
    #modal-selecionar-responsavel {
        z-index: 9999;
    }

    .search-input {
        width: 100%;
        padding: 8px;
        margin-bottom: 5px;
        border: 1px solid #ccc;
        border-radius: 99px;
    }

    .modal-box-header {
        position: fixed;
        top: 0;
        background-color: #f9f9f9;
        z-index: 1;
        width: 90%;
        padding: 20px 0px;
    }

    .modal-box-search {
        /* flex: 1 1 30%; */
        background-color: #f9f9f9;
        margin-top: 120px;
        padding: 0px 20px 20px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        height: 70vh;
        overflow: scroll;
    }

    .display {
        background: #f1f1f1;
        border: 1px solid #ccc;
    }

    .selected-item button {
        color: red;
        border: none;
        cursor: pointer;
        margin-left: 5px;
        font-size: large;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        padding: 8px 0;
        cursor: pointer;
    }

    .search-item {
        cursor: pointer;
    }

    .info-row:hover {
        background-color: #f1f1f1;
    }

    .responsavel-info p {
        margin: 0;
        font-size: 14px;
        color: #171717;
    }

    .modal-box-search h4 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #eebf4f;
        padding-bottom: 5px;
    }

    .modal-box-search::-webkit-scrollbar {
        width: 6px;
    }

    .modal-box-search::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 3px;
    }

    .modal-box-search::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    @media (max-width: 768px) {
        .modal-box-search {
            flex: 1 1 100%;
        }
    }
</style>
