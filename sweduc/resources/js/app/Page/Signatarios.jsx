import React, { useEffect, useState } from "react";
import {
  HashRouter as Router, Link, Route, Switch
} from "react-router-dom";
import axios from "axios";

const PlusIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
  </svg>
)

function Lista() {
  const [signatarios, setSignatarios] = useState([])

  useEffect(() => {
    axios.get('/api/v1/config/signatarios')
      .then(response => setSignatarios(response.data))
  }, [])

  return (
    <div>
      <table className="w-full table table-bordered">
        <thead>
          <th>Nome</th>
          <th>E-mail</th>
          <th>CPF</th>
          <th>Data de nascimento</th>
        </thead>

        <tbody>
          {signatarios.map(s => (
            <tr key={s.id}>
              <td>{s.nome_completo}</td>
              <td>{s.email}</td>
              <td>{s.cpf}</td>
              <td>{s.nascimento}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

function Criar() {
  function handleSubmit(event) {
    event.preventDefault()

    const form = new FormData(event.target)
    axios.post('/api/v1/config/signatarios', form)
      .then(() => history.back())
  }

  return (
    <form className="flex flex-wrap -m-2" onSubmit={handleSubmit}>
      <label className="block w-full sm:w-3/4 p-2">
        <span>Nome completo</span>
        <input type="text" name="nome_completo" className="form-element mt-1" />
      </label>

      <label className="block w-full sm:w-1/4 p-2">
        <span>Data de nascimento</span>
        <input type="date" name="nascimento" className="form-element mt-1" />
      </label>

      <label className="block w-full sm:w-3/4 p-2">
        <span>E-mail</span>
        <input type="email" name="email" className="form-element mt-1" />
      </label>

      <label className="block w-full sm:w-1/4 p-2">
        <span>CPF</span>
        <input type="text" name="cpf" className="form-element mt-1" />
      </label>

      <div className="w-full p-2">
        <button className="sw-btn sw-btn-primary">
          Salvar
        </button>
      </div>
    </form>
  )
}

export default function Rematricula() {
  return (
    <div className="container mx-auto p-5 md:w-2/3">
      <div className="flex flex-wrap items-center py-3 justify-center -m-2">
        <h3 className="m-0 p-2">
          Signatários
        </h3>

        <div className="w-full sm:w-auto sm:ml-auto p-2">
          <Link to="/signatarios/novo">
            <button className="inline-flex items-center sw-btn sw-btn-primary w-full justify-center">
              <PlusIcon />
              <span className="ml-1">Adicionar</span>
            </button>
          </Link>
        </div>
      </div>

      <Router>
        <Switch>
          <Route exact path="/signatarios/">
            <Lista />
          </Route>

          <Route path="/signatarios/novo">
            <Criar />
          </Route>

          <Route path="/signatarios/:id">
            <p>
              ID
            </p>
          </Route>
        </Switch>
      </Router>
    </div>
  );
}
