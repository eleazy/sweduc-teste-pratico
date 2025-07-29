import validator from 'card-validator'
import VMasker from 'vanilla-masker'

const formPagamento = document.getElementById('form-pagamento')
const cardNumber = document.getElementById('cartao-numero')
VMasker(cardNumber).maskPattern("9999 9999 9999 9999")

function validarCartao() {
    const card = validator.number(cardNumber.value)
    const type = card.card ? card.card.type : null;
    // const allowedTypes = (type == 'visa' || type == 'mastercard')
    const allowedTypes = true

    if (!allowedTypes) {
        alert('Essa bandeira não é suportada nesse estabelecimento!')
        return false;
    }

    if (card.isPotentiallyValid || allowedTypes) {
        cardNumber.classList.remove('is-invalid')
    }

    if (!card.isPotentiallyValid || !allowedTypes) {
        cardNumber.classList.add('is-invalid')
        return false;
    }
}

cardNumber.onkeypress = validarCartao
cardNumber.onchange = validarCartao
formPagamento.onsubmit = validarCartao
