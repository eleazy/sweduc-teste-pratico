function limpaResultados () {
  const resultados = document.querySelector('#resultados');
  if (resultados) resultados.innerHTML = '';
  setPeriodoDisplay('none');
}

function displayHomeElements (show) {
    const homeOuter = document.querySelector("#homeOuter");
    const voltaInicio = document.querySelector("#voltaInicio");

    if (homeOuter) homeOuter.style.display = `${show ? 'flex' : 'none'}`;
    if (voltaInicio) voltaInicio.style.display = `${show ? 'none' : 'block'}`;
}

function adicionaEventosMenu() {
  /* Triggers para remover seleção de períodos */
  const menu = document.querySelector("#hover_menu");
  menu.childNodes.forEach((item) => {
    if (item.value !== "Avaliações") {
      item.addEventListener("click", limpaResultados);
    }
      item.addEventListener("click", ()=> displayHomeElements(false));
  });

  /* Adiciona triggers nas opções do home e 'trocar senha' para limpar a tela */
  const homeContent = document.querySelector("#homeContent");
  const opcoes = Array.from(homeContent.childNodes).concat(document.querySelector("#trocarSenhar"));
  opcoes.forEach((item) => {
    if (!['Mensagem', 'Login do Aluno'].some(t => item.textContent.includes(t))){
      item.addEventListener("click", () => displayHomeElements(false));
      }
  });
};

var displayded = false;
function removeNaoSelecionados(id) {
  if (displayded) return;

  const todos = document.querySelectorAll('[id^="linha"]');
  todos.forEach(function (item) {
    if (item.id.replace('linha', '') !== id.toString()) {
      item.remove();
    } else {
      item.style.cursor = 'default';
    }
  });

  const selAluno = document.querySelector('#selAluno');
  if (selAluno) selAluno.remove();

  const menuButton = document.querySelector('#menu_button');
  const menu = document.querySelector("#hover_menu");
  const homeOuter = document.querySelector("#homeOuter");
  const loginResp = document.querySelector("#loginResp");
  if (menuButton) menuButton.classList.remove('hide');
  if (homeOuter) homeOuter.style.display = "flex";

  [menuButton, menu].forEach(b => {
    b.addEventListener("click", function () {
      menu.classList.toggle("show");
      //loginResp.classList.toggle("hidden"); /* Impede login de abrir quando menu estiver ativo */
    });
  });

  adicionaEventosMenu();

  displayded = true;
}

function backToHome() {
  limpaResultados();
  displayHomeElements(true);
}

function setPeriodoDisplay(d) {
  const periodoDiv = document.querySelector('[id^="dialog_avaliacoes"]');
  periodoDiv.style.display = d;

  const mes = new Date().getMonth() + 1;
  /* Preve período basedo no mês atual */
  if (d == "block") {
    var periodoOpcoes = periodoDiv.querySelector('[id^="select_avaliacoes"]').childNodes;

    var periodos = [];
    periodoOpcoes.forEach((item) => {
      periodos.push(item.innerText);
    });

    var t = periodos.join().includes('Bimestre') ?
      [1, 2].includes(mes) ? '1º Bimestre' : [3, 4].includes(mes) ? '2º Bimestre' : [5, 6].includes(mes) ? '3º Bimestre' : '4º Bimestre'
      :
      [1, 2, 3].includes(mes) ? '1º Trimestre' : [4, 5, 6].includes(mes) ? '2º Trimestre' : [7, 8, 9].includes(mes) ? '3º Trimestre' : '4º Trimestre';

    colorPeriodo(t);
  }
}

function colorPeriodo(ativo) {
  var periodoOpcoes = document.querySelector('[id^="select_avaliacoes"]').childNodes;
  periodoOpcoes.forEach((item) => {
    if (!item || !item.innerText) return;
    if (item.innerText == ativo) {
      item.click();
      item.classList.add('periodoAtivo');
    } else {
      item.classList.remove('periodoAtivo');
    }
  });
}

function printDiv(s) {
  var content = document.querySelector(s);
  var originalContents = document.body.innerHTML;
  document.body.innerHTML = content.innerHTML;
  window.print();
  document.body.innerHTML = originalContents;
}

