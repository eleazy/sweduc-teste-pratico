import Axios from 'axios'
import router from './router'
import snackbar from './snackbar'

export default {
    criarGrupo(event) {
        console.log('Criando perfil')
        event.target

        // Axios.post('/')

        debugger
    },

    salvarPermissoes(event) {
        const formulario = event.target
        const formData = new FormData(formulario)
        const grupoId = formData.get('grupoId')

        Axios
            .post(`/config/politicas/funcionarios/${grupoId}/salvar`, formData)
            .then(() => {
                snackbar({text: 'Salvo com sucesso'});
                router.carregarUrl('config/politicas/funcionarios')
            }).catch(() => {
                snackbar({text: 'Houve um erro.'});
            })
    }
}
