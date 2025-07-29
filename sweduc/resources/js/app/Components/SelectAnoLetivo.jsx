import axios from "axios";
import React, { useState, useEffect } from "react";

export default function SelectAnoLetivo(props) {
  const todos = props.todasUnidades ?? false;
  const [anos, setAnos] = useState([]);

  useEffect(() => {
    if (props.optionsUpdated) {
      props.optionsUpdated()
    }

    axios.get('/api/v1/academico/anos-letivos').then((response) => {
      setAnos(response.data)
    })
  }, []);

  return (
    <React.StrictMode>
      <select
        id={props.id}
        name={props.name}
        className="form-element"
        value={props.value}
        onChange={props.onChange}
      >
        <option value="" disabled={!todos}>
          {todos ? 'Todos' : 'Selecione uma opção'}
        </option>

        {anos.map((opt) => <option key={opt.id} value={opt.id}>{opt.anoletivo}</option>)}
      </select>
    </React.StrictMode>
  )
}