function copiaCodigoBoletoPix(tituloId, tipo) {
  fetch(`/boleto/copiarCodigo?id=${tituloId}&tipo=${tipo}`)
    .then(response => response.text())
    .then(data => {
      if (data !== 'error') {
        navigator.clipboard.writeText(data).then(() => {
          var b = document.querySelector("#botaoCopia");
          if (b){
              b.innerHTML = `<i class="fas fa-copy"></i>Copiado`;
              b.classList.add('codigoCopiado');
          }
          Swal.fire('Pronto!','Código copiado para a área de transferência','success');
        }).catch(err => {
          Swal.fire('Erro!','Não foi possível copiar o código','error');
        });
      }
    })
    .catch(error => console.error('Erro:', error));
}

function mostraMensagemResponsavel(m) {
  Swal.fire({
    title: 'Mensagem Para o Responsável',
    html: '<div class="swal-msg">' + m + '</div>',
    icon: 'info',
    showCancelButton: false,
    cancelButtonText: 'Não mostrar novamente',
    confirmButtonText: 'Fechar',
    customClass: {
      actions: 'swal2-custom-actions',
      title: 'swal2-custom-title',
    }
  });
}

/* Seleciona aluno se houver apenas uma matricula */
const matriculas = document.querySelectorAll('[id^="linha"]');
if (matriculas.length == 1) {
  document.querySelector(".cardInfoAluno").click();
}

/* Mostra mensagem para o responsável */
const msgButton = document.querySelector('#msgTodasButton');
if (msgButton) setTimeout(() => { msgButton.click(); }, 500);

function abrirDadosResponsavel(idAluno) {
  fetch(`responsaveis/matricula/atualizarDadosCadastrais/${idAluno}`)
  .then(response => response.text())
  .then(data => {
    document.getElementById('resultados').innerHTML = data;
  })
  .catch(error => console.error('Erro ao buscar dados:', error));
}

function salvaDadosCadastrais() {
  var form = document.getElementById('dados-cadastrais-form');
  var formData = new FormData(form);

  fetch('/responsaveis/matricula/saveUpdateDadosCadastrais', {
      method: 'POST',
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      console.log(data);
      if (data.success) {
          sweduc.carregarUrl('/responsaveis/matricula');
      } else {
          criaAlerta('error', 'Erro ao salvar dados cadastrais');
      }
  })
  .catch(error => {
      criaAlerta('error', 'Erro ao salvar dados cadastrais');
  });
}

function getCidades(idEstado) {
  fetch(`/api/v1/core/estados/${idEstado}/cidades`)
    .then(response => response.json())
    .then(data => {
      const selectCidadeAll = document.querySelectorAll('.select-cidades-dados-cadastrais');
      selectCidadeAll.forEach(selectCidade => {
        selectCidade.innerHTML = '';
        data.forEach(cidade => {
          const option = document.createElement('option');
          option.value = cidade.id;
          option.innerText = cidade.nom_cidade;
          selectCidade.appendChild(option);
        });
      });
    });
};

/* ____________________PWA Configs_____________________ */
// Desabilitado por enquanto

// if (navigator.standalone) {
//   console.log('Este aplicativo já está sendo executado como um app na tela inicial.');

// } else if (window.navigator.userAgent.includes('iPhone') || window.navigator.userAgent.includes('iPad')) {

//   alert('Para adicionar este site à sua tela inicial, toque no ícone de compartilhamento e selecione "Adicionar à Tela Inicial".');

// } else if (window.navigator.userAgent.includes('Android')) {

//   navigator.serviceWorker.getRegistration().then(function (registration) {
//     if (!registration) {
//       navigator.serviceWorker.register('/service-worker.js')
//         .then(registration => {
//           console.log('ServiceWorker registration successful with scope: ', registration.scope);
//         }, error => {
//           console.log('ServiceWorker registration failed: ', error);
//         });
//     }
//   });

//   var deferredPrompt;
//   window.addEventListener('beforeinstallprompt', (e) => {

//     e.preventDefault();
//     deferredPrompt = e;
//     deferredPrompt.prompt();

//     deferredPrompt.userChoice.then((choiceResult) => {
//       if (choiceResult.outcome === 'accepted') {
//         console.log('Usuário aceitou o A2HS prompt');
//       } else {
//         console.log('Usuário dispensou o A2HS prompt');
//       }
//       deferredPrompt = null;
//     });
//   });
// }
