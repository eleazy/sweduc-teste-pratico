const d = document;

function getWeekDay(day) {
    var weekDays = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    return weekDays[day];
}
function isToday(dateString) {
    const givenDate = new Date(dateString);
    const today = new Date();
    const givenDateUTC = new Date(givenDate.getUTCFullYear(), givenDate.getUTCMonth(), givenDate.getUTCDate());
    const todayUTC = new Date(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate());
    return (
        givenDateUTC.getFullYear() === todayUTC.getFullYear() &&
        givenDateUTC.getMonth() === todayUTC.getMonth() &&
        givenDateUTC.getDate() === todayUTC.getDate()
    );
}
function getDaysInMonth(year, month) {
    const date = new Date(year, month, 1);
    date.setMonth(date.getMonth() + 1);
    date.setDate(0);
    return date.getDate();
}
function getMonthName(month) {
    const monthNames = [
        'Janeiro',
        'Fevereiro',
        'Março',
        'Abril',
        'Maio',
        'Junho',
        'Julho',
        'Agosto',
        'Setembro',
        'Outubro',
        'Novembro',
        'Dezembro'
    ];
    return monthNames[month];
}
function toggleCalendarLeft(display) {
    d.querySelector('#dia-atual').style.display = display;
    d.querySelector('#eventos-proximos').style.display = display;
}

