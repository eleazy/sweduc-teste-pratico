import { Menu, Transition } from '@headlessui/react';
import { ChevronDownIcon, DotsHorizontalIcon } from '@heroicons/react/solid';
import axios from "axios";
import React, { Fragment, useEffect, useState } from 'react';
import SelectAnoLetivo from "../Components/SelectAnoLetivo.jsx";
import SelectUnidade from "../Components/SelectUnidade.jsx";
import SelectTurma from "../Components/SelectTurma.jsx";
import SelectCurso from "../Components/SelectCurso.jsx";
import SelectSerie from "../Components/SelectSerie.jsx";
import ContextoAcademico from "../Components/ContextoAcademico.jsx";
import DocumentModal from '../Components/DocumentModal.jsx';
import RematriculaModal from '../Components/RematriculaModal.tsx';
import router from '../../router';
import Swal from 'sweetalert2';

function situacao(situacao) {
  const situacoes = {
    'titulo-pago': 'Aguardando pagamento',
    'dados-cadastrais': 'Título pago',
    'ficha-anamnese': 'Atualização de dados',
    'documentos-enviados': 'Ficha de anamnese',
    'assinatura-do-contrato': 'Documentos de rematrícula',
    'rematricula-finalizada': 'Assinatura de contrato',
  }
  return situacoes[situacao] ?? 'Situação desconhecida'
}

function date(val) {
  if (!val) {
    return ''
  }

  const date = new Date(val)
  return date.toLocaleDateString()
}

function reiniciarPreenchimento(rematriculaId, recarregarLista) {
  if (confirm("Você deseja retroceder a rematrícula para a etapa de atualização de cadastro, cancelando eventuais documentos assinados ou em andamento?")) {
    axios.patch(`/api/v1/academico/rematriculas/${rematriculaId}/restart`)
    recarregarLista()
  }
}

function Opcoes({rematricula, recarregarLista, visualizarDocumento}) {
  const activeBtnClass = (active) => `${active ? 'bg-blue-500 text-white' : 'text-gray-700'}
    group flex rounded-md items-center w-full px-2 py-2`

  const contemPermissao = (permissao) => window.sweduc_permissions.indexOf(permissao) > -1

  return (
    <Menu as="div" className="relative inline-block text-left">
      <div>
        <Menu.Button className="inline-flex justify-center w-full px-4 py-2 text-white bg-black rounded-full bg-opacity-20 hover:bg-opacity-30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
          <DotsHorizontalIcon
            className="w-5 h-5"
          />

          <ChevronDownIcon
            className="w-5 h-5 ml-2 -mr-1"
            aria-hidden="true"
          />
        </Menu.Button>
      </div>

      <Transition
        as={Fragment}
        enter="transition ease-out duration-100"
        enterFrom="transform opacity-0 scale-95"
        enterTo="transform opacity-100 scale-100"
        leave="transition ease-in duration-75"
        leaveFrom="transform opacity-100 scale-100"
        leaveTo="transform opacity-0 scale-95"
      >
        <Menu.Items className="z-10 absolute right-0 w-56 mt-2 origin-top-right bg-white divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
          <div className="px-1 py-1">
            {contemPermissao('financeiro-controle-rematricula-autorizar-documentos') && <Menu.Item>
              {({ active }) => (
                <button
                  className={activeBtnClass(active)}
                  onClick={() => visualizarDocumento(rematricula.id)}>
                  Visualizar documentos
                </button>
              )}
            </Menu.Item>}

            {rematricula.contrato_id && (
              <Menu.Item>
                {({ active }) => (
                  <a
                    href={"/visualizar-contrato/" + rematricula.contrato_id}
                    target="_blank"
                    className={ `${activeBtnClass(active)} hover:no-underline hover:text-white` }
                  >
                    Contrato assinado
                  </a>
                )}
              </Menu.Item>
            )}

            {contemPermissao('financeiro-controle-rematricula-reiniciar-processo') && <Menu.Item>
              {({ active }) => (
                <button
                  className={activeBtnClass(active)}
                  onClick={() => reiniciarPreenchimento(rematricula.id, recarregarLista)}>
                  Reiniciar processo
                </button>
              )}
            </Menu.Item>}
          </div>
        </Menu.Items>
      </Transition>
    </Menu>
  )
}

