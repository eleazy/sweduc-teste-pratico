import Model from '../model'
import Axios from 'axios'

class Excel extends Model {
  static uploadFromForm(formElement) {
    const formData = new FormData(formElement)

    return Axios({
        method: 'POST',
        url: `/financeiro/importar-excel/importar`,
        data: formData
    })
}
}

export default Excel;
