import Model from '../model'

class Ocorrencia extends Model {
    static endpoint() {
        return '/api/v1/config/ocorrencia'
    }
}

export default Ocorrencia;