function Pagination({page, setPage, maxItens, setMaxItens, totalPages, totalItens}) {
  return (
    <div className="py-3 flex items-center">
      <div className="space-x-2">
        <button
          type="button"
          onClick={() => setPage(page - 1)}
          disabled={page === 1}
          className="sw-btn-circle sw-btn-white"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <button
          type="button"
          onClick={() => setPage(page + 1)}
          className="sw-btn-circle sw-btn-white"
          disabled={totalPages ? totalPages === page : false}
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <div className="ml-auto text-sm text-gray-700 px-2">
        Exibindo {page} de {totalPages} páginas ({totalItens} itens)
      </div>

      <div>
        <select
          className="form-element"
          value={maxItens}
          onChange={(e) => setMaxItens(e.target.value)}
        >
          <option>25</option>
          <option>50</option>
          <option>75</option>
          <option>100</option>
        </select>
      </div>
    </div>
  )
}

function Table({alunos, search, setDocumentFromRematricula}) {
  const header = [
    'Aluno',
    'Seguro Escolar',
    'Academico',
    'Etapa',
    // 'Docs. pendentes de aprovavação',
    'Nova Matrícula',
    'Pagamento do título',
    'Atualização de dados',
    'Atualização de anamnese',
    'Contrato assinado',
    'Documentação aprovada',
    'Opções',
  ]

  const tituloAluno = (rematricula) => (
    <>
      <div className="text-gray-900">{rematricula.aluno_nome}</div>
      <div className="text-xs">{rematricula.aluno_num}</div>
    </>
  )

  const seguroEscolar = (rematricula) => (rematricula['seguro_escolar'])
    ? (
      <span className="bg-green-200 text-gray-800 px-3 py-2 rounded-full">
        Sim
      </span>
    ) : (
      <span className="bg-red-200 text-gray-800 px-3 py-2 rounded-full">
        Não
      </span>
    )

  const contextoAcademico = (rematricula) => (
    <>
      <div className="text-gray-900">{rematricula.matricula_unidade}</div>
      <div className="text-xs">{rematricula.matricula_curso}</div>
      <div className="text-xs">{rematricula.matricula_serie}</div>
    </>
  )

  const matriculaLink = (id: string | number, numeroMatricula: string | number, problemas: { requerimentos: string[]; avisos: string[]; }) => {
    if (id) {
      return (
        <a
          href="#"
          onClick={() => router.carregarUrl(`alunos_cadastra.php?matriculaId=${id}`)}
        >
          {numeroMatricula}
        </a>
      )
    }

    const x = problemas.requerimentos.map(p => (
      <span key={p} className='bg-red-300 whitespace-nowrap inline-block my-1'>
        {p}
      </span>
    ));

    const y = problemas.avisos.map(p => (
      <span key={p} className='bg-yellow-200 whitespace-nowrap inline-block my-1'>
        {p}
      </span>
    ));

    return [...x, ...y];
  }

  const opcoes = (rematricula) => (
    <Opcoes
      rematricula={rematricula}
      recarregarLista={search}
      visualizarDocumento={setDocumentFromRematricula}
    />
  )

  const content = (rematricula: Rematricula) => [
    tituloAluno(rematricula),
    seguroEscolar(rematricula),
    contextoAcademico(rematricula),
    situacao(rematricula['etapa_atual']),
    // rematricula.aprovar_docs_qtd,
    matriculaLink(
        rematricula.nova_matricula?.id,
        rematricula.nova_matricula?.nummatricula,
        rematricula.problemas_rematricula
    ),
    date(rematricula.titulo_pago_em),
    date(rematricula.dados_atualizados_em),
    date(rematricula.anamnese_atualizada_em),
    date(rematricula.assinatura_digital_em),
    date(rematricula.documentacao_aprovada_em),
    opcoes(rematricula),
  ]

  const highlight = (rematricula) => !(
    rematricula.documentacao_aprovada_em &&
    rematricula.assinatura_digital_em
  )

  const highlightReprovado = (rematricula) => rematricula.documentacao_reprovada_em

  function selectAllAlunos (event) {
    document
      .querySelectorAll('.selected-alunos:enabled')
      .forEach((item) => item.checked = event.target.checked)
  }

  return (
    <div className="shadow overflow-auto border-b border-gray-100 sm:rounded">
      <table className="min-w-full divide-y divide-gray-200">
        <thead className="bg-gray-50">
          <tr>
            <th
              className='py-4 px-3 font-medium text-sm uppercase tracking-wider text-gray-700'
              onClick={selectAllAlunos}
            >
              <input type="checkbox" />
            </th>

            {header.map(title => (
              <th key={title} className="py-4 px-3 font-medium text-xs uppercase tracking-wider text-gray-700">
                {title}
              </th>
            ))}
          </tr>
        </thead>

        <tbody className="bg-white divide-y divide-gray-200">
          {alunos.map(rematricula => (
            <tr key={rematricula.id} className={(highlight(rematricula) ? '' : 'bg-green-100') + (highlightReprovado(rematricula) ? 'bg-yellow-100' : '')}>
              <td className='py-3 px-3 text-sm text-gray-700 text-center accent-blue-500'>
                <input
                  type="checkbox"
                  disabled={rematricula.problemas_rematricula.requerimentos.length}
                  data-id={rematricula.id}
                  className='selected-alunos'
                />
              </td>

              {content(rematricula).map((content, id) => (
                <td key={`${rematricula.id}-${id}`} className="py-3 px-3 text-sm text-gray-700">
                  {content}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

function rematricula() {
  const items = [];
  document
      .querySelectorAll('.selected-alunos')
      .forEach((item) => item.checked && items.push(item.dataset.id));
  //const joinedItems = items.join();
  return items;
}

export default function Rematricula() {
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(null)
  const [totalItens, setTotalItens] = useState(0)
  const [maxItens, setMaxItens] = useState(25)
  const [alunos, setAlunos] = useState([])
  const [form, setForm] = useState([])
  const [downloadLink, setDownloadLink] = useState('')
  const [documentFromRematricula, setDocumentFromRematricula] = useState(null)
  const [anoletivo, setAnoletivo] = useState('');
  const [unidade, setUnidade] = useState('');
  const [curso, setCurso] = useState('');
  const [serie, setSerie] = useState('');
  const [turma, setTurma] = useState('');
  const [showRematriculaModal, setShowRematriculaModal] = useState<boolean>(false);
  const [etapa, setEtapa] = useState('');

  const situacoes = {
    'titulo-nao-pago': 'Aguardando pagamento',
    'titulo-pago': 'Título pago',
    'atualizacao-dados': 'Atualização de dados',
    'atualizacao-anamnese': 'Atualização de anamnese',
    'contrato-assinado': 'Contrato assinado',
    'contrato-nao-assinado': 'Contrato não assinado',
    'documentacao-aprovada': 'Documentação aprovada',
    'documentacao-pendente': 'Documentação pendente',
    'disponivel-rematricula': 'Disponível para rematrícula',
  }

  function handleFormUpdate(event) {
    event.preventDefault()
    setPage(1)
    setForm(new FormData(event.target))
  }

  function search() {
    setDownloadLink('/api/v1/academico/rematriculas.xls?' +
      Array.from(form.entries())
      .filter(x => "" !== x[1])
      .map(x => `${encodeURIComponent(x[0])}=${encodeURIComponent(x[1])}`)
      .join('&')
    )

    const data = [
      ...form.entries(),
      ['page', page],
      ['perPage', maxItens]
    ];

    const query = data
        .filter(x => "" !== x[1])
        .map(x => `${encodeURIComponent(x[0])}=${encodeURIComponent(x[1])}`)
        .join('&');

    axios.get(`/api/v1/academico/rematriculas?${query}`)
      .then(response => {
        setAlunos(response.data.data)
        setTotalItens(response.data.totalItens)
        setTotalPages(response.data.totalPages)
      })
  }

  useEffect(search, [page, maxItens, form])

  return (
    <div className="bg-gray-200 border-t-2 border-white">
      <div className="container mx-auto p-5">
        <h3 className="text-lg p-0 m-0">
          Financeiro &gt; Rematrícula
        </h3>

        <form onSubmit={handleFormUpdate} className="bg-white p-4 my-2 shadow rounded">
          <div className="flex flex-auto -m-2">
            <div className='w-full p-2'>
              <label htmlFor="ano-letivo">
                Ano Letivo
              </label>

              <SelectAnoLetivo
                id="ano-letivo"
                name="ano-letivo"
                value={anoletivo}
                onChange={(e) => setAnoletivo(e.target.value)}
              />
            </div>

            <div className="w-full p-2">
              <label htmlFor="unidade">
                Unidade
              </label>

              <SelectUnidade
                id="unidade"
                name="unidade"
                value={unidade}
                onChange={(e) => setUnidade(e.target.value)}
                todasUnidades={true}
                disabled={!anoletivo}
              />
            </div>

            <div className="w-full p-2">
              <label htmlFor="curso">
                Curso
              </label>

              <SelectCurso
                id="curso"
                name="curso"
                value={curso}
                onChange={(e) => setCurso(e.target.value)}
                optionsUpdated={() => setCurso('')}
                unidade={unidade}
                todos={true}
              />
            </div>

            <div className="w-full p-2">
              <label htmlFor="serie">
                Série
              </label>

              <SelectSerie
                id="serie"
                name="serie"
                value={serie}
                onChange={(e) => setSerie(e.target.value)}
                optionsUpdated={() => setSerie('')}
                curso={curso}
                todos={true}
              />
            </div>

            {/* <div className="w-auto p-2">
              <label htmlFor="turma">
                Turma atual
              </label>

              <SelectTurma
                id="turma"
                value={turma}
                onChange={(e) => setTurma(e.target.value)}
                optionsUpdated={() => setTurma('')}
                serie={serie}
                todos={true}
              />
            </div> */}
          </div>

          <div className="flex flex-wrap py-2 -m-2">
            <div className="p-2 w-1/2 md:w-1/4">
              <label htmlFor="aluno_nome">
                Aluno
              </label>

              <input
                id="aluno_nome"
                name="aluno_nome"
                type="text"
                className="form-input form-element"
              />
            </div>

            <div className="p-2 w-1/2 md:w-1/4">
              <label htmlFor="etapa_atual">
                Etapa
              </label>

              <select
                id="etapa_atual"
                name="etapa_atual"
                className="form-select form-element"
                value={etapa}
                onChange={(e) => setEtapa(e.target.value)}
            >
                <option value="">Todas</option>

                {Object.entries(situacoes).map(x => (
                  <option key={x[0]} value={x[0]}>{x[1]}</option>
                ))}
              </select>
            </div>

            <div className="p-2 w-1/2 md:w-1/4">
              <label htmlFor="periodo_de">
                De
              </label>

              <input
                id="periodo_de"
                name="periodo_de"
                type="date"
                className="form-element"
                />
            </div>

            <div className="p-2 w-1/2 md:w-1/4">
              <label htmlFor="periodo_ate">
                Até
              </label>

              <input
                id="periodo_ate"
                name="periodo_ate"
                type="date"
                className="form-element"
                />
            </div>
          </div>

          <div className="pt-2 flex items-center">
            <button
              type="submit"
              className="sw-btn sw-btn-primary flex items-center"
            >
              <span className="inline-flex mr-1">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              Buscar
            </button>

            <div>
              <select name="ordenacao" className="form-element mx-2">
                <option value="">Ordenação desativada</option>
                <option value="aluno-asc">Ordenar por nome do aluno</option>
                <option value="last-updated">Ordenar por data de modificação</option>
              </select>
            </div>

            <a
              href={downloadLink}
              download
              target="_blank"
              className="ml-auto font-bold text-blue-400 hover:text-blue-500 focus:no-underline hover:no-underline flex items-center"
            >
              <span className="inline-flex mr-1">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </span>

              Salvar como planilha
            </a>
          </div>
        </form>

        <div className='text-right py-3'>
          <button
            className='sw-btn sw-btn-primary ml-auto'
            onClick={() => setShowRematriculaModal(() => {
                if (rematricula().length == 0) {
                    Swal.fire({
                        title: 'Ops!',
                        text: 'Nenhum aluno selecionado para rematrícula.',
                        icon: 'warning',
                        confirmButtonText: 'Ok',
                    });
                    return false;
                }
                return true;
            })}
            disabled={curso === '' || etapa !== 'disponivel-rematricula'}
            {...(curso !== '' ? {} : {
                'data-bs-toggle': "tooltip",
                'data-bs-placement': "top",
                'title': "Necessário selecionar curso",
            })}
          >
            Rematricula
          </button>
        </div>

        <Pagination
          page={page}
          setPage={setPage}
          maxItens={maxItens}
          setMaxItens={setMaxItens}
          totalPages={totalPages}
          totalItens={totalItens}
        />

        <Table
          alunos={alunos}
          search={search}
          setDocumentFromRematricula={setDocumentFromRematricula}
        />

        <Pagination
          page={page}
          setPage={setPage}
          maxItens={maxItens}
          setMaxItens={setMaxItens}
          totalPages={totalPages}
          totalItens={totalItens}
        />
      </div>

      <DocumentModal
        rematriculaId={documentFromRematricula}
        closeFn={() => {setDocumentFromRematricula(null);search()}}
      />

      {showRematriculaModal &&
        <RematriculaModal
            unidade={unidade}
            alunos={rematricula()}
            show={showRematriculaModal}
            closeFn={() => setShowRematriculaModal(false)}
        />
      }
    </div>
  );
}
