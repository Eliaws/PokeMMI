import { Octokit } from "@octokit/core";

const octokit = new Octokit({
  auth: import.meta.env.VITE_GITHUBTOKEN
});

console.log(octokit);

async function getCollaborators() {
  try {
    const response = await octokit.request('GET /repos/{owner}/{repo}/collaborators', {
      owner: 'Eliaws',
      repo: 'PokeMMI',
      headers: {
        'X-GitHub-Api-Version': '2022-11-28'
      }
    });
    console.log("Collaborateurs récupérés avec succès :", response.data);
    return response.data;
  } catch (error) {
    console.error("Erreur lors de la récupération des collaborateurs :", error);
    return [];
  }
}

async function displayCollaborators() {
  const collaborators = await getCollaborators();
  const list = document.getElementById("collaborators-list");

  if (!list) {
    console.error("Erreur : élément #collaborators-list introuvable !");
    return;
  }

  list.innerHTML = collaborators.map(collab => `
    <li class="flex items-center gap-2">
      <img class="w-8 h-8 rounded-full border-2" src="${collab.avatar_url}" alt="${collab.login}">
      <a class="text-sm font-medium text-gray-800 hover:underline hover:text-blue-500 hover:font-bold" href="${collab.html_url}" target="_blank">${collab.login}</a>
    </li>
  `).join('');
}

document.addEventListener("DOMContentLoaded", () => {
  // APIs modal controls
  const openApisBtn = document.getElementById('open-apis-modal');
  const apisModal = document.getElementById('apis-modal');
  const closeApisBtn = document.getElementById('close-apis-modal');
  openApisBtn?.addEventListener('click', async () => {
    await displayCollaborators();
    apisModal?.classList.remove('hidden');
  });
  closeApisBtn?.addEventListener('click', () => {
    apisModal?.classList.add('hidden');
  });
});
