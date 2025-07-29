import Model from '../model'
import Axios from 'axios'

class Logo extends Model {
  static uploadFromForm(formElement) {
    const formData = new FormData(formElement)

    return Axios({
        method: 'POST',
        url: `/config/logo`,
        data: formData
    })
}
}

export default Logo;
