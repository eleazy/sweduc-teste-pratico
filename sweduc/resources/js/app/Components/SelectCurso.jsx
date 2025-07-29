import axios from "axios";
import React, { useState, useEffect } from "react";

export default function SelectCurso(props) {
  const todos = props.todos ?? false;
  const unidade = props.unidade ?? null
  const [cursos, setCursos] = useState([]);

  useEffect(() => {
    if (props.optionsUpdated) {
      props.optionsUpdated()
    }

    if (unidade) {
      axios.get(`/api/v1/unidade/${unidade}/cursos`).then((response) => setCursos(response.data))
    }
  }, [unidade]);

  return (
    <React.StrictMode>
      <select
        id={props.id}
        name={props.name}
        className="form-element"
        value={props.value}
        onChange={props.onChange}
        disabled={!unidade}
      >
        <option value="" disabled={!todos}>
          {todos ? 'Todos' : 'Selecione uma opção'}
        </option>

        {cursos.map((opt) => <option key={opt.id} value={opt.id}>{opt.curso}</option>)}
      </select>
    </React.StrictMode>
  )
}
