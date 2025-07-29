import axios from "axios";
import React, { useState, useEffect } from "react";

export default function SelectTurma(props) {
  const todos = props.todos ?? false;
  const serie = props.serie ?? null;
  const [turmas, setTurmas] = useState([]);

  useEffect(() => {
    if (props.optionsUpdated) {
      props.optionsUpdated()
    }

    if (serie) {
      axios.get(`/api/v1/serie/${serie}/turmas`).then((response) => setTurmas(response.data))
    }
  }, [serie]);

  return (
    <React.StrictMode>
      <select
        id={props.id}
        name={props.name}
        className="form-element"
        value={props.value}
        onChange={props.onChange}
        disabled={!serie}
      >
        <option value="" disabled={!todos}>
          {todos ? 'Todos' : 'Selecione uma opção'}
        </option>

        {turmas.map((opt) => <option key={opt.id} value={opt.id}>{opt.turma}</option>)}
      </select>
    </React.StrictMode>
  )
}
