import Model from '../model'

class CondicaoDeParcelamento extends Model {
    static endpoint() {
        return '/api/v1/financeiro/condicao-de-parcelamento'
    }
}

export default CondicaoDeParcelamento;
