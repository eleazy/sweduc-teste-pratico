import React, { useEffect, useState } from "react";
import axios from "axios";

const EditIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
  </svg>
)

const CheckIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
  </svg>
)

const CrossIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
  </svg>
)

const TrashIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
  </svg>
)

function Header({handleCreate}) {
  return (
    <div className="flex my-4">
      <h2 className="p-0 m-0">
        Documentos de rematrícula
      </h2>

      <div className="ml-auto">
        <button
          type="button"
          className="sw-btn sw-btn-primary"
          onClick={() => handleCreate()}
        >
          Adicionar
        </button>
      </div>
    </div>
  )
}

function Row(props) {
  const {id, nome, obrigatorio} = props.dados
  const {handleUpdate, handleDelete} = props.ops
  const [editable, setEditable] = useState(props.dados.editable ?? false)
  const [novoNome, setNovoNome] = useState(nome)

  return (
    <tr key={id} className="text-gray-800 hover:bg-gray-200 rounded">
      <td className="px-3 py-1 w-full">
        { !editable
          ? <span>{nome}</span>
          : <input type="text" value={novoNome} onChange={(e) => setNovoNome(e.target.value)} className="form-element" />
        }
      </td>

      <td className="px-3 py-1 text-center">
        <input
          type="checkbox"
          className="ml-auto mr-4"
          checked={obrigatorio}
          onChange={() => handleUpdate({id, nome, obrigatorio: !obrigatorio})}
        />
      </td>

      <td className="px-3 py-1 text-right">
        { !editable ? (
          <div className="flex">
            <button
              className="sw-btn sw-btn-secondary align-middle transform scale-75"
              onClick={() => setEditable(!editable)}
            >
              <EditIcon />
            </button>

            <button
              className="sw-btn sw-btn-danger align-middle transform scale-75"
              onClick={() => confirm(`Deseja deletar o ${nome}`) && handleDelete(id)}
            >
              <TrashIcon />
            </button>
          </div>
        ) : (
          <div className="flex">
            <button
              className="sw-btn sw-btn-primary align-middle transform scale-75"
              onClick={() => {
                handleUpdate({id, nome: novoNome, obrigatorio})
                setEditable(!editable)
              }}
            >
              <CheckIcon />
            </button>

            <button
              className="sw-btn sw-btn-danger align-middle transform scale-75"
              onClick={() => {
                setNovoNome(nome)
                setEditable(!editable)
              }}
            >
              <CrossIcon />
            </button>
          </div>
        )}
      </td>
    </tr>
  )
}

function Lista(props) {
  return (
    <div className="bg-gray-300 rounded overflow-hidden">
      <table className="w-full">
        <thead>
          <tr className="text-gray-700 text-xs">
            <th className="p-3">Documento</th>
            <th className="p-3 text-center">Obrigatoriedade</th>
            <th className="p-3 text-center">Editar</th>
          </tr>
        </thead>

        <tbody>
          {props.dados.map(
            (dados) => <Row key={dados.id} dados={dados} ops={props.operations} />
          )}
        </tbody>
      </table>
    </div>
  )
}

export default function Rematricula() {
  const [data, setData] = useState([])

  useEffect(() => {
    axios.get('/api/v1/academico/documentos-rematricula')
    .then(response => {
        const data = response.data.map(({id, documento, obrigatoriedade}) => ({
          id,
          nome: documento,
          obrigatorio: obrigatoriedade,
        }))
        setData(data)
      })
  }, [])


  function handleCreate() {
    axios.post(`/api/v1/academico/documentos-rematricula`, {
      documento: 'Documento sem título',
      obrigatoriedade: false,
    }).then((response) => {
      const newData = data.slice()
      newData.push({
        id: response.data.id,
        nome: response.data.documento,
        obrigatorio: !!response.data.obrigatoriedade,
        editable: true,
      })
      setData(newData)
    })
  }

  function handleUpdate({id, nome, obrigatorio}) {
    // Atualização no server
    axios.put(`/api/v1/academico/documentos-rematricula/${id}`, {
      documento: nome,
      obrigatoriedade: obrigatorio
    }).then(() => {
      // Clonagem da lista de objetos inicial
      const newData = data.slice()

      // Atualização in-place
      Object.assign(newData.find((data) => data.id === id), {id, nome, obrigatorio})

      // Atualização da lista de objetos
      setData(newData)
    })
  }

  function handleDelete(id) {
    axios.delete(`/api/v1/academico/documentos-rematricula/${id}`).then(
      () => setData(data.filter((d) => d.id !== id))
    )
  }

  return (
    <div className="container mx-auto p-5">
      <Header handleCreate={handleCreate} />
      <Lista dados={data} operations={{handleUpdate, handleDelete}} />
    </div>
  );
}
