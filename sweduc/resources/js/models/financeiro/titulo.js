import Axios from 'axios'
import Model from '../model'

class Titulo extends Model {
    static endpoint() {
        return '/api/v1/financeiro/contas-a-receber'
    }
}

export default Titulo;
