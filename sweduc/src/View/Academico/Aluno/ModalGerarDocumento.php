<div
    id="modal-gerar-documento-aluno"
    class="fixed inset-0 overflow-y-auto hidden"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    onclick="document.getElementById('modal-gerar-documento-aluno').classList.toggle('hidden')">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-20"
            onclick="event.stopPropagation()">

            <div class="modal-box-search">
                <div class='modal-box-header'>
                    <h4> Com os Selecionados... </h4>
                    <input
                        type="text"
                        id="searchDocInput"
                        class="search-input"
                        placeholder="Buscar documento ou ação..."
                        oninput="buscarDocumentos(true)">
                </div>
                <div class="search-results"></div>
            </div>

        </div>
    </div>
</div>

<script>
    let isLoading = false;
    let currentPage = 1;
    const resultadoDocs = document.querySelector('.search-results');

    function buscarDocumentos(isNewSearch = false) {
        if (isLoading) return;
        isLoading = true;

        if (isNewSearch) {
            currentPage = 1;
            resultadoDocs.innerHTML = '';
        }

        const searchText = document.getElementById('searchDocInput').value;

        fetch(`/documento-academico-buscar?search=${encodeURIComponent(searchText)}&page=${currentPage}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Ocorreu um erro ao buscar os documentos acadêmicos.');
                }
                return response.json();
            })
            .then((data) => {
                if (data.docs.length === 0 && currentPage === 1) {
                    resultadoDocs.innerHTML = '<p class="no-results">Nenhum resultado encontrado.</p>';
                    return;
                }

                data.docs.forEach((doc) => {
                    if (doc.tipo === 'separador') {
                        const div = document.createElement('div');
                        div.className = 'separator-row';
                        div.innerHTML = `
                        <div class="">
                            <p class="">${doc.nomedoc}</p>
                        </div>
                    `;
                        resultadoDocs.appendChild(div);
                    } else {
                        const div = document.createElement('div');
                        div.className = 'info-row';
                        div.id = doc.id;
                        div.innerHTML = `
                            <div class="doc-info">
                                <p class="search-item">${doc.nomedoc}</p>
                            </div>
                        `;
                        div.addEventListener('click', () => {
                            const input = document.getElementById('oqfazer');
                            input.value = doc.contexto !== undefined ? (doc.contexto === 6 ? 11 : 10) : doc.id;

                            document.getElementById('oqfazerValue').value = doc.id;
                            document.getElementById('oqfazerDisplay').value = doc.nomedoc;
                            document.getElementById('modal-gerar-documento-aluno').classList.toggle('hidden');
                            verificaSeDocumentoEnviaEmail();
                        });
                        resultadoDocs.appendChild(div);
                    }
                });

                currentPage++;
            })
            .catch((error) => {
                console.error('Get Academic Documents Error:', error);
            })
            .finally(() => {
                isLoading = false
            });
    }

    resultadoDocs.addEventListener('scroll', () => {
        const scrollPosition = resultadoDocs.scrollTop + resultadoDocs.clientHeight;
        const scrollHeight = resultadoDocs.scrollHeight;

        if (scrollPosition >= scrollHeight - 10) {
            buscarDocumentos();
        }
    });

    buscarDocumentos();
</script>

<style>
    #modal-gerar-documento-aluno {
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
        position: sticky;
        top: 0;
        background-color: #f9f9f9;
        z-index: 1;
    }

    .modal-box-search {
        flex: 1 1 30%;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        height: 70vh;
        overflow: hidden;
    }

    .search-results {
        max-height: calc(70vh - 130px);
        overflow-y: auto;
    }

    .separator-row {
        background-color: #f5e1b3;
        text-align: center;
        margin: 10px 0px;
        border-radius: 6px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        padding: 8px 0;
    }

    .info-row:hover {
        background-color: #f1f1f1;
    }

    .doc-info p {
        margin: 0;
        font-size: 14px;
        color: #171717;
        cursor: pointer;
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
