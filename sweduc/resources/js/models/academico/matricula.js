import Model from "../model";

/**
 * Contas bancárias
 */
class Matricula extends Model {
    static endpoint() {
        return '/api/v1/academico/matricula'
    }
}

export default Matricula;
