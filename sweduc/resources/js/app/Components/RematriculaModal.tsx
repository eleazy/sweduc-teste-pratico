import { Dialog, Transition } from '@headlessui/react'
import axios from 'axios'
import React, { FormEvent, Fragment, useEffect, useState } from 'react'
import { SerieFinanceiro } from '../../interfaces/serie-financeiro';

interface RematriculaModalProps {
    unidade: string|Number,
    alunos: any[],
    show: boolean,
    closeFn: (value: boolean) => void,
}

function submitRematricula(event: FormEvent, alunos: any[], form: Object, closeFn: (value: boolean) => void) {
  event.preventDefault();

  axios.post(`/api/v1/academico/rematriculas/gerar-matriculas`, {
    rematriculas: alunos,
    ...form,
  });

  closeFn(true);
}

export default function RematriculaModal({ unidade, alunos, show, closeFn }: RematriculaModalProps) {
  const [descontoNoBoleto, setDescontoNoBoleto] = useState(true);
  const [pagamentoComCartao, setPagamentoComCartao] = useState(true);

  const [serieFinanceiro, setSerieFinanceiro] = useState<SerieFinanceiro>();
  const [serieFinError, setSerieFinError] = useState<string>('');

  useEffect(() => {
    axios
      .post('/api/v1/financeiro/getSerieFinanceiro', { alunos })
      .then(response => {
        setSerieFinanceiro(() => response.data );
      })
        .catch(error => {
            setSerieFinError(() => error.response.data.error);
        });
  }, []);


  setTimeout(() => {
    $(".vmask-money").maskMoney({
      prefix:'R$ ',
      allowNegative: true,
      thousands:'.',
      decimal:',',
      affixesStay: false
    });
  });

  return (
    <Transition appear show={show} as={Fragment}>
      <Dialog
        as="div"
        className="fixed inset-0 z-50 overflow-y-auto"
        onClose={closeFn}
      >
        <div className="min-h-screen px-4 text-center bg-black bg-opacity-80">
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-300"
            enterFrom="opacity-0"
            enterTo="opacity-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <Dialog.Overlay className="fixed inset-0" />
          </Transition.Child>

          {/* This element is to trick the browser into centering the modal contents. */}
          <span
            className="inline-block h-screen align-middle"
            aria-hidden="true"
          >
            &#8203;
          </span>
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-300"
            enterFrom="opacity-0 scale-95"
            enterTo="opacity-100 scale-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100 scale-100"
            leaveTo="opacity-0 scale-95"
          >
            <div className="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
              <Dialog.Title
                as="h3"
                className="text-xl font-bold leading-6 text-blue-900 mt-1"
              >
                Promoção de alunos
              </Dialog.Title>
                {
                    serieFinError !== '' ? (
                        <div className="alert alert-danger"> { serieFinError } </div>
                    )
                    :
                    (
                        <form onSubmit={(event: FormEvent) => submitRematricula(event, alunos, {
                            descontoNoBoleto,
                            pagamentoComCartao
                          }, closeFn)}>
                            <div className="mt-3">
                                <div className='mb-2'>
                                    <p id="evento-financeiro" className='mb-0'>
                                    Evento financeiro
                                    </p>
                                    <h4> { serieFinanceiro?.eventoFinanceiro }</h4>
                                </div>

                                <div className='mb-2'>
                                    <p id="conta" className='mb-0'>
                                    Receber na conta/caixa
                                    </p>
                                    <h4> { serieFinanceiro?.conta }</h4>
                                </div>

                                <div className='mb-2'>
                                    <p id="qtd-parcelas" className='mb-0'>
                                    Quantidade de parcelas
                                    </p>
                                    <h4> { serieFinanceiro?.quantidadeParcelas } </h4>
                                </div>

                                <div className='mb-2'>
                                    <p id="valor-parcelas" className='mb-0'>
                                    Valor das parcelas (R$)
                                    </p>

                                    <h4> { serieFinanceiro?.valorParcelas } </h4>
                                </div>

                                <div className='mb-2'>
                                    <p id="data-primeiro-vencimento" className='mb-0'>
                                    Data do 1º Vencimento
                                    </p>

                                    <h4> { serieFinanceiro?.dataPrimeiroVenc } </h4>
                                </div>

                                <label htmlFor="desconto-boleto" className='block'>
                                    <input
                                    type="checkbox"
                                    name="desconto-boleto"
                                    id="desconto-boleto"
                                    className='mr-1'
                                    checked={descontoNoBoleto}
                                    onChange={event => setDescontoNoBoleto(event.target.checked)}
                                    />

                                    Recebe desconto no boleto
                                </label>

                                <label htmlFor="recebe-com-cartao" className='block'>
                                    <input
                                    type="checkbox"
                                    name="recebe-com-cartao"
                                    id="recebe-com-cartao"
                                    className='mr-1'
                                    checked={pagamentoComCartao}
                                    onChange={event => setPagamentoComCartao(event.target.checked)}
                                    />

                                    Recebe com cartão
                                </label>
                                </div>
                                <div className="text-center mt-4 space-x-2 space-y-2">
                                <button
                                    type="submit"
                                    className="sw-btn sw-btn-primary text-sm"
                                >
                                    Gerar matrículas!
                                </button>
                            </div>
                        </form>
                    )
                }
            </div>
          </Transition.Child>
        </div>
      </Dialog>
    </Transition>
  )
}
