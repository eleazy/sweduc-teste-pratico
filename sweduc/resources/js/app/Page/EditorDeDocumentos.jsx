import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import Editor from "@monaco-editor/react";
import axios from "axios";

export default function EditorDeDocumentos() {
  let { id } = useParams()
  const [documento, setDocumento] = useState({
    nomedoc: '',
    contexto: 6,
    colunas: 0,
    linhas: 0,
    margemsuperior: 0,
    margemdireita: 0,
    margeminferior: 0,
    margemesquerda: 0,
  });

  useEffect(() => {
    if (!id) {
      return
    }

    axios.get(`/api/v1/documento/${id}`)
      .then(request => {
        request.data.documento && setDocumento(Object.assign({}, documento, request.data.documento))
      })
  }, [])

  function salvarDocumento () {
    axios.post(`/api/v1/documento/${id}`, documento)
      .then(() => {
        alert('Documento salvo')
      })
  }

  function handleKeyDown (e) {
    const ctrlOuCmdAtiva = e.ctrlKey || e.metaKey;
    const teclaS = String.fromCharCode(e.which).toLowerCase() == 's';
    const atalhoSalvar = ctrlOuCmdAtiva && teclaS

    if (!atalhoSalvar) {
      return true;
    }

    e.preventDefault();
    confirm('Deseja salvar o documento?') && salvarDocumento();
    return false;
  }

  return (
    <div className="container mx-auto p-2" onKeyDown={handleKeyDown}>
      <div className="flex flex-wrap items-center justify-center py-4">
        <h3 className="mr-auto m-0 whitespace-nowrap p-1 w-full md:w-auto text-center">
          Configurações {'>'} Novo documento
        </h3>

        <div className="p-1">
          <button
            type="button"
            className="sw-btn sw-btn-primary whitespace-nowrap"
            onClick={() => sweduc.carregarUrl("/config/editor-de-documentos/")}
          >
            Listar Documentos
          </button>
        </div>

        <div className="p-1">
          <button type="button" className="sw-btn sw-btn-secondary whitespace-nowrap">
            Criar Novo Documento
          </button>
        </div>
      </div>

      <div className="rounded p-3 border">
        <div className="flex flex-wrap -m-2 justify-between">
          <div className="p-2 w-full md:w-auto">
            <label htmlFor="nome">Nome do documento</label>
            <input
              id="nome"
              type="text"
              name="nome"
              className="form-element"
              value={documento?.nomedoc}
              onChange={event => setDocumento({...documento, nomedoc: event.target.value})}
            />
          </div>

          <div className="p-2 w-full md:w-auto">
            <label htmlFor="contexto">Contexto do documento</label>
            <select
              id="contexto"
              name="contexto"
              className="form-element"
              value={documento?.contexto}
              onChange={event => setDocumento({...documento, contexto: event.target.value})}
            >
              <option value="0">
                Alunos/Acadêmico (v1)
              </option>
              <option value="6">
                Alunos/Acadêmico (v2)
              </option>
              <option value="1">
                Turmas/Acadêmico (v1)
              </option>
              <option value="2">
                Financeiro (v1)
              </option>
              <option value="3">
                Empresa (v1)
              </option>
              <option value="4">
                Responsáveis (v1)
              </option>
              <option value="5">
                Rematrícula (v1)
              </option>
            </select>
          </div>

          <div className="w-full p-2">
            <button
              type="button"
              className="sw-btn sw-btn-primary"
              onClick={salvarDocumento}
            >
              <i className="fa fa-check mr-1"></i>
              Salvar
            </button>
          </div>
        </div>
      </div>

      <div className="border rounded overflow-hidden my-3">
        <Editor
          height="90vh"
          defaultLanguage="liquid"
          value={documento?.template}
          onChange={value => setDocumento({...documento, template: value})}
          theme="vs-dark"
        />
      </div>
    </div>
  );
}
