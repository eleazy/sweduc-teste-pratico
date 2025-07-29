<div
    id="modal-importador-notas"
    class="fixed z-10 inset-0 overflow-y-auto hidden"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    onclick="document.getElementById('modal-importador-notas').classList.toggle('hidden')"
>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-20"
            onclick="event.stopPropagation()"
        >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mt-3 sm:mt-0">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 sm:text-left m-0 p-0" id="modal-title">
                        Importar notas de CSV
                    </h3>

                    <div class="mt-2">
                        <p class="text-sm text-gray-500 sm:text-left m-0 p-0">
                            Deve-se sinalizar o identificador para comparar o aluno,
                            configurar a posição que os dados do identificador e da nota ocupam e indicar
                            se o csv possui cabeçalho
                        </p>
                    </div>

                    <div class="mt-2">
                        <label for="tipo-id">
                            Identificador
                        </label>

                        <select name="tipo-id" id="tipo-id" class="form-element">
                            <option value="numeroaluno" selected>Número do aluno</option>
                        </select>
                    </div>

                    <div class="sm:flex -m-2 mt-2">
                        <div class="p-2">
                            <label for="posicao-id">
                                Posição do identificador
                            </label>

                            <input type="number" name="posicao-id" id="posicao-id" class="form-element" value="1">
                        </div>

                        <div class="p-2">
                            <label for="posicao-nota">
                                Posição da nota
                            </label>

                            <input type="number" name="posicao-nota" id="posicao-nota" class="form-element" value="2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex">
                <div class="ml-auto">
                    <button
                        type="button"
                        class="sw-btn sw-btn-danger mr-2"
                        onclick="document.getElementById('modal-importador-notas').classList.toggle('hidden')"
                    >
                        Cancelar
                    </button>
                </div>

                <div>
                    <label for="arquivo-csv" class="sw-btn sw-btn-secondary">
                        Importar

                        <input
                            id="arquivo-csv"
                            type="file"
                            name="csv"
                            style="display: none !important;"
                            accept=".csv,text/csv,text/comma-separated-values,application/csv"
                            onchange="buscaNotas()"
                        >
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
