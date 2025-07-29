$("#login-button").click(function(event){
	event.preventDefault();

	$('form').fadeOut(500);
	$('.wrapper').addClass('form-success');
});

var assetsWarning = null;
$(document).ajaxComplete(function(event, xhr, settings) {
    var requestAssetVersion = xhr.getResponseHeader('ASSETS_VERSION');
    if (requestAssetVersion) {
        if (!assetsVersion) {
            assetsVersion = requestAssetVersion;
        }

        if (requestAssetVersion != assetsVersion && !assetsWarning) {
            assetsWarning = document.createElement('div');
            assetsWarning.className = "w-full bg-yellow-400 p-3 text-lg text-center font-bold text-gray-900 cursor-pointer";
            assetsWarning.innerText = "Houve uma atualização de recursos. Clique aqui recarregar a página.";
            assetsWarning.onclick = function () { window.location = '' };
            document.body.prepend(assetsWarning);
        }
    }
});

$(document).ajaxError ((ev, jxhr, settings) => {
	if (jxhr.status == 401) {
		window.location.href = 'index.php'
	}
});

function criaAlerta(type, message) {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "500",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    toastr[type](message, 'Atenção');
}

$.mask.definitions['#']='[0123]';
$.mask.definitions['@']='[01]';
$.mask.definitions['&']='[12]';
$.mask.definitions['$']='[012]';
$.mask.definitions['%']='[012345]';

$.datepicker._defaults.dayNamesMin = ['D','S','T','Q','Q','S','S','D']
$.datepicker._defaults.dayNamesShort = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
$.datepicker._defaults.dayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
$.datepicker._defaults.monthNamesShort = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
$.datepicker._defaults.dateFormat = "dd/mm/yy"
$.datepicker._defaults.locale = "pt-br"
$.datepicker._defaults.monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"]
$.datepicker._defaults.closeText = 'Fechar'
$.datepicker._defaults.currentText = 'Hoje'

