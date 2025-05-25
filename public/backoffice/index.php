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
    import { getVersionForName } from '../../src/utils.js';

    // const versions = getVersionForName(); 
    // const select = document.getElementById('version');
    // versions.forEach(v => {
    //     const option = document.createElement('option');
    //     option.value = v.name;
    //     option.textContent = v.label || v.name;
    //     select.appendChild(option);
    // });

    const versionSelect = document.getElementById('version');
        Object.keys(getVersionForName).forEach((version) => {
        const option = document.createElement('option');
        option.value = version;
        option.textContent = getVersionForName[version];
        versionSelect.append(option);
    });
</script>

</body>
</html>
