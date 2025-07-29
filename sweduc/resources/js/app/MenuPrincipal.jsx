import React, { Component} from "react";
import router from '../router'
import "./MenuPrincipal.scss";
import { CSSTransitionGroup } from 'react-transition-group'

class MenuPrincipal extends Component{
    constructor(props) {
        super(props)
        this.state = {
            nomeRotaAtiva: 'alunos',
            active: false
        }
    }

    select(nomeRotaAtiva) {
        this.setState({ nomeRotaAtiva })

        if (nomeRotaAtiva == 'alunos') {
            router.carregarUrl('/alunos_busca.php');
        }
    }

    stateClass(route) {
        return route == this.state.nomeRotaAtiva ? 'active' : ''
    }

    abrirMenu() {
        this.setState({ active: true })
    }

    fecharMenu() {
        this.setState({ active: false })
    }

    render() {
        return(
            <div className="menu">
                <CSSTransitionGroup
                    transitionName="example"
                    transitionEnterTimeout={8000}
                    transitionLeaveTimeout={5000}
                >
                    <nav className={`drawer ${this.state.active ? 'd-flex flex-column ' : 'd-none'}`}>
                        <div className="mx-auto text-center py-4">
                            <img src="/images/logo-sweduc.png" alt=""/>
                        </div>

                        <ul className="nav nav-pills nav-fill flex-column">
                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('alunos')}`} href="#" onClick={() => this.select('alunos')}>
                                    Alunos
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('marketing')}`} href="#" onClick={() => this.select('marketing')}>
                                    Marketing
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('acadêmico')}`} href="#" onClick={() => this.select('acadêmico')}>
                                    Acadêmico
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('financeiro')}`} href="#" onClick={() => this.select('financeiro')}>
                                    Financeiro
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('estoque')}`} href="#" onClick={() => this.select('estoque')}>
                                    Estoque
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('configurações')}`} href="#" onClick={() => this.select('configurações')}>
                                    Configurações
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('sistema')}`} href="#" onClick={() => this.select('sistema')}>
                                    Sistema
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('contato')}`} href="#" onClick={() => this.select('contato')}>
                                    Contato
                                </a>
                            </li>

                            <li className="nav-item">
                                <a className={`nav-link ${this.stateClass('recarregar')}`} href="#" onClick={() => this.select('recarregar')}>
                                    Recarregar
                                </a>
                            </li>
                        </ul>

                        <div className="w-100 mt-auto">
                            <button className="btn btn-danger btn-block my-1" onClick={() => this.fecharMenu()}>
                                Fechar menu
                            </button>
                        </div>
                    </nav>
                </CSSTransitionGroup>

                <CSSTransitionGroup
                    transitionName="example"
                    transitionEnterTimeout={8000}
                    transitionLeaveTimeout={5000}
                >
                    <div className={`drawer-shadow ${this.state.active || 'd-none'}`} onClick={() => this.fecharMenu()}/>
                </CSSTransitionGroup>

                <div className="w-100">
                    <button className="btn btn-primary m-3" onClick={() => this.abrirMenu()}>
                        Abrir menu
                    </button>
                </div>
            </div>
        );
    }
}

export default MenuPrincipal;
