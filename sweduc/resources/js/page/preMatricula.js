import VMasker from 'vanilla-masker'
import Axios from 'axios'

var defaultSelect = '<option value="" selected disabled>Selecione</option>'

function preencheCurso(unidadeEl, seletor) {
    var unidadeId = unidadeEl.value
    var cursoContainer = document.querySelector(seletor);

    Axios.post('/api/public/v1/academico/cursos', { unidadeId: unidadeId })
        .then(function (response) {
            var cursos = response.data;
            var options = cursos.map(function (val) {
                return '<option value="' + val.id + '">' + val.curso + '</option>';
            }).join('');

            cursoContainer.innerHTML = defaultSelect + options;
        });
}

function preencheSerie(cursoEl, seletor) {
    var cursoId = cursoEl.value
    var serieContainer = document.querySelector(seletor);

    Axios.post('/api/public/v1/academico/serie', { cursoId: cursoId })
        .then(function (response) {
            var cursos = response.data;
            var options = cursos.map(function (val) {
                return '<option value="' + val.id + '">' + val.serie + '</option>';
            }).join('');

            serieContainer.innerHTML = defaultSelect + options;
        });
}

function preencheTurno(serieEl, seletor) {
    var serieId = serieEl.value
    var turnoContainer = document.querySelector(seletor);

    Axios.post('/api/public/v1/academico/turno', { serieId: serieId })
        .then(function (response) {
            var cursos = response.data;
            var options = cursos.map(function (val) {
                return '<option value="' + val.id + '">' + val.turno + '</option>';
            }).join('');

            turnoContainer.innerHTML = defaultSelect + options;
        });
}

window.preencheCurso = preencheCurso
window.preencheSerie = preencheSerie
window.preencheTurno = preencheTurno

/**
 * CEP
 */
const cep = document.getElementById('cep')
VMasker(cep).maskPattern("99999-999")
cep.onchange = function preencheEndereco() {
    const cep = this.value.replace('-', '')
    var target = document.querySelector('#endereco');

    Axios.get('https://viacep.com.br/ws/' + cep + '/json')
        .then(function (response) {
            var dados = response.data;

            if (!("erro" in dados)) {
                target.value = [
                    dados.uf + ' - ' + dados.localidade,
                    dados.bairro,
                    dados.logradouro
                ].join(', ');
            }
        });
}

const telefone = document.getElementById('telefone')
VMasker(telefone).maskPattern('(99) 9999-9999')
const celular = document.getElementById('celular')
VMasker(celular).maskPattern('(99) 99999-9999')
