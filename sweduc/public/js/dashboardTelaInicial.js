const chartMoneyFormatter = new Intl.NumberFormat("pt-BR", {
  style: "currency",
  currency: "BRL",
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

function fDate(date) {
  return date.split("-").reverse().join("/");
}

const monthNames = [
  "Janeiro",
  "Fevereiro",
  "Março",
  "Abril",
  "Maio",
  "Junho",
  "Julho",
  "Agosto",
  "Setembro",
  "Outubro",
  "Novembro",
  "Dezembro",
];

const chartInstances = {};

function renderDashboard(i, data) {
  var dataInicio = data["datainicio"] || "";
  var dataFim = data["datafim"] || "";
  var RRFesperado = Number(data["RRFvalorEsperado"]) || 0;
  var RRFrecebido = Number(data["RRFvalorRecebido"]) || 0;
  var RIrecebido = Number(data["RIvalorRecebido"]) || 0;
  var RIfaltando = Number(data["RIvalorAberto"]) || 0;
  var LPrecebido = Number(data["LPvalorRecebido"]) || 0;
  var LPgasto = Number(data["LPvalorGasto"]) || 0;
  const nomeUnidade = data["unidadeNome"];
  var periodosMedias = data["periodosMedias"] || [];
  var series = data["series"] || [];
  var medias = data["medias"] || [];
  var novasMatriculas = data["matriculasnovas"] || [];
  var rematriculas = data["rematriculas"] || [];
  var totalMatriculas = novasMatriculas.map((m, i) => m + rematriculas[i]);
  var years = data["years"] || [];
  years = rematriculas.every((n) => n == -4)
    ? years.map((y) => monthNames[Number(y.split("-")[1]) - 1])
    : years.map((y) => y.split("-")[0]);

  // Lógica para mostrar período de calculo das médias
  var dashBoardMediasTitle = periodosMedias.every(
    (p) => p === periodosMedias[0]
  )
    ? `Média por Série, ${periodosMedias[0]}`
    : `Média por Série`;

  // Create the main container div
  const dashboardTelaInicial = document.createElement("div");
  dashboardTelaInicial.id = "dashboardTelaInicial";
  dashboardTelaInicial.innerHTML = nomeUnidade
    ? ` <div id = "dashboardTitle"> <h2> ${nomeUnidade} </h2> </div> `
    : "";

  const dashboardsDiv = document.createElement("div");
  dashboardsDiv.id = "dashboardsWrapper";

  const titles = [
    "Gráfico Faturado x Recebido",
    "Gráfico de inadimplência",
    "DRE",
    dashBoardMediasTitle,
    "Evolução de matrículas",
  ];
  const info = [
    `Na coluna "Faturado" exibe o somatório de todos os títulos emitidos com vcto no período selecionado pelo valor previsto.

    Na coluna "Recebidos" exibe o somatório de todos os títulos recebidos com vcto no período selecionado pelo valor recebido. (${fDate(
      dataInicio
    )} - ${fDate(dataFim)})`,
    `Na coluna "Recebidos" exibe o somatório de todos os títulos recebidos com vcto no período selecionado, pelo valor recebido.

      Na coluna "Aberto" exibe o somatório de todos os títulos em aberto com vcto no período selecionado, pelo valor bruto. (${fDate(
        dataInicio
      )} - ${fDate(dataFim)})`,
    `Na coluna "Recebido" exibe o somatório de todos os títulos recebidos no período selecionado, pelo valor recebido.

    Na coluna "Pagamentos" exibe o somatório de todos os pagamentos efetuados no período selecionado, pelo valor pago. (${fDate(
      dataInicio
    )} - ${fDate(dataFim)}).`,
    "Mostra a média total da serie, de acordo com as turmas, disciplinas, ano e período selecionados ",
    "Mostra a evolução de matrículas e rematrículas ao longo dos anos",
  ];

  // Create dashboard divs
  for (var j = 0; j < 5; j++) {
    // Condições para não mostrar gráficos vazios
    if (
      [0, 1].includes(j) &&
      [RRFesperado, RRFrecebido, RIfaltando, RIrecebido].reduce(
        (a, b) => a + b
      ) == 0
    ) {
      continue;
    }
    if (j == 2 && [LPrecebido, LPgasto].reduce((a, b) => a + b) == 0) {
      continue;
    }
    if (j == 3 && series.length == 0) {
      continue;
    }
    if (j == 4 && years.length == 0) {
      continue;
    }

    const dashboardDiv = document.createElement("div");
    dashboardDiv.classList.add("dashboardDiv");
    dashboardDiv.classList.add(`dashboard${i}_${j}`);

    const dashboardTitle = document.createElement("h1");
    dashboardTitle.textContent = titles[j];

    const tooltip = document.createElement("div");
    tooltip.classList.add("dashboardTitleTooltip");
    tooltip.textContent = `${info[j]}`;

    dashboardTitle.addEventListener("mouseover", (event) => {
      tooltip.style.display = "block";
      tooltip.style.left = `${event.pageX - 150}px`;
      tooltip.style.top = `${event.pageY - 50}px`;
    });

    dashboardTitle.addEventListener("mouseout", () => {
      tooltip.style.display = "none";
    });

    //dashboardDiv.append();
    const dashCanvas = document.createElement("canvas");
    dashboardDiv.append(dashboardTitle, tooltip, dashCanvas);
    dashboardsDiv.append(dashboardDiv);
  }

  // Append financeiro and pedagogico sections to dashboardTelaInicial
  dashboardTelaInicial.append(dashboardsDiv);

  // Append dashboardTelaInicial to dashboardWrapper
  const dashboardWrapper = document.querySelector("#dashboardOuter");
  dashboardWrapper.append(dashboardTelaInicial);

  // Cores
  var c1 = "rgba(255, 187, 56, 0.8)";
  var c2 = "rgba(255, 211, 77, 0.8)";
  var c3 = "rgba(255, 230, 171, 0.8)";
  var c4 = "rgba(247, 225, 26, 0.9)";
  var c5 = "rgba(204, 142, 27, 0.8)";

  if (
    [RRFesperado, RRFrecebido, RIfaltando, RIrecebido].reduce((a, b) => a + b) >
    0
  ) {
    // Gráfico relação entre faturado e recebido
    const canvasId1 = `.dashboard${i}_0 canvas`;
    const ctx1 = document.querySelector(canvasId1).getContext("2d");

    if (chartInstances[canvasId1]) chartInstances[canvasId1].destroy();
    chartInstances[canvasId1] = new Chart(ctx1, {
      type: "bar",
      data: {
        labels: ["Faturado", "Recebidos"],
        datasets: [
          {
            label: "Faturado x Recebido",
            data: [RRFesperado, RRFrecebido],
            backgroundColor: [c1, c3],
            borderColor: "#b0b0b0",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
        plugins: {
          legend: {
            align: "start",
            labels: {
              boxWidth: 15,
            },
          },
        },
      },
    });

    // Gráfico quantia a ser recebida e faltando
    const canvasId2 = `.dashboard${i}_1 canvas`;
    const ctx2 = document.querySelector(canvasId2).getContext("2d");

    if (chartInstances[canvasId2]) chartInstances[canvasId2].destroy();
    chartInstances[canvasId2] = new Chart(ctx2, {
      type: "pie",
      data: {
        labels: [
          `Abertos: ${chartMoneyFormatter.format(RIfaltando)}`,
          `Recebidos: ${chartMoneyFormatter.format(RIrecebido)}`,
        ],
        datasets: [
          {
            label: "Percentual",
            data: [
              ((RIfaltando / (RIfaltando + RIrecebido)) * 100).toFixed(2),
              ((RIrecebido / (RIfaltando + RIrecebido)) * 100).toFixed(2),
            ],
            backgroundColor: [c3, c1],
            hoverBackgroundColor: ["#ffffff", "#ffffff"],
            borderColor: ["#b0b0b0"],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            align: "start",
            labels: {
              boxWidth: 15,
            },
          },
        },
      },
    });
  }
  if (LPrecebido > 0 || LPgasto > 0) {
    // Gráfico relação entre recebido e gasto
    const canvasId3 = `.dashboard${i}_2 canvas`;
    const ctx3 = document.querySelector(canvasId3).getContext("2d");

    if (chartInstances[canvasId3]) chartInstances[canvasId3].destroy();
    chartInstances[canvasId3] = new Chart(ctx3, {
      type: "bar",
      data: {
        labels: ["Recebidos", "Pagamentos", "Lucro/Prejuízo"],
        datasets: [
          {
            label: "DRE",
            data: [LPrecebido, LPgasto, LPrecebido - LPgasto],
            backgroundColor: [c1, c2, c3],
            borderColor: "#b0b0b0",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
        plugins: {
          legend: {
            align: "start",
            labels: {
              boxWidth: 15,
            },
          },
        },
      },
    });
  }

  //Grafico de linha, pedagógico, média por série
  if (series.length > 0) {
    const canvasId4 = `.dashboard${i}_3 canvas`;
    const ctx4 = document.querySelector(canvasId4).getContext("2d");

    if (chartInstances[canvasId4]) chartInstances[canvasId4].destroy();
    chartInstances[canvasId4] = new Chart(ctx4, {
      type: "line",
      data: {
        labels: series,
        datasets: [
          {
            label: "Média",
            data: medias.map((m) => Number(m)),
            fill: false,
            borderColor: c1,
            tension: 0.1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            align: "start",
            labels: {
              boxWidth: 15,
            },
          },
        },
      },
    });
  }

  if (years.length > 0) {
    //Grafico de linha, evolução de matriculas e rematriculas
    const canvasId5 = `.dashboard${i}_4 canvas`;
    const ctx5 = document.querySelector(canvasId5).getContext("2d");

    const datasets = [];
    if (totalMatriculas.length > 0 && rematriculas.some((m) => m != -4)) {
      datasets.push({
        label: "Total de Matrículas",
        data: totalMatriculas.map((m) => Number(m)),
        fill: false,
        borderColor: c4,
        tension: 0.1,
      });
    }
    if (rematriculas.length > 0 && rematriculas.some((m) => m != -4)) {
      datasets.push({
        label: "Rematrículas",
        data: rematriculas.map((m) => Number(m)),
        fill: false,
        borderColor: c1,
        tension: 0.1,
      });
    }
    if (novasMatriculas.length > 0) {
      datasets.push({
        label: "Novas Matrículas",
        data: novasMatriculas.map((m) => Number(m)),
        fill: false,
        borderColor: c5,
        tension: 0.1,
      });
    }

    if (chartInstances[canvasId5]) chartInstances[canvasId5].destroy();
    chartInstances[canvasId5] = new Chart(ctx5, {
      type: "line",
      data: {
        labels: years,
        datasets: datasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            align: "start",
            labels: {
              padding: 14,
              boxWidth: 15,
            },
          },
        },
      },
    });
  }
}
