import { Dialog, Transition } from '@headlessui/react'
import axios from 'axios'
import React, { Fragment, useEffect, useState } from 'react'

function DocumentosGrid({rematriculaId, documentos}) {
  function handleStatusUpdate(id, status) {
    return axios.patch(`/api/v1/academico/rematriculas/${rematriculaId}/documentos-enviados/${id}/${status}`)
  }

  function status(documento) {
    if (documento.aprovado_em) {
      return 'aprovado'
    }

    if (documento.rejeitado_em) {
      return 'rejeitado'
    }

    return 'pendente'
  }

  if (documentos === null) {
    return (
      <p className="text-sm text-gray-500">
        Carregando...
      </p>
    )
  }

  if (documentos.length === 0) {
    return (
      <p className="text-sm text-gray-500">
        Não há nada aqui
      </p>
    )
  }

  return (
    <>
      <table className="w-full">
        <thead>
          <tr className="text-gray-800 uppercase font-light text-sm">
            <th>Documento</th>
            <th>Situação</th>
          </tr>
        </thead>

        <tbody>
          {documentos.map(documento => (
            <tr key={documento.id}>
              <td>
                <a href={`/rematriculas-documentos-enviados/${documento.file_id}`} target="_blank">
                  {documento.documento}
                </a>
              </td>

              <td className="py-1">
                <select className="form-element text-sm" defaultValue={status(documento)} onChange={(event) => handleStatusUpdate(documento.id, event.target.value)}>
                  <option value="pendente">Pendente</option>
                  <option value="aprovado">Aprovado</option>
                  <option value="rejeitado">Rejeitado</option>
                </select>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </>
  )
}

export default function DocumentModal({rematriculaId, closeFn}) {
  const [documentos, setDocumentos] = useState(null)

  useEffect(() => {
    if (!rematriculaId) {
      setDocumentos(null)
      return
    }

    axios.get(`/api/v1/academico/rematriculas/${rematriculaId}/documentos-enviados`)
      .then(response => setDocumentos(response.data))
  }, [rematriculaId])

  function aprovarDoc() {
    return axios.patch(`/api/v1/academico/rematriculas/${rematriculaId}/aprova-documentos`)
  }

  function zerarDoc() {
    return axios.patch(`/api/v1/academico/rematriculas/${rematriculaId}/rejeita-documentos`)
  }

  return (
    <Transition appear show={!!rematriculaId} as={Fragment}>
      <Dialog
        as="div"
        className="fixed inset-0 z-50 overflow-y-auto"
        onClose={closeFn}
      >
        <div className="min-h-screen px-4 text-center bg-black bg-opacity-80">
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-300"
            enterFrom="opacity-0"
            enterTo="opacity-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <Dialog.Overlay className="fixed inset-0" />
          </Transition.Child>

          {/* This element is to trick the browser into centering the modal contents. */}
          <span
            className="inline-block h-screen align-middle"
            aria-hidden="true"
          >
            &#8203;
          </span>
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-300"
            enterFrom="opacity-0 scale-95"
            enterTo="opacity-100 scale-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100 scale-100"
            leaveTo="opacity-0 scale-95"
          >
            <div className="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
              <Dialog.Title
                as="h3"
                className="mt-0 text-lg font-medium leading-6 text-gray-900"
              >
                Aprovação de documentos de rematrícula
              </Dialog.Title>

              <div className="mt-6">
                <DocumentosGrid rematriculaId={rematriculaId} documentos={documentos} />
              </div>

              <div className="mt-4 space-x-2 space-y-2">
                <button
                  type="button"
                  className="sw-btn sw-btn-warning text-sm"
                  onClick={() => zerarDoc().then(closeFn)}
                >
                  Existe pendencia
                </button>

                <button
                  type="button"
                  className="sw-btn sw-btn-primary text-sm"
                  onClick={() => aprovarDoc().then(closeFn)}
                >
                  Todos aprovados
                </button>
              </div>
            </div>
          </Transition.Child>
        </div>
      </Dialog>
    </Transition>
  )
}
