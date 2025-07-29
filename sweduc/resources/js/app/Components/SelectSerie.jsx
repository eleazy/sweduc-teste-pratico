import axios from "axios";
import React, { useState, useEffect } from "react";

export default function SelectSerie(props) {
  const todos = props.todos ?? false;
  const curso = props.curso ?? null
  const [series, setSeries] = useState([]);

  useEffect(() => {
    if (props.optionsUpdated) {
      props.optionsUpdated()
    }

    if (curso) {
      axios.get(`/api/v1/curso/${curso}/series`).then((response) => setSeries(response.data))
    }
  }, [curso]);

  return (
    <React.StrictMode>
      <select
        id={props.id}
        name={props.name}
        className="form-element"
        value={props.value}
        onChange={props.onChange}
        disabled={!curso}
      >
        <option value="" disabled={!todos}>
          {todos ? 'Todos' : 'Selecione uma opção'}
        </option>

        {series.map((opt) => <option key={opt.id} value={opt.id}>{opt.serie}</option>)}
      </select>
    </React.StrictMode>
  )
}
