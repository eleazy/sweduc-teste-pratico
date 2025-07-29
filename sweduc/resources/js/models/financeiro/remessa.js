
import Axios from 'axios'

/**
 * Remessas bancárias
 */
class Remessa {
    static uploadFromForm(formElement) {
        const formData = new FormData(formElement)

        return Axios({
            method: 'POST',
            url: `/financeiro_retorno_upload.php`,
            data: formData
        })
    }
}

export default Remessa;
