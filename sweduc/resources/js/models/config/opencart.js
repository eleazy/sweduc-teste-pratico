import Model from '../model'

class Opencart extends Model {
    static endpoint() {
        return '/api/v1/config/opencart'
    }
}

export default Opencart;
