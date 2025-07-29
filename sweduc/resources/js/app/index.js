import router from '../router'
import Matricula from '../models/academico/matricula'
import Conta from '../models/financeiro/conta'
import Titulo from '../models/financeiro/titulo'
import Remessa from '../models/financeiro/remessa'
import Excel from '../models/financeiro/excel'
import Opencart from '../models/config/opencart'
import Ocorrencia from '../models/config/ocorrencia'
import Logo from '../models/config/logo'
import CondicaoDeParcelamento from '../models/financeiro/condicaoDeParcelamento'
import Interacoes from '../interacoes'
import VMasker from 'vanilla-masker'
import * as React from 'react';
import { render } from 'react-dom';
import App from './App.jsx';

import '../../css/tailwind.scss'
import '../../css/legacy.scss'

global.VMasker = VMasker
global.sweduc = router

global.Academico = {
    Matricula,
}

global.Financeiro = {
    Conta,
    Titulo,
    CondicaoDeParcelamento,
    Remessa,
    Excel,
}

global.Config = {
    Opencart,
    Ocorrencia,
    Logo
}

global.Interacoes = Interacoes
global.displaySuccess = Interacoes.SucessoXhrPadrao
global.displayError = Interacoes.ErroXhrPadrao

render(<App />, document.getElementById('app'));
