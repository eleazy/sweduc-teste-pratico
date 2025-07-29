import Model from "../model";

/**
 * Contas bancárias
 */
class Conta extends Model {
    static endpoint() {
        return '/api/v1/financeiro/conta'
    }

    static fromForm(formElement) {
        const formData = new FormData(formElement)
        const data = Object.fromEntries(formData)
        const conta = Object.assign(new Conta(data.idcontasbanco), data)
        return conta
    }
}

export default Conta;