function renderCalendar(idfunc = null, events = [], permissions = {}, cursos = {}, series = {}, turmas = {}) {
    const canEditAll = permissions['cadastrar-editar'];
    const canAddNew = permissions['cadastrar'];
    const hoje = new Date();
    const proxEventos = events.filter((event) => new Date(event.date) >= hoje).sort((a, b) => new Date(a.date) - new Date(b.date));
    var hojeH2 = d.querySelector('#dia-atual h2'); /* dia */
    var hojeH3 = d.querySelector('#dia-atual h3'); /* dia da semana */
    var proxEventosDiv = d.querySelector('#eventoDivOuter');
    proxEventosDiv.innerHTML = '';

    hojeH2.innerHTML = hoje.getDate();
    hojeH3.innerHTML = getWeekDay(hoje.getDay());

    /* Preenche lista de eventos  */
    proxEventos.forEach((prox) => {
        var eventoDiv = isToday(prox.date.split(' ')[0]) ? 'active' : '';
        var dateOrHoje = isToday(prox.date.split(' ')[0]) ? 'Hoje' : prox.date.split(' ')[0].split('-').reverse().join('/');
        var message = prox.description.length > 50 ? prox.description.substring(0, 70) + '...' : prox.description;

        var proxEvento = `<div class="eventoDiv ${eventoDiv}"><div><p>${dateOrHoje}</p><h4>${prox.title}</h4></div><h5>${message}</h5></div>`;
        proxEventosDiv.innerHTML += proxEvento;
    });

    if (proxEventos.length === 0) {
        d.querySelector('#eventos-proximos').innerHTML = '<h5>Nenhum evento próximo</h5>';
    }

    /* Adiciona trigger na seleção de anos para alterar entre eles */
    var yearSelection = d.querySelector('#calendar-right #selecao-ano');
    yearSelection.querySelector('h2').innerHTML = hoje.getFullYear();
    var yearPlus = d.querySelector('#calendar-right #selecao-ano #ano-mais');
    var yearLess = d.querySelector('#calendar-right #selecao-ano #ano-menos');

    [yearPlus, yearLess].forEach((y) => {
        y.addEventListener('click', (e) => {
            const selectedYear = yearSelection.querySelector('h2');
            if (e.target.id === 'ano-mais') {
                selectedYear.innerHTML = parseInt(selectedYear.innerHTML) + 1;
            } else {
                selectedYear.innerHTML = parseInt(selectedYear.innerHTML) - 1;
            }
            setCalendar(parseInt(selectedYear.innerHTML), d.querySelector('#calendar-right #selecao-meses div.mes-atual').getAttribute('values') - 1);
        });
    });

    /* Adiciona trigger na seleção de meses para alterar entre eles */
    var monthSelection = d.querySelectorAll('#calendar-right #selecao-meses div');
    monthSelection.forEach((month) => {
        month.addEventListener('click', (e) => {
            const selectedMonth = e.target.getAttribute('values') - 1;
            const selectedYear = yearSelection.querySelector('h2').innerHTML;
            monthSelection.forEach((m) => m.classList.remove('mes-atual'));

            setCalendar(selectedYear, selectedMonth);
        });
    });

    var blockInteraction = false;

    function setCalendar(year, month) {
        /* Preenche dias do calendário e marca dias com evento */
        const monthNumOfDays = getDaysInMonth(year, month);
        const calendar = d.querySelector('#calendario-dias');
        var calendarDaysAndFillDays = calendar.querySelectorAll('div:not(.week)');
        calendarDaysAndFillDays.forEach((day) => day.remove());
        const localHoje = new Date(year, month);

        [...Array(monthNumOfDays).keys()].forEach((day) => {
            var dayDiv = d.createElement('div');
            dayDiv.classList.add('calendario-dia');
            var month = localHoje.getMonth() + 1;
            dayDiv.values = `${localHoje.getFullYear()}-${month < 10 ? '0' + month : month}-${day + 1 < 10 ? '0' + (day + 1) : day + 1}`;
            dayDiv.innerHTML = day + 1;
            if (events.find((event) => event.date.split(' ')[0] === dayDiv.values)) {
                dayDiv.classList.add('has-event');
            }
            /* Marca dia atual */
            if (isToday(dayDiv.values)) {
                dayDiv.classList.add('dia-atual');
            }
            /* Marca mês atual */
            d.querySelector(`#calendar-right #selecao-meses [values="${month}"]`).classList.add('mes-atual');
            /* Verifica qual dia da semana é o dia 01, para acertar os dias da semana no grid */
            if (day === 0) {
                const firstDay = new Date(localHoje.getFullYear(), localHoje.getMonth(), 1).getDay();
                for (var i = 0; i < firstDay; i++) {
                    calendar.appendChild(d.createElement('div'));
                }
            }
            calendar.appendChild(dayDiv);
        });

        /* Mostra eventos do dia selecionado */
        var calendarioDias = d.querySelectorAll('.calendario-dia');
        var diaAtual = d.querySelector('#dia-selecionado') || d.createElement('div');
        var eventosDiaDiv = d.querySelector('#eventos-dia-selecionado') || d.createElement('div');
        if (!diaAtual.id) diaAtual.id = 'dia-selecionado';
        if (!eventosDiaDiv.id) eventosDiaDiv.id = 'eventos-dia-selecionado';
        var hadDateSelected = '';
        var closeButton = d.querySelector('#closeEventsDay');
        closeButton.addEventListener('click', closeEventsOfDay);

        // Funcionalidade do botão 'Novo Evento'
        var newEventButton = d.querySelector('#novo-evento');
        if (!canAddNew) {
          newEventButton.style.display = 'none';
        } else {
          newEventButton.addEventListener('click', ()=> addOrEditEvent(null));
        }

        function showEventsOfDay(date, wasClick) {
            if (d.querySelector('#novo-evento-outer')) return; // Se houver formulário de novo evento aberto, não mostra eventos do dia
            var eventosDia = events.filter((event) => event.date.split(' ')[0] === date);
            [diaAtual, eventosDiaDiv].forEach((div) => div.innerHTML = '');
            if (wasClick) {
                closeButton.classList.add('active');
                hadDateSelected = date; // Adiciona data selecionada (se houver) ao input de novo evento
            }

            diaAtual.appendChild(d.createElement('h3')).innerHTML = `${getWeekDay(new Date(date).getUTCDay())}, ${new Date(date).getUTCDate()} de ${getMonthName(localHoje.getUTCMonth())}`;
            d.querySelector('#calendar-left').insertBefore(diaAtual, d.querySelector('#left-buttons'));

            eventosDia.forEach((evento) => {
                var eventoDiv = d.createElement('div');
                eventoDiv.classList.add('eventosDiaDiv');
                const isOwner = evento.idfuncionario == idfunc;
                if (!['contas-a-pagar','prospeccao','entrevista'].includes(evento.type)) {
                  if (isOwner || canEditAll) {
                    const editEvent = d.createElement('button');
                    editEvent.classList.add('editEvent');
                    editEvent.innerHTML = 'Editar';
                    eventoDiv.appendChild(editEvent);
                    editEvent.addEventListener('click', () => addOrEditEvent(evento.eventId));

                    const deleteB = d.createElement('button');
                    deleteB.classList.add('deleteEvent');
                    deleteB.innerHTML = 'Excluir';
                    eventoDiv.appendChild(deleteB);
                    deleteB.addEventListener('click', () => deleteEvent(evento.eventId));
                  }
                }
                eventoDiv.appendChild(d.createElement('h5')).innerHTML = evento.title;
                eventoDiv.appendChild(d.createElement('h4')).innerHTML = evento.description;
                eventosDiaDiv.appendChild(eventoDiv);
            });
            var existemEventos = eventosDiaDiv.querySelector('.eventosDiaDiv');
            if (!existemEventos) {
                eventosDiaDiv.innerHTML = '<h5>Nenhum evento para este dia</h5>';
            }
            d.querySelector('#calendar-left').insertBefore(eventosDiaDiv, d.querySelector('#left-buttons'));
        }

        function closeEventsOfDay() {
            blockInteraction = false;
            toggleCalendarLeft('block');
            eventosDiaDiv.remove();
            diaAtual.remove();
            closeButton.classList.remove('active');
        }

        function removeNewEventArea(display) {
            var newEventDiv = d.querySelector('#novo-evento-outer');
            if (newEventDiv) newEventDiv.remove();
            var newEvButtons = d.querySelectorAll('.novo-evento-botoes');
            if (newEvButtons) newEvButtons.forEach((b => b.remove()));

            toggleCalendarLeft(display);
            newEventButton.style.display = 'flex';
            hadDateSelected = '';
            blockInteraction = false;
        }

        function addOrEditEvent(eventId = null) {
          removeNewEventArea('none'); // Fecha novo evento anterior se houver
          blockInteraction = true;
          eventosDiaDiv.remove(); // Fecha eventos do dia
          diaAtual.remove();
          closeButton.classList.remove('active');
          newEventButton.style.display = 'none';
          var evento = events.find((event) => event.eventId === eventId);

          var newEventForm = d.createElement('div');
          newEventForm.id = 'novo-evento-outer';

          var formTitle = d.createElement('h3');
          formTitle.textContent = eventId ? 'Editar Evento' : 'Novo Evento';
          newEventForm.appendChild(formTitle);

          var formContainer = d.createElement('div');
          formContainer.id = 'novo-evento-form';

          var dateInput = d.createElement('input');
          dateInput.id = 'newEvDate';
          dateInput.type = 'date';
          dateInput.required = true;
          if (eventId) {
              dateInput.value = evento.date.split(' ')[0];
          } else {
              dateInput.value = hadDateSelected;
          }
          formContainer.appendChild(dateInput);

          var titleInput = d.createElement('input');
          titleInput.id = 'newEvTitle';
          titleInput.type = 'text';
          titleInput.placeholder = 'Título';
          titleInput.required = true;
          if (eventId) {
              titleInput.value = evento.title;
          }
          formContainer.appendChild(titleInput);

          var tipoEvento = d.createElement('select');
          tipoEvento.id = 'newEvTipo';
          tipoEvento.required = true;
          var funcEvento = d.createElement('option');
          funcEvento.value = 'funcionario';
          funcEvento.textContent = 'Funcionários';
          var alunoEvento = d.createElement('option');
          alunoEvento.value = 'alunos';
          alunoEvento.textContent = 'Alunos';
          var respEvento = d.createElement('option');
          respEvento.value = 'responsaveis';
          respEvento.textContent = 'Responsáveis';

          tipoEvento.appendChild(funcEvento);
          tipoEvento.appendChild(alunoEvento);
          tipoEvento.appendChild(respEvento);

          // Select de unidades que o funcionário tem permissão de cadastrar eventos
          var unidadesSelect = d.createElement('select');
          unidadesSelect.id = 'newEvUnidades';
          unidadesSelect.multiple = true;

          // Preenche select de unidades
          if (permissions.unidades.length > 1) {
            var todasOption = d.createElement('option');
            todasOption.value = '-1';
            todasOption.textContent = 'Todas';
            unidadesSelect.appendChild(todasOption);

            permissions.unidades.forEach(unidade => {
              var option = d.createElement('option');
              option.value = unidade.id;
              option.textContent = unidade.unidade;

              const regex = new RegExp(`(^|,)${unidade.id}(,|$)`); // Verifica se a unidade está no evento, caso seja edição
              if (eventId && evento.idunidades && regex.test(evento.idunidades)) {
                  option.selected = true;
              }

              if (unidade.unidadeDoFuncionario && !eventId) option.selected = true;

              unidadesSelect.appendChild(option);
            });
          } else {
            var option = d.createElement('option');
            option.value = permissions.unidades[0].id;
            option.textContent = permissions.unidades[0].unidade;
            option.selected = true;
            unidadesSelect.appendChild(option);

            unidadesSelect.disabled = true;
          }

          var divTiposUnidades = d.createElement('div');
          divTiposUnidades.appendChild(tipoEvento);
          divTiposUnidades.appendChild(unidadesSelect);
          divTiposUnidades.classList.add('divTiposUnidades');

          formContainer.appendChild(divTiposUnidades);

          // Select de cursos
          var cursosSelect = d.createElement('select');
          cursosSelect.id = 'newEvCursos';
          cursosSelect.multiple = true;
          cursosSelect.disabled = true;
          cursosSelect.hidden = true;

          // Preenche select de cursos
          cursos.forEach(curso => {
              var option = d.createElement('option');
              option.value = curso.id;
              option.textContent = curso.curso;

              const regex = new RegExp(`(^|,)${curso.id}(,|$)`); // Verifica se o curso está no evento, caso seja edição
              if (eventId && evento.idcursos && regex.test(evento.idcursos)) {
                  option.selected = true;
              }
              cursosSelect.appendChild(option);
          });
          formContainer.appendChild(cursosSelect);

          // Select de series
          var seriesSelect = d.createElement('select');
          seriesSelect.id = 'newEvSeries';
          seriesSelect.multiple = true;
          seriesSelect.disabled = true;
          seriesSelect.hidden = true;

          // Preenche select de series
          series.forEach(serie => {
              var option = d.createElement('option');
              option.value = serie.id;
              option.textContent = serie.serie;

              const regex = new RegExp(`(^|,)${serie.id}(,|$)`);
              if (eventId && evento.idseries && regex.test(evento.idseries)) {
                  option.selected = true;
              }
              seriesSelect.appendChild(option);
          });
          formContainer.appendChild(seriesSelect);

          // Select de turmas
          var turmasSelect = d.createElement('select');
          turmasSelect.id = 'newEvTurmas';
          turmasSelect.multiple = true;
          turmasSelect.required = true;
          turmasSelect.disabled = true;
          turmasSelect.hidden = true;
          var todasOption = d.createElement('option');
          todasOption.value = '-1';
          todasOption.textContent = 'Todas';
          turmasSelect.appendChild(todasOption);

          // Preenche select de turmas
          turmas.forEach(turma => {
              var option = d.createElement('option');
              option.value = turma.id;
              option.textContent = turma.turma;

              const regex = new RegExp(`(^|,)${turma.id}(,|$)`);
              if (eventId && evento.idturmas && regex.test(evento.idturmas)) {
                  option.selected = true;
              }
              turmasSelect.appendChild(option);
          });
          formContainer.appendChild(turmasSelect);

          var limpaFiltros = d.createElement('button');
          limpaFiltros.hidden = true;
          limpaFiltros.textContent = 'Limpar Filtros';
          limpaFiltros.style.fontSize = '12px';
          limpaFiltros.classList.add('novo-evento-botoes');
          limpaFiltros.addEventListener('click', () => {
            Array.from(turmasSelect.options).forEach(option => { option.selected = false; option.hidden = false } ); // Limpa seleção de turmas
            Array.from(cursosSelect.options).forEach(option => { option.selected = false; option.hidden = false } ); // Limpa seleção de cursos
            Array.from(seriesSelect.options).forEach(option => { option.selected = false; option.hidden = false } ); // Limpa seleção de series
            turmasSelect.options[0].disabled = false;
          });
          formContainer.appendChild(limpaFiltros);

          var descTextarea = d.createElement('textarea');
          descTextarea.id = 'newEvDesc';
          descTextarea.resize = 'none';
          descTextarea.placeholder = 'Descrição';
          if (eventId) descTextarea.value = evento.description;
          formContainer.appendChild(descTextarea);

          newEventForm.appendChild(formContainer);

          // Adiciona evento de change no select de tipo de evento, para desbloquear select de cursos, series e turmas
          tipoEvento.addEventListener('change', (e) => {
            if (e.target.value != 'funcionario') {
              limpaFiltros.hidden = false;
              [turmasSelect, cursosSelect, seriesSelect].forEach((s) => {
                s.hidden = false;
                s.disabled = false
              });
            } else {
              limpaFiltros.hidden = true;
              [turmasSelect, cursosSelect, seriesSelect].forEach((s) => {
                s.hidden = true;
                s.disabled = true
              });
              Array.from(turmasSelect.options).forEach(option => option.selected = false ); // Limpa seleção de turmas
            }
          })

          // Adiciona evento de change no select de unidades, para filtrar opções de cursos
          unidadesSelect.addEventListener('change', (e) => {
            if (unidadesSelect.selectedOptions[0].value == '-1') {
              limpaFiltros.click();
              return;
            }

            var selectedUnidades = Array.from(unidadesSelect.selectedOptions).map(option => option.value);
            var selectedCursos = Array.from(cursosSelect.selectedOptions).map(option => option.value);
            var selectedSeries = Array.from(seriesSelect.selectedOptions).map(option => option.value);
            var selectedTurmas = Array.from(turmasSelect.selectedOptions).map(option => option.value);

            cursos.forEach(curso => {
              var cursoOption = d.querySelector(`#newEvCursos option[value="${curso.id}"]`);
              if (selectedUnidades.length > 0 && selectedUnidades.some(unidade => curso.idunidade == unidade) && !selectedCursos.includes(curso.id)) {
                cursoOption.hidden = false;
              } else {
                cursoOption.hidden = true;
                cursoOption.selected = false;
              }
            });

            series.forEach(serie => {
              var serieOption = d.querySelector(`#newEvSeries option[value="${serie.id}"]`);
              if (selectedUnidades.length > 0 && selectedUnidades.some(unidade => serie.idunidade == unidade) && !selectedSeries.includes(serie.id)) {
                serieOption.hidden = false;
              } else {
                serieOption.hidden = true;
                serieOption.selected = false;
              }
            });

            turmas.forEach(turma => {
              var turmaOption = d.querySelector(`#newEvTurmas option[value="${turma.id}"]`);
              if (selectedUnidades.length > 0 && selectedUnidades.some(unidade => turma.idunidade == unidade) && !selectedTurmas.includes(turma.id)) {
                turmaOption.hidden = false;
              } else {
                turmaOption.hidden = true;
                turmaOption.selected = false;
              }
            });
          });

          // Adiciona evento de change no select de cursos, para filtrar opções de series e turmas
          cursosSelect.addEventListener('change', (e) => {
            // Se existe curso selecionado, opção 'Todas as Turmas' é automaticamente todas as turmas dos cursos selecionados
            if (cursosSelect.selectedOptions.length > 0) turmasSelect.options[0].disabled = true;

            var selectedCursos = Array.from(cursosSelect.selectedOptions).map(option => option.value);
            var selectedSeries = Array.from(seriesSelect.selectedOptions).map(option => option.value);
            var selectedTurmas = Array.from(turmasSelect.selectedOptions).map(option => option.value);

            series.forEach(serie => {
              var serieOption = d.querySelector(`#newEvSeries option[value="${serie.id}"]`);
              if (selectedCursos.length > 0 && selectedCursos.some(curso => serie.idcurso == curso) && !selectedSeries.includes(serie.id)) {
                serieOption.hidden = false;
              } else {
                serieOption.hidden = true;
                serieOption.selected = false;
              }
            });

            turmas.forEach(turma => {
              var turmaOption = d.querySelector(`#newEvTurmas option[value="${turma.id}"]`);
              if (selectedCursos.length > 0 && selectedCursos.some(curso => turma.idcurso == curso) && !selectedTurmas.includes(turma.id)) {
                turmaOption.hidden = false;
              } else {
                turmaOption.hidden = true;
                turmaOption.selected = false;
              }
            });
          });

          // Adiciona evento de change no select de series, para filtrar opções de turmas
          seriesSelect.addEventListener('change', (e) => {
            // Se existe série selecionada, opção 'Todas as Turmas' é automaticamente todas as turmas das séries selecionadas
            if (seriesSelect.selectedOptions.length > 0) turmasSelect.options[0].disabled = true;

            var selectedSeries = Array.from(seriesSelect.selectedOptions).map(option => option.value);
            var selectedTurmas = Array.from(turmasSelect.selectedOptions).map(option => option.value);

            turmas.forEach(turma => {
              var turmaOption = d.querySelector(`#newEvTurmas option[value="${turma.id}"]`);
              if (selectedSeries.length > 0 && selectedSeries.some(serie => turma.idserie == serie) && !selectedTurmas.includes(turma.id)) {
                turmaOption.hidden = false;
              } else {
                turmaOption.hidden = true;
                turmaOption.selected = false;
              }
            });
          });

          if (eventId) {
            switch (evento.type) {
                case 'funcionario':
                    funcEvento.selected = true;
                    break;
                case 'alunos':
                    alunoEvento.selected = true;
                    break;
                case 'responsaveis':
                    respEvento.selected = true;
                    break;
            }
            tipoEvento.dispatchEvent(new Event('change'));
          }

          // Dispara eventos de change para filtrar opções de cursos, séries e turmas, porque sempre haverá uma unidade selectionada ao criar um novo evento
          unidadesSelect.dispatchEvent(new Event('change'));

          // Botões de salvar e cancelar
          var salvarButton = d.createElement('button');
          salvarButton.textContent = 'Salvar';
          salvarButton.classList.add('novo-evento-botoes');

          salvarButton.addEventListener('click', () => {
            // Lida com a seleção de turmas, se 'Todas' estiver selecionado, seleciona todas as turmas
            // Se 'Todas' não estiver selecionado, seleciona todas as turmas dos cursos ou séries selecionados
            var selectedTurmas = Array.from(turmasSelect.selectedOptions).map(option => option.value);
            if (selectedTurmas.length == 0) {
              var selectedSeries = Array.from(seriesSelect.selectedOptions).map(option => option.value);
              var selectedCursos = Array.from(cursosSelect.selectedOptions).map(option => option.value);

              if (selectedSeries.length > 0) {
                selectedTurmas = turmas.filter(turma => selectedSeries.some(s=> s == turma.idserie)).map(turma => turma.id);
              } else if (selectedCursos.length > 0) {
                selectedTurmas = turmas.filter(turma => selectedCursos.some(c=> c == turma.idcurso)).map(turma => turma.id);
              }

              Array.from(turmasSelect.options).forEach(option => {
                if (selectedTurmas.some(t=> t == option.value)) {
                  option.selected = true;
                } else {
                  option.selected = false;
                }
              });
            }

            // Lida com a seleção de unidades, se 'Todas' estiver selecionado, seleciona todos as unidades
            if (unidadesSelect.selectedOptions.length == 1 && unidadesSelect.selectedOptions[0].value == '-1') {
              Array.from(unidadesSelect.options).forEach(option => {
                option.selected = true;
              });
            }

            saveNewEvent(eventId);
          });

          var cancelarButton = d.createElement('button');
          cancelarButton.textContent = 'Cancelar';
          cancelarButton.classList.add('novo-evento-botoes');
          cancelarButton.addEventListener('click', () => removeNewEventArea("block"));

          d.querySelector('#left-buttons').insertBefore(salvarButton, d.querySelector('#closeEventsDay'));
          d.querySelector('#left-buttons').insertBefore(cancelarButton, d.querySelector('#closeEventsDay'));
          d.querySelector('#calendar-left').insertBefore(newEventForm, d.querySelector('#left-buttons'));
        }

        function saveNewEvent(eventId) {
            var title = d.querySelector('#novo-evento-form #newEvTitle').value;
            var tipo = d.querySelector('#novo-evento-form #newEvTipo').value;
            var idunidades = Array.from(d.querySelector('#novo-evento-form #newEvUnidades').selectedOptions).map(option => option.value).join(',');
            var idturmas = Array.from(d.querySelector('#novo-evento-form #newEvTurmas').selectedOptions).map(option => option.value).join(',');
            var description = d.querySelector('#novo-evento-form #newEvDesc').value || '';
            var date = d.querySelector('#novo-evento-form #newEvDate').value;

            if (!title || !date) {
                swal('Alerta!', 'Preencha ao menos título e data para adicionar um novo evento', 'warning');
                return;
            }
            if (tipo != 'funcionario' && !idturmas) {
                swal('Alerta!', 'Selecione ao menos uma turma para adicionar um novo evento', 'warning');
                return;
            }
            if (tipo == 'funcionario' && !idunidades) {
                swal('Alerta!', 'Selecione ao menos uma unidade para adicionar um novo evento', 'warning');
                return;
            }

            fetch('/calendarioEventos/saveEvento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    eventId,
                    idfunc,
                    idunidades,
                    dataevento: date,
                    tipo,
                    idturmas,
                    titulo: title,
                    descricao: description,
                }),
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    events.push({ date: date.split('T')[0], title, description, type: 'evento' });
                 })
                .catch(error => console.error('Ocorreu um erro ao inserir novo evento', error))
                .finally(() => {
                    removeNewEventArea('block');
                    setCalendar(year, month);
                });
        }

        function deleteEvent(eventId) {
          Swal.fire({"title":"Deseja realmente excluir este evento?","text":"Esta ação não poderá ser desfeita!","icon":"warning","showCancelButton":true,"confirmButtonColor":"#3085d6","cancelButtonColor":"#d33","confirmButtonText":"Sim, excluir!","cancelButtonText":"Cancelar"})
          .then((result) => {
            if (result.isConfirmed) {

              fetch('/calendarioEventos/deleteEvento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    eventId: eventId
                }),
              })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    events = events.filter((event) => event.eventId !== eventId);
                })
                .catch(error => console.error('Ocorreu um erro ao deletar evento', error))
                .finally(() => {
                    removeNewEventArea('block');
                    setCalendar(year, month);
                });
            }
          });

        }

        // Adiciona eventos de hover e click nos dias do calendário
        calendarioDias.forEach((dia) => {
            dia.addEventListener('mouseover', (e) => {
                if (blockInteraction) return;
                setTimeout(() => {
                    toggleCalendarLeft('none');
                    showEventsOfDay(e.target.values, false);
                }, 200);
            });

            dia.addEventListener('click', (e) => {
                toggleCalendarLeft('none');
                showEventsOfDay(e.target.values, true);
                blockInteraction = true;
            });

            dia.addEventListener('mouseout', () => {
                if (blockInteraction) return;
                setTimeout(() => {
                    if (!blockInteraction) {
                        toggleCalendarLeft('block');
                        closeEventsOfDay();
                    }
                }, 200);
            });
        });

    }
    setCalendar(hoje.getUTCFullYear(), hoje.getUTCMonth());
}
