import axios from "axios";
import React, { useState, useEffect } from "react";

export default function (props) {
  const selectCurso = props.cursos !== false;
  const selectSerie = selectCurso && props.series !== false;
  const selectTurma = selectSerie && props.turmas !== false;
  const qualquerUnidade = props.qualquerUnidade ?? false;

  const [unidades, setUnidades] = useState([]);
  const [cursos, setCursos] = useState([]);
  const [series, setSeries] = useState([]);
  const [turmas, setTurmas] = useState([]);

  const [unidadeSelecionado, setUnidadeSelecionado] = useState([]);
  const [cursoSelecionado, setCursoSelecionado] = useState([]);
  const [serieSelecionado, setSerieSelecionado] = useState([]);

  useEffect(() => {
    axios.get('/api/v1/unidade').then((response) => {
      setUnidades(response.data)
    })
  }, []);

  const handleUnidadeChange = () => {
    const unidadeId = document.getElementById('unidade').value
    setUnidadeSelecionado(unidadeId)
    setCursoSelecionado([])
    setSerieSelecionado([])
    setCursos([])
    setSeries([])
    setTurmas([])

    if (unidadeId) {
      axios.get(`/api/v1/unidade/${unidadeId}/cursos`).then((response) => setCursos(response.data))
    }
  }

  const handleCursoChange = () => {
    const cursoId = document.getElementById('curso').value
    setCursoSelecionado(cursoId)
    setSerieSelecionado([])
    setSeries([])
    setTurmas([])

    if (cursoId) {
      axios.get(`/api/v1/curso/${cursoId}/series`).then((response) => setSeries(response.data))
    }
  }

  const handleSerieChange = () => {
    const serieId = document.getElementById('serie').value
    setSerieSelecionado(serieId)
    setTurmas([])

    if (serieId) {
      axios.get(`/api/v1/serie/${serieId}/turmas`).then((response) => setTurmas(response.data))
    }
  }

  return (
    <div>
      <div className="flex -m-2">
        <div className="p-2 w-full">
          <label htmlFor="unidade">Unidade</label>
          <select
            name="unidade"
            id="unidade"
            className="form-element"
            defaultValue=""
            onChange={handleUnidadeChange}
          >
            <option value="" disabled={!qualquerUnidade}>
              {qualquerUnidade ? 'Todos' : 'Selecione uma opção'}
            </option>

            {unidades.map((opt) => <option key={opt.id} value={opt.id}>{opt.unidade}</option>)}
          </select>
        </div>

        { selectCurso &&
          <div className="p-2 w-full">
            <label htmlFor="curso">Curso</label>
            <select
              id="curso"
              name="curso"
              className="form-element"
              defaultValue=""
              disabled={!unidadeSelecionado.length}
              onChange={handleCursoChange}
            >
              <option value="">Todos</option>
              {cursos.map((opt) => <option key={opt.id} value={opt.id}>{opt.curso}</option>)}
            </select>
          </div>
        }

        { selectSerie &&
          <div className="p-2 w-full">
            <label htmlFor="serie">Série</label>
            <select
              id="serie"
              name="serie"
              className="form-element"
              defaultValue=""
              disabled={!cursoSelecionado.length}
              onChange={handleSerieChange}
            >
              <option value="">Todos</option>
              {series.map((opt) => <option key={opt.id} value={opt.id}>{opt.serie}</option>)}
            </select>
          </div>
        }

        { selectTurma &&
          <div className="p-2 w-full">
            <label htmlFor="turma">Turma</label>
            <select
              id="turma"
              name="turma"
              className="form-element"
              defaultValue=""
              disabled={!serieSelecionado.length}
            >
              <option value="">Todos</option>
              {turmas.map((opt) => <option key={opt.id} value={opt.id}>{opt.turma}</option>)}
            </select>
          </div>
        }
      </div>
    </div>
  )
}
