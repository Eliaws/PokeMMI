document.addEventListener('DOMContentLoaded', () => {
  // Inject header
  const headerContainer = document.getElementById('header');
  fetch('./includes/header.html')
    .then(res => res.text())
    .then(html => { headerContainer.innerHTML = html });

  const form = document.getElementById('upload-form');

  // Remplir la liste déroulante
  const getVersionForNameCopy = {
    red: "Pokémon Rouge",
    blue: "Pokémon Bleue",
    yellow: "Pokémon Jaune",
    gold: "Pokémon Or",
    silver: "Pokémon Argent",
    crystal: "Pokémon Crystal",
    sapphire: "Pokémon Saphir",
    ruby: "Pokémon Rubis",
    emerald: "Pokémon Émeraude",
    firered: "Pokémon Rouge feu",
    leafgreen: "Pokémon Vert feuille",
    diamond: "Pokémon Diamant",
    pearl: "Pokémon Perle",
    platinum: "Pokémon Platine",
    heartgold: "Pokémon Or HeartGold",
    soulsilver: "Pokémon Argent SoulSilver",
    white: "Pokémon Blanche",
    black: "Pokémon Noire",
    "black-2": "Pokémon Noire 2",
    "white-2": "Pokémon Blanche 2",
    x: "Pokémon X",
    y: "Pokémon Y",
    "omega-ruby": "Pokémon Rubis Oméga",
    "ultra-sun": "Pokémon Ultra-Soleil",
    sun: "Pokémon Soleil",
    moon: "Pokémon Lune",
    "ultra-moon": "Pokémon Ultra-Lune",
    "alpha-sapphire": "Pokémon Saphir Alpha",
    sword: "Pokémon Épée",
    shield: "Pokémon Bouclier",
    violet: "Pokémon Violet",
    scarlet: "Pokémon Écarlate",
    "lets-go-eevee": "Pokémon Let's Go, Évoli",
    "lets-go-pikachu": "Pokémon Let's Go, Pikachu",
    "legends-arceus": "Légendes Pokémon : Arceus",
};

  
  const versionSelect = document.getElementById('version-select');
  Object.entries(getVersionForNameCopy).forEach(([value,label]) => {
    const opt = document.createElement('option');
    opt.value = value;
    opt.textContent = label;
    versionSelect.append(opt);
  });



  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const response = await fetch('api/upload', {
      method: 'POST',
      body: formData,
    });
    const result = await response.text();
    alert(result);
  });
});
