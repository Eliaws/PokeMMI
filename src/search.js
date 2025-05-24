//import { fetchPokemonForGeneration } from "./api/tyradex.js"; Méthode pour afficher tous les pokemons dans la barre de recherche à la place de la génération 1
import { fetchAllPokemons } from "./api/tyradex.js";

const SEARCH_MODAL_TIMEOUT_MS = 500; // 0.5 secondes

document.addEventListener("DOMContentLoaded", async () => {
    const searchInput = document.getElementById("poke-search");
    const autocompleteList = document.getElementById("autocomplete-list");

    let allPokemons = [];

    // Fetch all Pokémon for the first generation (or all generations if needed)
    try {
        allPokemons = await fetchAllPokemons();
    } catch (error) {
        console.error("Erreur lors de la récupération des Pokémon :", error);
    }

    // Function to filter Pokémon based on search query
    const filterPokemons = (query) => {
        const lowerQuery = query.toLowerCase();
        return allPokemons.filter(pokemon => 
            pokemon.name.fr.toLowerCase().includes(lowerQuery) || 
            pokemon.id
        );
    };

    // Function to render the autocomplete list
    const renderAutocomplete = (results) => {
        autocompleteList.innerHTML = "";
        if (results.length === 0) {
            const noResultItem = document.createElement("li");
            noResultItem.textContent = "Aucun Pokémon correspond à cette recherche";
            noResultItem.className = "p-2 text-gray-500";
            autocompleteList.appendChild(noResultItem);
        } else {
            results.forEach(pokemon => {
                const listItem = document.createElement("li");
                listItem.className = "p-2 cursor-pointer hover:bg-gray-200 flex items-center gap-2 z-10";

                const sprite = document.createElement("img");
                sprite.src = pokemon.sprites.regular;
                sprite.alt = pokemon.name.fr;
                sprite.className = "w-8 h-8";

                const text = document.createElement("span");
                text.textContent = `#${pokemon.pokedex_id} - ${pokemon.name.fr}`;

                listItem.appendChild(sprite);
                listItem.appendChild(text);

                listItem.addEventListener("click", () => {
                    window.location.href = `?id=${pokemon.pokedex_id}`;
                });

                autocompleteList.appendChild(listItem);
            });
        }
        autocompleteList.classList.remove("hidden");
    };

    // Event listener for input changes
    searchInput.addEventListener("input", (event) => {
        const query = event.target.value;
        if (query.trim() === "") {
            autocompleteList.classList.add("hidden");
            return;
        }
        const results = filterPokemons(query);
        renderAutocomplete(results);
    });

    // Hide the autocomplete list when clicking outside
    document.addEventListener("click", (event) => {
        if (!autocompleteList.contains(event.target) && event.target !== searchInput) {
            autocompleteList.classList.add("hidden");
        }
    });

    
    // Open modal
    setTimeout(() => {
        const closeSearchModalButton = document.getElementById("close-search-modal");
        const searchModal = document.getElementById("search-modal");
        const inputSearch = document.getElementById("poke-search");

        let openSearchModalButtons = document.querySelectorAll("#open-search-modal");

        for (const button of openSearchModalButtons) {
            button.addEventListener("click", () => {
                searchModal.classList.remove("hidden");
                searchModal.classList.add("flex");
            });
        }
        // Close modal
        closeSearchModalButton.addEventListener("click", () => {
            searchModal.classList.add("hidden");
            searchModal.classList.remove("flex");
            inputSearch.value = "";
        });

        // Close modal when clicking outside the modal content
        searchModal.addEventListener("click", (event) => {
            if (event.target === searchModal) {
                searchModal.classList.add("hidden");
                searchModal.classList.remove("flex");
                inputSearch.value = "";
            }
        });
    }, SEARCH_MODAL_TIMEOUT_MS);

    // Delegate opening of search modal for any open-search-modal button
    document.addEventListener("click", event => {
        if (event.target.matches("#open-search-modal")) {
            const searchModal = document.getElementById("search-modal");
            searchModal.classList.remove("hidden");
            searchModal.classList.add("flex");
        }
    });
});