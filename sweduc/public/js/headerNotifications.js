document.addEventListener("DOMContentLoaded", async function () {
  const res = await fetch("/atualizacoes/novas");
  const data = await res.json();
  const quantNaoVistos = data.count_nao_visualizado;

  const countEl = document.getElementById("notification-count");
  const badgeEl = document.querySelector(".notification-badge");
  const bellIcon = document.querySelector("#notifications-outer svg");

  if (quantNaoVistos > 0) {
    countEl.innerText = quantNaoVistos;
    bellIcon.classList.add("active");
    badgeEl.style.display = "inline-block";
  } else {
    countEl.innerText = "0";
    bellIcon.classList.remove("active");
    badgeEl.style.display = "none";
  }
});
