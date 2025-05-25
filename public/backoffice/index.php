<?php require_once 'header.php'; ?>
<main class="max-w-xl mx-auto p-4 bg-white rounded shadow">
  <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label for="version" class="block mb-1 font-semibold">Sélectionner le jeu :</label>
      <select name="version" id="version" class="w-full p-2 border rounded" required></select>
    </div>
    <div>
      <label for="jaquette" class="block mb-1 font-semibold">Uploader une jaquette :</label>
      <input type="file" name="jaquette" accept="image/*" class="w-full p-2 border rounded" required>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Uploader</button>
  </form>
</main>

<script type="module">
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

  
  const versionSelect = document.getElementById('version');
  Object.entries(getVersionForNameCopy).forEach(([value,label]) => {
    const opt = document.createElement('option');
    opt.value = value;
    opt.textContent = label;
    versionSelect.append(opt);
  });
</script>

</body>
</html>
