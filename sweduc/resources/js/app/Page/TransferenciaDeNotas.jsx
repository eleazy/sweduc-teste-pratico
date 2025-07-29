import React, { useEffect, useState } from "react";
import axios from "axios";
import SelectAnoLetivo from "../Components/SelectAnoLetivo.jsx";
import SelectUnidade from "../Components/SelectUnidade.jsx";
import SelectTurma from "../Components/SelectTurma.jsx";
import SelectCurso from "../Components/SelectCurso.jsx";
import SelectSerie from "../Components/SelectSerie.jsx";

function Tabela(data, moverNota) {
  return (
    <div className="my-3">
      <table className="w-full bg-gray-100">
        <thead>
          <tr className="bg-gray-300">
            <th className="p-2">ID Nota</th>
            <th className="p-2">Nome</th>
            <th className="p-2">Avaliação</th>
            <th className="p-2">Nome do período</th>
            <th className="p-2">Ano letivo</th>
            <th className="p-2">Disciplina</th>
            <th className="p-2">Turma</th>
            <th className="p-2"></th>
          </tr>
        </thead>

        <tbody>
          {data.map(n => (
            <tr
              key={n.id}
              className="hover:bg-gray-300"
            >
              <td className="p-2">
                {n.id}
              </td>

              <td className="p-2">
                {n.aluno.pessoa.nome}
              </td>

              <td className="p-2">
                {n.avaliacao?.avaliacao}
              </td>

              <td className="p-2">
                {n.media.nome}
                {
                  n.media.nome !== n.media.periodo.periodo &&
                  <strike className="block text-gray-500">
                    {n.media.periodo.periodo}
                  </strike>
                }
              </td>

              <td className="p-2">
                {n.media.grade.periodo_letivo.anoletivo}
              </td>

              <td className="p-2">
                {n.media.grade.disciplina.disciplina}
              </td>

              <td className="p-2">
                {n.media.grade.turma.turma}
              </td>

              <td className="p-2">
                <button
                  onClick={() => moverNota(n.id, n.nova_media)}
                  className="sw-btn sw-btn-primary whitespace-nowrap"
                >
                  Migrar para turma atual
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

export default function TransferenciaDeNotas() {
  const [anoletivo, setAnoletivo] = useState('');
  const [unidade, setUnidade] = useState('');
  const [curso, setCurso] = useState('');
  const [serie, setSerie] = useState('');
  const [turma, setTurma] = useState('');
  const [aluno, setAluno] = useState(null);
  const [data, setData] = useState([]);

  const moverNota = (id, media) => {
    axios.put(`/api/v1/academico/notas/${id}`, {
      media
    }).then(
      () => setData(data.filter(n => n.id !== id))
    )
  }

  useEffect(() => {
    if (turma) {
      const formData = new FormData();
      formData.set('action', 'buscarAlunosTranferencias');
      formData.set('idturma', turma);
      formData.set('idanoletivo', anoletivo);

      axios.post(`/dao/alunos.php`, formData).then((response) => {
        const selectAluno = document.getElementById('aluno');
        selectAluno.innerHTML = response.data;
      })
    }
  }, [turma]);

  useEffect(() => {
    if (aluno) {
      const form = new FormData();
      form.set('idalunos', aluno);
      form.set('idanoletivo', anoletivo);
      form.set('idturma', turma);

      axios.post(`/academico/transferencia-de-notas`, form).then((response) => {
        setData(response.data);
      })
    }
  }, [aluno]);

  return (
    <div id="content-outer" className="container-fluid">
      <h3>
        Acadêmico | Transferências de Notas
      </h3>

      <form
        id="mainform"
        method="post"
        action=""
        className="box-search"
      >
        <div className="flex flex-auto">
          <div className="w-full p-2">
            <label
              htmlFor="ano-letivo"
              className="whitespace-nowrap"
            >
              Ano letivo
            </label>

            <SelectAnoLetivo
              id="ano-letivo"
              value={anoletivo}
              onChange={(e) => setAnoletivo(e.target.value)}
            ></SelectAnoLetivo>
          </div>

          <div className="w-full p-2">
            <label htmlFor="unidade">
              Unidade
            </label>

            <SelectUnidade
              id="unidade"
              value={unidade}
              onChange={(e) => setUnidade(e.target.value)}
              disabled={!anoletivo}
            />
          </div>

          <div className="w-full p-2">
            <label htmlFor="curso">
              Curso
            </label>

            <SelectCurso
              id="curso"
              value={curso}
              onChange={(e) => setCurso(e.target.value)}
              optionsUpdated={() => setCurso('')}
              unidade={unidade}
            />
          </div>

          <div className="w-full p-2">
            <label htmlFor="serie">
              Série
            </label>

            <SelectSerie
              id="serie"
              value={serie}
              onChange={(e) => setSerie(e.target.value)}
              optionsUpdated={() => setSerie('')}
              curso={curso}
            />
          </div>
        </div>

        <div className="flex">
          <div className="w-auto p-2">
            <label htmlFor="turma">
              Turma atual
            </label>

            <SelectTurma
              id="turma"
              value={turma}
              onChange={(e) => setTurma(e.target.value)}
              optionsUpdated={() => setTurma('')}
              serie={serie}
            />
          </div>

          <div className="w-auto p-2">
            <label htmlFor="aluno">
              Aluno
            </label>

            <select
              id="aluno"
              name="aluno"
              className="form-element"
              onChange={(e) => setAluno(e.target.value)}
              disabled={!turma}
            >
              <option value="">Selecione o aluno</option>
            </select>
          </div>
        </div>
      </form>

      { data.length ? Tabela(data, moverNota) : null }
    </div>
  )
}
