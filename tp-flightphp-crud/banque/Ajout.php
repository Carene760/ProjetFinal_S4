<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion de aaa fonds - EF</title>
   
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - " . $site_title : $site_title; ?></title>
    <meta name="description" content="<?php echo $site_description; ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="main">
<div class="container">
  <div class="card">
    <div class="card-header">
      <h2><i class="fas fa-money-bill-wave"></i> Gestion des fonds</h2>
    </div>
    
    <div class="card-body">
      <form onsubmit="ajouterFond(event)">
        <input type="hidden" id="etablissement_id" value="1">
        
        <div class="form-group">
          <label for="montant"><i class="fas fa-coins"></i> Montant :</label>
          <div class="form-amount">
            <span class="currency-symbol">Ar</span>
            <input type="number" step="0.01" id="montant" class="form-control" placeholder="Entrez le montant" required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="type_mouvement"><i class="fas fa-exchange-alt"></i> Type de mouvement :</label>
          <select id="type_mouvement" class="form-control" required>
            <option value="0">Entrée de fonds</option>
            <option value="1">Sortie de fonds</option>
          </select>
        </div>
        
        <button type="submit" class="btn btn-primary" id="submitBtn">
          <i class="fas fa-plus-circle"></i> Ajouter le fond
        </button>
      </form>
      
      <div id="message"></div>
      
      <div id="fondActuel">
        <i class="fas fa-wallet"></i>
        <span>Fond actuel : <span class="fond-value" id="fondValue">Chargement...</span></span>
      </div>
    </div>
  </div>
</div>

<script>
  const apiBase = "/tp-flightphp-crud/ws";

  function ajouterFond(event) {
    event.preventDefault();
    
    // Afficher l'indicateur de chargement
    const submitBtn = document.getElementById("submitBtn");
    submitBtn.innerHTML = '<span class="loading"></span> Traitement...';
    submitBtn.disabled = true;

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
        message.className = "";
        
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          message.textContent = response.message;
          message.className = "success-message";
          
          document.getElementById("montant").value = "";
          chargerFondActuel();
        } else {
          try {
            const response = JSON.parse(xhr.responseText);
            message.textContent = response.error || "Erreur lors de l'ajout du fond";
          } catch {
            message.textContent = "Erreur lors de l'ajout du fond";
          }
          message.className = "error-message";
        }
        
        // Réactiver le bouton
        submitBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter le fond';
        submitBtn.disabled = false;
      }
    };

    xhr.send(data);
  }

  function chargerFondActuel() {
    const fondValue = document.getElementById("fondValue");
    fondValue.textContent = "Chargement...";
    
    const xhr = new XMLHttpRequest();
    xhr.open("GET", apiBase + "/fond-actuel", true);

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          fondValue.textContent = response.fond_actuel + " Ar";
        } else {
          fondValue.textContent = "Erreur de chargement";
        }
      }
    };

    xhr.send();
  }

  // Charger le fond actuel au démarrage
  window.onload = chargerFondActuel;
</script>
<button onclick="history.back()" class="btn btn-secondary" style="margin-bottom: 20px;">
  <i class="fas fa-arrow-left"></i> Retour
</button>

</body>
</main>
</html>