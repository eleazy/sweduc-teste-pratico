import axios from "axios";
import React, { useState, useEffect } from "react";
import ContextoAcademico from '../Components/ContextoAcademico.jsx';

export default function NotasInconsistentes() {
  const [notas, setNotas] = useState([]);
  const [status, setStatus] = useState('not-started');

  function atualizaListagemNotas() {
    const unidadeId = document.getElementById('unidade').value
    const cursoId = document.getElementById('curso').value
    const serieId = document.getElementById('serie').value

    let constraint = '';
    unidadeId && (constraint = `unidadeId=${unidadeId}`)
    cursoId && (constraint = `cursoId=${cursoId}`)
    serieId && (constraint = `serieId=${serieId}`)

    const notasInconsistentesReq = axios.get('/api/v1/notas-inconsistentes?' + constraint)
    setStatus('loading')
    notasInconsistentesReq
      .then((response) => {
        setNotas(response.data)

        if (response.data.length > 0) {
          setStatus('loaded')
        } else {
          setStatus('loaded-empty')
        }
      })
      .catch(() => {
        setStatus('error')
      })
  }

  // useEffect(() => {
  //   atualizaListagemNotas();
  // }, []);

  /**
   * Atualiza estado do componente removendo nota enviada por parâmetro
   */
  function removeNotaDaLista(nota) {
    if (Array.isArray(nota)) {
      setNotas(notas.filter((n) => !nota.includes(n)))
    } else {
      setNotas(notas.filter((n) => n != nota))
    }
  }

  function apagarNotasDuplicadas() {
    const removerNotas = notas
        .filter((nota) => nota.nota_antiga == nota.nota_atual)

    axios.post('/api/v1/notas-inconsistentes', {
      apagar_id: removerNotas.map((nota) => nota.nota_antiga_id)
    }).then(() => {
      removeNotaDaLista(removerNotas)
    })
  }

  function apagarNota(nota) {
    axios.post('/api/v1/notas-inconsistentes', {
      apagar_id: nota.nota_antiga_id
    }).then(() => {
      removeNotaDaLista(nota)
    })
  }

  function substituirNota(nota) {
    axios.post('/api/v1/notas-inconsistentes', {
      substituir_id: nota.nota_atual_id,
      por_id: nota.nota_antiga_id
    }).then(() => {
      removeNotaDaLista(nota)
    })
  }

  function renderRow(nota, index) {
    return (
      <tr key={index} className="hover:bg-gray-300 text-gray-700 hover:text-black">
        <td className="p-1 text-center">
          {nota.aluno_nome}
        </td>

        <td className="p-1 text-center">
          {nota.ano_letivo}
        </td>

        <td className="p-1 text-center">
          {nota.disciplina}
        </td>

        <td className="p-1 text-center">
          {nota.avaliacao}
        </td>

        <td className="p-1 text-center">
          {nota.periodo}
        </td>

        <td className="p-1 text-center">
          {nota.turma_antiga_nome}
        </td>

        <td className="p-1 text-center">
          {nota.nota_antiga}
        </td>

        <td className="p-1 text-center">
          {nota.turma_atual_nome}
        </td>

        <td className="p-1 text-center">
          {nota.nota_atual}
        </td>

        <td className="text-center">
          <div className="p-1 flex justify-center">
            { (nota.nota_antiga == nota.nota_atual) &&
              <div>
                <button
                  type="button"
                  className="sw-btn sw-btn-primary w-full md:w-auto m-1 whitespace-nowrap"
                  onClick={apagarNota.bind(this, nota)}
                >
                  Excluir antiga
                </button>
              </div>
            }

            { (nota.nota_antiga != nota.nota_atual) &&
              <div>
                <button
                  type="button"
                  className="sw-btn sw-btn-warning w-full md:w-auto m-1 whitespace-nowrap"
                  onClick={apagarNota.bind(this, nota)}
                >
                  Excluir antiga
                </button>
              </div>
            }

            { (nota.nota_antiga != nota.nota_atual) &&
              <div>
                <button
                  type="button"
                  className="sw-btn sw-btn-secondary w-full md:w-auto m-1 whitespace-nowrap"
                  onClick={substituirNota.bind(this, nota)}
                >
                  Substituir nova
                </button>
              </div>
            }
          </div>
        </td>
      </tr>
    )
  }

  function render() {
    return (
      <div>
        <div className="my-3 text-right">
          <button
            type="button"
            className="sw-btn sw-btn-primary"
            onClick={apagarNotasDuplicadas}
          >
            Excluir todas duplicadas
          </button>
        </div>

        <div className="my-2 rounded bg-gray-200 overflow-y-hidden">
          <table className="w-full">
            <thead>
              <tr className="bg-red-300 py-2">
                <th className="p-1 text-center">
                  Aluno
                </th>
                <th className="p-1 text-center">
                  Ano letivo
                </th>
                <th className="p-1 text-center">
                  Disciplina
                </th>
                <th className="p-1 text-center">
                  Avaliacao
                </th>
                <th className="p-1 text-center">
                  Periodo
                </th>
                <th className="p-1 text-center">
                  Turma antiga
                </th>
                <th className="p-1 text-center">
                  Nota antiga
                </th>
                <th className="p-1 text-center">
                  Turma atual
                </th>
                <th className="p-1 text-center">
                  Nota atual
                </th>
                <th className="p-1 text-center">
                  Opções
                </th>
              </tr>
            </thead>
            <tbody>
              { notas.map((nota, index) => renderRow(nota, index)) }
            </tbody>
          </table>
        </div>
      </div>
    )
  }

  function statusMessage() {
    if (notas.length > 0) {
      return null;
    }

    if (status === 'loading') {
      return <p className="text-center font-bold text-lg">Carregando...</p>
    }

    if (notas.length === 0 && status === 'loaded-empty') {
      return <p className="text-center font-bold text-lg">Nenhuma nota identificada</p>
    }

    if (notas.length === 0 && status === 'error') {
      return <p className="text-center font-bold text-lg">Erro na requisição</p>
    }
  }

  const showCarregarMaisBtn = !['not-started', 'loading', 'error', 'loaded-empty'].includes(status)

  return (
    <div className="container mx-auto p-2">
      <h2>Relatório de notas inconsistentes</h2>

      <ContextoAcademico turmas={false} />

      <div className="my-2">
        <button
          type="button"
          className="sw-btn sw-btn-primary"
          onClick={atualizaListagemNotas}
        >
          Buscar
        </button>
      </div>

      {statusMessage()}

      {notas.length > 0 && render()}

      { showCarregarMaisBtn &&
        <div className="my-3 text-center">
          <button
            type="button"
            className="sw-btn sw-btn-primary"
            onClick={atualizaListagemNotas}
          >
            Carregar mais
          </button>
        </div>
      }
    </div>
  );
}
