<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajout de fond - Établissement Financier</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #eef1f5;
      padding: 40px;
    }

    .container {
      width: 400px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
    }

    label {
      display: block;
      margin-top: 15px;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    button {
      margin-top: 20px;
      width: 100%;
      padding: 10px;
      background-color: #2d89ef;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
    }

    button:hover {
      background-color: #1b5fa7;
    }

    #message {
      text-align: center;
      margin-top: 15px;
      color: green;
    }

    #fondActuel {
      text-align: center;
      font-weight: bold;
      margin-top: 20px;
      font-size: 18px;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Ajout de Fond - EF</h2>
  <form onsubmit="ajouterFond(event)">
    <input type="hidden" id="etablissement_id" value="1">

    <label for="montant">Montant :</label>
    <input type="number" step="0.01" id="montant" required>

    <label for="type_mouvement">Type de mouvement :</label>
    <select id="type_mouvement" required>
      <option value="0">Entrée</option>
      <option value="1">Sortie</option>
    </select>

  

    <button type="submit">Ajouter le fond</button>
  </form>

  <div id="message"></div>
  <div id="fondActuel">Chargement du fond actuel...</div>
</div>

<script>
  const apiBase = "http://localhost/S4/ProjetFinal_S4_/tp-flightphp-crud/ws";

  function ajouterFond(event) {
    event.preventDefault();

    const etablissement_id = document.getElementById("etablissement_id").value;
    const montant = document.getElementById("montant").value;
    const type_mouvement = document.getElementById("type_mouvement").value;

   const data = `etablissement_id=${etablissement_id}&montant=${montant}&type_mouvement=${type_mouvement}`;


    const xhr = new XMLHttpRequest();
    xhr.open("POST", apiBase + "/ajout-fond", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        const message = document.getElementById("message");
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          message.textContent = response.message;
          message.style.color = "green";
          document.getElementById("montant").value = "";
          document.getElementById("date_mouvement").value = "";
          chargerFondActuel(); // Recharger le fond
        } else {
          try {
            const response = JSON.parse(xhr.responseText);
            message.textContent = response.error || "Erreur lors de l’ajout";
          } catch {
            message.textContent = "Erreur lors de l’ajout";
          }
          message.style.color = "red";
        }
      }
    };

    xhr.send(data);
  }

  function chargerFondActuel() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", apiBase + "/fond-actuel", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        const fondActuel = document.getElementById("fondActuel");
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          fondActuel.textContent = "Fond actuel : " + response.fond_actuel + " Ar";
        } else {
          fondActuel.textContent = "Erreur lors du chargement du fond actuel";
        }
      }
    };

    xhr.send();
  }

  window.onload = chargerFondActuel();
</script>

</body>
</html>
