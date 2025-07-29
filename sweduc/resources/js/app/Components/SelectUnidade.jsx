import axios from "axios";
import React, { useState, useEffect } from "react";

export default function SelectUnidade(props) {
  const todasUnidades = props.todasUnidades ?? false;
  const disabled = props.disabled ?? false
  const [unidades, setUnidades] = useState([]);

  useEffect(() => {
    if (props.optionsUpdated) {
      props.optionsUpdated()
    }

    axios.get('/api/v1/unidade').then((response) => {
      setUnidades(response.data)
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
        disabled={disabled}
      >
        <option value="" disabled={!todasUnidades}>
          {todasUnidades ? 'Todos' : 'Selecione uma opção'}
        </option>

        {unidades.map((opt) => <option key={opt.id} value={opt.id}>{opt.unidade}</option>)}
      </select>
    </React.StrictMode>
  )
}
