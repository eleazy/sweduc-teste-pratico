import axios from "axios";

const router = {
  lastRoute: '',

  /**
   * Carrega um fragmento de view HTML na pagina
   * @param {string} url Endereço do fragmento de html
   * @param {string|JQuery|HTMLElement} seletor Elemento no qual conteudo deve ser inserido
   * @returns Promise interface, atualmente o jquery ajax mas pode ser substituido pelo axios
   */
  carregarUrl(url, seletor = null) {
    if (!seletor) {
      this.lastRoute = url
      window.location = `/#/` + url.replace(/^(\/)/, '')
      seletor = '#conteudo'
    }

    return $.ajax({
      url,
      type: 'GET',
      beforeSend: bloqueiaUI,
      complete: $.unblockUI,
      success: data => {
          $(seletor).html(data)
      }
    });
  },

  /**
   * Carrega um fragmento de view HTML na pagina
   * @param {string} url Endereço do fragmento de html
   * @param {string|JQuery|HTMLElement} seletor Elemento no qual conteudo deve ser inserido
   * @returns Promise interface, atualmente o jquery ajax mas pode ser substituido pelo axios
   */
   carregarForm(form, seletor) {
    if (!seletor) {
      this.lastRoute = url
      window.location = `/#/` + url.replace(/^(\/)/, '')
      seletor = '#conteudo'
    }

    const formData = new FormData(form);

    bloqueiaUI()

    return axios({
      method: form.method,
      url: form.action,
      data: formData,
    })
      .then(response => {
        $(seletor).html(response.data)
      })
      .finally($.unblockUI)
  },

  /**
   * Modifica url e esvazia conteudo padrão dando espaço para carregamento de componentes react
   *
   * @param {string} url Rota do componente React
   */
  carregarView(url) {
    window.location = `/#/` + url.replace(/^(\/)/, '')
    document.getElementById('conteudo').innerHTML = null
  },

  /**
   * Carrega uma pagina a partir do href do elemento ancora do evento
   *
   * @param {Event} event Endereço do fragmento de html
   */
  a(event) {
    event = event || window.event

    if (!event) {
      throw new Error('Parametro de função "evento" não encontrado.');
    }

    if (!event.target || !event.target.href) {
      throw new Error('Atributo href não encontrado');
    }

    event.preventDefault()
    const href = event.target.href
    this.carregarUrl(href)
  },

  /**
   * Recarrega a url previamente acessada
   */
  r() {
    this.carregarUrl(this.lastRoute)
  },

  logout() {
    if (confirm("Confirma sair ?") == true) {
      location.href = '/logout';
    }
  }
}

export default router
