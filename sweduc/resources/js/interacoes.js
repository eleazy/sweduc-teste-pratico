
export default {
    SucessoXhrPadrao: function (data) {
        let msg = null;

        try {
            const parsedData = JSON.parse(data)
            msg = parsedData.msg ||
                  parsedData.message
        } catch (error) {
            console.error(error)
        }

        criaAlerta('success', msg || 'Operação realizada com sucesso')
    },

    ErroXhrPadrao: function (xhr) {
        let msg = null;

        try {
            const parsedData = JSON.parse(xhr.responseText)
            msg = parsedData.msg ||
                  parsedData.message ||
                  parsedData.erro ||
                  parsedData.error
        } catch (error) {
            console.error(error)
        }

        criaAlerta('error', msg || 'Houve um erro na operação.');
    },

    SucessoAxiosPadrao: function (data) {
        let msg = null;

        try {
            msg = data.msg || data.message
        } catch (error) {
            console.error(error)
        }

        criaAlerta('success', msg || 'Operação realizada com sucesso')
    },

    ErroAxiosPadrao: function (erro) {
        let msg = null;

        try {
            const parsedData = erro.response.data
            msg = parsedData.msg ||
                  parsedData.message ||
                  parsedData.erro ||
                  parsedData.error
        } catch (error) {
            console.error(error)
        }

        criaAlerta('error', msg || 'Houve um erro na operação.');
    },

    ConfirmarExclusao: function (sujeito, callback) {
        swal({
            title: "Atenção",
            text: `Deseja apagar ${sujeito}?`,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Deletar!",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true
        }, callback)
    }
}
