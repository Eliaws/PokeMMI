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
  const header = document.getElementById("collaborators-header");

  if (!header) {
    console.error("Erreur : élément #collaborators-header introuvable !");
    return;
  }

  header.innerHTML = collaborators.map(collab => `
    <div class="flex items-center gap-2">
      <img class="w-8 h-8 rounded-full border" src="${collab.avatar_url}" alt="${collab.login}">
      <a class="text-sm font-medium text-white hover:underline" href="${collab.html_url}" target="_blank">${collab.login}</a>
    </div>
  `).join('');
}

document.addEventListener("DOMContentLoaded", displayCollaborators);
