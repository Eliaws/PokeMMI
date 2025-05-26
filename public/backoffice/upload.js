document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('upload-form');
  const versionSelect = document.getElementById('version-select');
  const messageBanner = document.getElementById('message-banner'); // Get the banner element

  // Function to display messages in the banner
  function showMessage(message, isSuccess) {
    messageBanner.innerHTML = ''; // Clear previous messages
    const messageDiv = document.createElement('div');
    messageDiv.textContent = message;
    messageDiv.className = `p-4 rounded-md ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
    messageBanner.appendChild(messageDiv);
   
    setTimeout(() => {
        messageBanner.innerHTML = '';
    }, 10000);
  }

  // Populate the select dropdown (game versions)
  const gameVersions = {
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

  Object.entries(gameVersions).forEach(([value, label]) => {
    const opt = document.createElement('option');
    opt.value = value;
    opt.textContent = label;
    versionSelect.appendChild(opt);
  });

  // Handle form submission
  form.addEventListener('submit', function(event) {
    event.preventDefault();
    messageBanner.innerHTML = ''; // Clear banner on new submission

    const formData = new FormData(form);

    // Basic client-side validation (optional, good practice)
    const fileInput = form.querySelector('input[type="file"]');
    if (!fileInput.files || fileInput.files.length === 0) {
        showMessage('Veuillez sélectionner un fichier.', false);
        return;
    }
    if (!versionSelect.value) {
        showMessage('Veuillez sélectionner un jeu.', false);
        return;
    }

    fetch('api/upload.php', {
      method: 'POST',
      body: formData,
    })
    .then(response => {
      if (!response.ok) {
        // If server response is not OK (e.g., 500 error), try to get text error or throw generic
        return response.text().then(text => {
            throw new Error(`Erreur serveur: ${response.status} ${response.statusText}. Détails: ${text}`);
        });
      }
      return response.json(); // Expect JSON response from PHP
    })
    .then(data => {
      if (data.success) {
        showMessage(data.message || 'Upload réussi !', true);
        form.reset();
      } else {
        showMessage(data.message || 'Une erreur est survenue lors de l\'upload.', false);
      }
    })
    .catch(error => {
      console.error('Fetch Error:', error);
      showMessage(`Erreur de communication avec le serveur: ${error.message}`, false);
    });
  });
});
