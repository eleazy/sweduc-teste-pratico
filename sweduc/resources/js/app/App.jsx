import React, { Component } from "react";
import {
  HashRouter as Router, Route, Switch
} from "react-router-dom";
import DocumentosRematricula from "./Page/DocumentosRematricula.jsx";
import EditorDeDocumentos from "./Page/EditorDeDocumentos.jsx";
import NotasInconsistentes from "./Page/NotasInconsistentes.jsx";
import Rematricula from "./Page/Rematricula.tsx";
import Signatarios from "./Page/Signatarios.jsx";
import HistoricoDeRematricula from "./Page/HistoricoDeRematricula.jsx";
import TransferenciaDeNotas from "./Page/TransferenciaDeNotas.jsx";


export default class App extends Component {
  render() {
    return(
      <Router>
        {/*
          A <Switch> looks through all its children <Route>
          elements and renders the first one whose path
          matches the current URL. Use a <Switch> any time
          you have multiple routes, but you want only one
          of them to render at a time
        */}
        <Switch>
          <Route path="/notas-inconsistentes" component={NotasInconsistentes} />
          <Route path="/editor-de-documentos/:id" component={EditorDeDocumentos} />
          <Route path="/documentos-rematricula" component={DocumentosRematricula} />
          <Route path="/rematricula" component={Rematricula} />
          <Route path="/signatarios" component={Signatarios} />
          <Route path="/historico-de-rematricula" component={HistoricoDeRematricula} />
          <Route path="/transferencia-de-notas" component={TransferenciaDeNotas} />
        </Switch>
      </Router>
    );
  }
}
