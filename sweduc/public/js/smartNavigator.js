const goToRoute = (route) => {
  sweduc.carregarUrl(route);
  d.getElementById("navigator-chat").classList.add("hidden");
  d.querySelector(".navigator-search-input").value = "";
  d.querySelector(".navigator-chat-content").innerHTML = "";
};

const getMatch = (searchTerm) => {
  searchTerm = searchTerm.replace(/[^a-zA-ZÀ-ÿ\s]/g, "");

  d.querySelector(".navigator-chat-content").innerHTML = "";

  const url = "/local/nlu";
  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      text: searchTerm,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.auth === false) {
        criaAlerta(
          "error",
          "Seu perfil não tem permissão para acessar esse recurso."
        );
        return;
      }

      const route = data.route;
      if (route && route !== "No match over 60") {
        goToRoute(route);
      } else if (data.other_routes && data.other_routes.length > 0) {
        Object.values(data.other_routes).forEach((route) => {
          const option = d.createElement("div");
          option.className = "option";
          option.textContent = route.intent;
          option.onclick = () => {
            goToRoute(route.route);
          };
          d.querySelector(".navigator-chat-content").appendChild(option);
        });
      } else {
        const noMatch = d.createElement("div");
        noMatch.className = "option";
        noMatch.textContent = "Nenhum resultado encontrado.";
        d.querySelector(".navigator-chat-content").appendChild(noMatch);
        criaAlerta("error", `"${searchTerm}" não localizado.`);
      }
    })
    .catch((error) => {
      console.error("Erro ao buscar rota:", error);
    })
    .finally(() => {
      d.querySelector("#snav-mic-icon").classList.remove("pulsing");
      d.querySelector("#snav-mic-icon").classList.add("hidden");
    });
};

async function recordAndTranscribe() {
  const cannotProcess = await dailyLimiteReached();
  if (cannotProcess) {
    criaAlerta(
      "error",
      "Limite diário de comandos de voz atingido. Tente novamente amanhã."
    );
    return;
  }

  d.querySelector("#snav-mic-icon").classList.add("pulsing");
  d.querySelector("#snav-mic-icon").classList.remove("hidden");

  const stream = await navigator.mediaDevices.getUserMedia({
    audio: true,
  });
  const mediaRecorder = new MediaRecorder(stream);

  const chunks = [];
  mediaRecorder.ondataavailable = (e) => chunks.push(e.data);

  mediaRecorder.onstop = async () => {
    const blob = new Blob(chunks, {
      type: "audio/wav",
    });
    await transcribeAudio(blob);
  };

  mediaRecorder.start();

  const segundos = await recordingSeconds();
  setTimeout(() => mediaRecorder.stop(), segundos * 1000);
}

async function transcribeAudio(blob) {
  const formData = new FormData();
  formData.append("audio", blob, "audio.wav");

  const url = "https://sttmid.webtoolssistemas.com.br/transcribe";
  const response = await fetch(url, {
    method: "POST",
    body: formData,
  }).catch((error) => {
    console.error("Erro ao enviar áudio:", error);
    d.querySelector("#snav-mic-icon").classList.remove("pulsing");
    d.querySelector("#snav-mic-icon").classList.add("hidden");

    return;
  });

  const data = await response.json();
  const textArr = data.text.split("]");
  const text = textArr[textArr.length - 1].trim();

  incrementUsage();
  // if (text !== ""){
  //   incrementUsage();
  // } // perhaps this would be more reliable

  getMatch(text);
}

const dailyLimiteReached = async () => {
  try {
    const response = await fetch("/config/googleapi/daily-limit-reached");
    const data = await response.json();
    return data.daily_limit_reached;
  } catch (error) {
    console.error("Erro ao verificar limite diário:", error);
    return true;
  }
};

const recordingSeconds = async () => {
  const key = "smart_navigator_segundos_de_gravacao";

  const storedValue = sessionStorage.getItem(key);
  if (storedValue !== null) {
    return Number(storedValue);
  }

  try {
    const response = await fetch("/config/googleapi/segundos-gravacao");
    const data = await response.json();

    sessionStorage.setItem(key, data.segundos_de_gravacao);

    return Number(data.segundos_de_gravacao);
  } catch (error) {
    console.error("Erro ao verificar dados:", error);
    return 3;
  }
};

const incrementUsage = () => {
  fetch("/config/googleapi/increment-usage")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.warn("Failed to increment usage");
      }
    })
    .catch((err) => {
      console.error("Error incrementing usage:", err);
    });
};
