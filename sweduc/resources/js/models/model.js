import Axios from 'axios'

class Model {
    constructor(id) {
        this.id = id;
    }

    criar() {
        return Axios({
            method: 'POST',
            url: `${this.constructor.endpoint()}`,
            data: this
        });
    }

    salvar() {
        return Axios({
            method: 'PUT',
            url: `${this.constructor.endpoint()}/${this.id}`,
            data: this
        });
    }

    deletar() {
        return Axios({
            method: 'DELETE',
            url: `${this.constructor.endpoint()}/${this.id}`,
        });
    }

    static deletar(id) {
        return Axios({
            method: 'DELETE',
            url: `${this.endpoint()}/${id}`,
        });
    }

    static endpoint() {
        throw new Error('Endpoint do modelo não implementada.')
    }

    static fromForm(formElement, id = null) {
        const formData = new FormData(formElement)
        const data = Object.fromEntries(formData)
        return Object.assign(new this(id), data)
    }
}

export default Model;
