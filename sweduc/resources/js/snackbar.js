import Snackbar from 'node-snackbar'
import 'node-snackbar/dist/snackbar.min.css'

export default function (params) {
    return Snackbar.show({
        pos: 'top-right',
        actionText: 'Fechar',
        ...params
    })
}
