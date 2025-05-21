import { test, expect } from "@playwright/test";

test.beforeEach(async ({ page }) => {
    await page.goto("/");
    // Attendre le chargement des Pokemon initiaux
    await page.waitForSelector('[data-testid="pokemon"]');
});

test("should add new Pokedex", { tag: "@smoke" }, async ({ page }) => {
    // Attendre le chargement initial du pokedex
    await page.waitForSelector('[data-pokedex]');
    const pokedexOnPage = await page.getByTestId("pokedex");
    const nbPokedexOnPage = await pokedexOnPage.count();

    await page.getByTestId("load-generation-btn").first().click();
    await expect(pokedexOnPage).toHaveCount(nbPokedexOnPage + 1);
    await expect(page.getByTestId("load-generation-btn")).toHaveAttribute(
        "data-load-generation",
        String(nbPokedexOnPage + 2)
    );
});

test("should disable load generation button when there's no generation anymore", async ({
    page,
}) => {
    // Attendre le chargement initial du pokedex
    await page.waitForSelector('[data-pokedex]');
    const loadGenerationBtn = await page
        .getByTestId("load-generation-btn")
        .first();
    const fakeGeneration = "42";
    await loadGenerationBtn.evaluate((node) => {
        const fakeGeneration = "42";
        return node.setAttribute("data-load-generation", fakeGeneration);
    });

    const dexRequest = page.waitForResponse(
        `https://tyradex.vercel.app/api/v1/gen/${fakeGeneration}`
    );

    await expect(loadGenerationBtn).toHaveAttribute(
        "data-load-generation",
        fakeGeneration
    );
    await loadGenerationBtn.click();
    await dexRequest;

    await expect(loadGenerationBtn).toHaveAttribute("inert", "");
});

test("should not reload the page after select a Pokemon", { tag: "@smoke" }, async ({
    page,
}) => {
    // Attendre le chargement initial du pokedex
    await page.waitForSelector('[data-pokedex]');

        const firstPkmn = page.getByTestId("pokemon").first();
        await firstPkmn.waitFor();
        await firstPkmn.click();

        await expect(page).not.toHaveTitle("Pokédex v1.0.0");
    }
);

test("should change title's value according to current generation displayed", async ({
    page,
}) => {
    // Attendre le rendu initial
    await page.waitForSelector('[data-header-pokedex]');

    const loadGenerationButton = await page
        .getByTestId("load-generation-btn")
        .first();
    const nextGenerationNumber = await loadGenerationButton.getAttribute(
        "data-load-generation"
    );
    await loadGenerationButton.click();
        
    // Attendre le rendu de la nouvelle génération
    await page.waitForSelector(`[data-header-pokedex="${nextGenerationNumber}"]`);
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));

    // Vérifier que le titre a été mis à jour dynamiquement
    await expect(page).toHaveTitle(new RegExp(`Génération #${nextGenerationNumber}`));
});

test("should listen to query string params @smoke", async ({ page }) => {
    // Ouvrir directement avec un paramètre id
    await page.goto("/?id=17");
    await page.getByTestId("pokemon-modal").waitFor({ state: 'visible' });
    await expect(page.getByTestId("pokemon-modal")).toHaveAttribute("open", "");
    // Retour arrière doit fermer le modal
    await page.goBack();
    await expect(page.getByTestId("pokemon-modal")).not.toHaveAttribute("open", "");
});

test("should indicate the right gen in the navigation shortcut", async ({
    page,
}) => {
    // Attendre le rendu du header initial
    await page.waitForSelector('[data-header-pokedex]');

    const loadGenerationButton = await page
        .getByTestId("load-generation-btn")
        .first();
    const nextGenerationNumber = await loadGenerationButton.getAttribute(
        "data-load-generation"
    );
    await loadGenerationButton.click();

    // Attendre le rendu du header de la génération suivante
    await page.waitForSelector(`[data-header-pokedex="${nextGenerationNumber}"]`);

    // Cliquer sur le raccourci de la génération et vérifier la classe active
    const gen2Shortcut = page.locator(`[data-id="pokedex-${nextGenerationNumber}"]`).first();
    await gen2Shortcut.click();
    await expect(gen2Shortcut).toHaveClass(/font-bold/);
});
