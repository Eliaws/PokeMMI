<?php
require_once 'header.php';

// Fetch game list from src/utils.js via PHP (we'll parse the JS file to get game keys)
$jsFile = __DIR__ . '/../src/utils/index.js';
$jsContent = file_get_contents($jsFile);
// Regex to match keys in getVersionForName
preg_match('/export const getVersionForName = \{([\s\S]*?)\};/m', $jsContent, $matches);
$gameList = [];
if (isset($matches[1])) {
    $entries = explode(',', $matches[1]);
    foreach ($entries as $entry) {
        if (preg_match("/['\"]([a-z0-9\-]+)['\"]\s*:\s*['\"](.+)['\"]/", $entry, $m)) {
            $gameList[$m[1]] = $m[2];
        }
    }
}
?>
<h1>Uploader une jaquette de jeu</h1>
<form action="upload_handler.php" method="post" enctype="multipart/form-data">
    <label for="game">Jeu :</label>
    <select name="game" id="game" required>
        <option value="">-- Sélectionnez un jeu --</option>
        <?php foreach ($gameList as $key => $label): ?>
            <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
    </select><br/><br/>

    <label for="cover">Jaquette :</label>
    <input type="file" name="cover" id="cover" accept=".png,.jpg,.jpeg" required /><br/><br/>

    <button type="submit">Uploader</button>
</form>
<?php require_once 'footer.php';