function checaCPF(cpf, rejeitarZero) {
	if (cpf.length != 14)
		return false

	cpf = cpf.replace(/\./g, '').replace('-','');

	if (rejeitarZero && cpf == "00000000000")
		return false;

	if (cpf.length != 11 ||  cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999")
		return false;

	add = 0;
	for (i=0; i < 9; i ++) add += parseInt(cpf.charAt(i)) * (10 - i);
	rev = 11 - (add % 11);
	if (rev == 10 || rev == 11)rev = 0;
	if (rev != parseInt(cpf.charAt(9))) return false;

	add = 0;
	for (i = 0; i < 10; i ++) add += parseInt(cpf.charAt(i)) * (11 - i);
	rev = 11 - (add % 11);
	if (rev == 10 || rev == 11) rev = 0;
	if (rev != parseInt(cpf.charAt(10))) return false;

	return true;
}

$.blockUI = function () {
    document.getElementById('loading-screen').style.display = "flex";
}

$.unblockUI = function () {
    document.getElementById('loading-screen').style.display = "none";
}

function bloqueiaUI() {
	$.blockUI({
		message:
    `<div id="displayBox">
      <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" fill="#f0c552">
        <circle cx="60" cy="60" r="45" fill="none" stroke="#fceabb" stroke-width="18" opacity="0.5"/>
        <circle cx="60" cy="60" r="45" fill="none" stroke="#f0c552" stroke-width="18" stroke-dasharray="203" stroke-dashoffset="15" stroke-linecap="round">
          <animateTransform
            attributeName="transform"
            type="rotate"
            from="0 60 60"
            to="360 60 60"
            dur="1.2s"
            repeatCount="indefinite" />
        </circle>
      </svg>
    </div>`,
		css: {
			top:  ($(window).height() - 400) /2 + 'px',
			left: ($(window).width() - 400) /2 + 'px',
			width: '400px'
		}
	});
}

function desbloqueiaUI() {
  $.unblockUI();
}

function exibirErroDeValidacao(id, mensagem) {
	swal("Atenção", mensagem);
	id.css("border", "1px solid #f00");
	id.focus();
}

function validaSenha(senha) {
	let tamanhoSenha = senha.length > 5
	let contemLetras = !!senha.match (/[^0-9]/)
	let contemNumeros = !!senha.match (/[0-9]/)
	return (tamanhoSenha && contemLetras && contemNumeros)
}

function naturalizaDiasDaSemana() {
	$('.diasdasemana').each(function() {
		$(this).text(formataDiasDaSemana($(this).text()))
	})
}

function formataDiasDaSemana(dias) {
	const diasDaSemana = {
		domingo: 'Domingo',
		segunda: 'Segunda',
		terca: 'Terça',
		quarta: 'Quarta',
		quinta: 'Quinta',
		sexta: 'Sexta',
		sabado: 'Sábado'
	}
	const diasCompletoArray = Object.keys(diasDaSemana)

	let diasArray = dias.split(',')
	let diasFinal = ''
	let sequencia = null

	if (diasArray.length == 7) return 'Todos os dias'

	diasArray.forEach(function(element, index) {
		let separador = (index+1 == diasArray.length ? ' e ' : ', ')
		diasFinal = (index==0) ? diasDaSemana[element] : diasFinal + separador + diasDaSemana[element]

		let idx = diasCompletoArray.findIndex(function (element) {
			return element === diasArray[index]
		})

		if (sequencia !== false && diasArray[index+1]) {
			sequencia = diasArray[index+1] == diasCompletoArray[idx+1]
		}
	});

	return (sequencia && diasArray.length > 2) ? diasDaSemana[diasArray[0]] + ' a ' + diasDaSemana[diasArray[diasArray.length-1]] : diasFinal
}

function ativaEnter() {
	$('#bodynotas').children('tr').keyup(function (e) {
		if(e.which == 13) {
			$(this).next().find('.camponota').focus();
		}
	})
}

function abrirFichaRapidaProspeccao(id) {
	$.ajax({
		url: '/alunos_prospeccao_ficha_rapida.html',
		beforeSend: bloqueiaUI(),
		success: function (data) {
			if(!$("#prospeccaoDialog").length) {
				$("#conteudo").append(data)
			}
			abrirModalFichaProspeccao(id)
		}
	})
}

const crm = {
	editar: function (id, id_ficha_prospeccao) {
		if ($("input[name*='sem-interesse']").prop('checked')) return

		$.ajax({
			url: "prospeccao_crm.php",
			type: "POST",
			context: $('#recebe-novo-contato'),
			data: {
				id_crm: id,
				id_ficha_prospeccao: id_ficha_prospeccao
			},
			beforeSend: bloqueiaUI(),
			complete: $.unblockUI(),
			success: function (data) {
				$("#dialog-novo-contato").modal("toggle");
				this.html(data);
			}
		});
	},

	remover: function (id, id_ficha_prospeccao) {
		swal({
			title: "Atenção",
			text: "Deseja apagar essa entrada de CRM?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Deletar!",
			cancelButtonText: "Cancelar",
			closeOnConfirm: true
		}, function () {
			$.ajax({
				url: "/dao/prospeccao.php",
				type: "POST",
				context: $('#recebe-novo-contato'),
				data: {
					action: 'removeCRM',
					id_crm: id,
					id_ficha_prospeccao: id_ficha_prospeccao
				},
				beforeSend: bloqueiaUI(),
				complete: $.unblockUI(),
				success: function(data) {
					criaAlerta('success', 'Entrada de CRM removida');
					atualizaTabela()
				},
				error: function(data) {
					criaAlerta('error', 'Falha ao remover entrada de CRM. Tente novamente mais tarde.');
				}
			});
		})
	}
}

function salvarConfig(chave, valor) {
	if (typeof(valor) == "boolean") {
		valor = valor ? '1' : '0';
	}

	return $.ajax({
		url: "dao/config.php",
		type: "POST",
		data: {
			action: "salvaConfig",
			chave: chave,
			valor: valor
		},
		beforeSend: bloqueiaUI,
		complete: $.unblockUI,
		success: function(data) {
			criaAlerta('success', data);
		},
		error: function(xhr) {
			criaAlerta('error', 'Erro ao salvar configurações.');
		}
	});
}

function insereAlerta(tipo, contexto, texto, textoLink, linkLink) {
    tipo = $.trim(tipo);
    $("#" + contexto).prepend('<div id="message-' + tipo + '" ><table border="0" width="100%" cellpadding="0" cellspacing="0"><tr><td class="' + tipo + '-left">' + texto + '<a href="' + linkLink + '">' + textoLink + '</a></td><td class="' + tipo + '-right"><a class="close-' + tipo + '"><img src="images/table/icon_close_' + tipo + '.gif"  id="' + tipo + '" name="' + tipo + '"  alt="" ></a></td></tr></table></div>');

    $("#" + tipo).click(function () {
        $(this).parent().parent().parent().hide(500);
    });

    $("#message-" + tipo).show(500); //.css('display','block'); //
    setTimeout(function () {
        $("#message-" + tipo).fadeOut('slow');
    }, 7000);
    $('html, body').animate({
        scrollTop: $("#" + contexto).offset().top
    }, 1000);
}

function insereAlertaNovo(tipo, contexto, texto, textoLink, linkLink) {
    tipo = $.trim(tipo);
    $("#" + contexto).prepend('<div id="message-' + tipo + '" class="message-' + tipo + '" ><table border="0" width="100%" cellpadding="0" cellspacing="0"><tr><td class="' + tipo + '-left">' + texto + '<a href="' + linkLink + '">' + textoLink + '</a></td><td class="' + tipo + '-right"><a class="close-' + tipo + '"><img src="images/table/icon_close_' + tipo + '.gif"  id="' + tipo + '" name="' + tipo + '"  alt="" ></a></td></tr></table></div>');

    $("#" + tipo).click(function () {
        $(this).parent().parent().parent().hide(500);
    });

    $(".message-" + tipo).show(500); //.css('display','block'); //
    setTimeout(function () {
        $(".message-" + tipo).fadeOut('slow');
    }, 7500);
}

function insereAlertaFixo(tipo, contexto, texto, textoLink, linkLink) {
    tipo = $.trim(tipo);
    $("#" + contexto).prepend('<div id="message-' + tipo + '" ><table border="0" width="100%" cellpadding="0" cellspacing="0"><tr><td class="' + tipo + '-left">' + texto + '<a href="' + linkLink + '">' + textoLink + '</a></td><td class="' + tipo + '-right"><a class="close-' + tipo + '"><img src="images/table/icon_close_' + tipo + '.gif"  id="' + tipo + '" name="' + tipo + '"  alt="" ></a></td></tr></table></div>');

    $("#" + tipo).click(function () {
        $(this).parent().parent().parent().hide(500);
    });
    $("#message-" + tipo).show(500); //.css('display','block'); //
    $('html, body').animate({
        scrollTop: $("#" + contexto).offset().top
    }, 1000);
}

function number_format(number, decimals, dec_point, thousands_sep) {
    // %        nota 1: Para 1000.55 retorna com precisão 1 no FF/Opera é 1,000.5, mas no IE é 1,000.6
    // *     exemplo 1: number_format(1234.56);
    // *     retorno 1: '1,235'
    // *     exemplo 2: number_format(1234.56, 2, ',', ' ');
    // *     retorno 2: '1 234,56'
    // *     exemplo 3: number_format(1234.5678, 2, '.', '');
    // *     retorno 3: '1234.57'
    // *     exemplo 4: number_format(67, 2, ',', '.');
    // *     retorno 4: '67,00'
    // *     exemplo 5: number_format(1000);
    // *     retorno 5: '1,000'
    // *     exemplo 6: number_format(67.311, 2);
    // *     retorno 6: '67.31'

    var n = number, prec = decimals;
    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
    var dec = (typeof dec_point == "undefined") ? '.' : dec_point;

    var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;

    var abs = Math.abs(n).toFixed(prec);
    var _, i;

    if (abs >= 1000) {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;

        _[0] = s.slice(0, i + (n < 0)) +
                _[0].slice(i).replace(/(\d{3})/g, sep + '$1');

        s = _.join(dec);
    } else {
        s = s.replace('.', dec);
    }

    return s;
}

function buildOptions(url, element, config) {
    $.ajax({
        url: url,
        dataType: 'json',
        success: function (itens) {
            const isDisabled = config.nullable ? '' : 'disabled'
            var options = '<option value="" selected ' + isDisabled + '>' + config.placeholder || 'Selecione uma opção' + '</option>';

            itens.forEach(function(item) {
                var value = config.value(item) || item.id || '';
                var text = config.text(item) || item.name || '';
                options += "<option value=\'" + value + "\'>" + text + "</option>";
            });

            element.innerHTML = options;
        }
    });
}

function copiarTabelaParaPlanilha(el) {
  var body = document.body, range, sel;
  if (document.createRange && window.getSelection) {
      range = document.createRange();
      sel = window.getSelection();
      sel.removeAllRanges();
      try {
          range.selectNodeContents(el);
          sel.addRange(range);
      } catch (e) {
          range.selectNode(el);
          sel.addRange(range);
      }
      document.execCommand("copy");
      sel.removeAllRanges();
  } else if (body.createTextRange) {
      range = body.createTextRange();
      range.moveToElementText(el);
      range.select();
      range.execCommand("Copy");
  }
}

// Add hover functionality to user login menu
var userMenu = document.querySelector('.user-options-outer');
if (userMenu) {
  var userSubmenu = userMenu.querySelector('.user-options');
  var userTimeoutId;
  var userShowDelayId;

  userMenu.addEventListener('mouseenter', function () {
    clearTimeout(userTimeoutId);
    userShowDelayId = setTimeout(function () {
      userSubmenu.classList.add('active');
      userMenu.classList.add('active');
    }, 150);
  });

  userMenu.addEventListener('mouseleave', function () {
    clearTimeout(userShowDelayId);
    userTimeoutId = setTimeout(function () {
      userSubmenu.classList.remove('active');
      userMenu.classList.remove('active');
    }, 300);
  });

  // Add event listener for mouseenter on submenu to cancel hiding
  userSubmenu.addEventListener('mouseenter', function () {
    clearTimeout(userTimeoutId);
  });
}
