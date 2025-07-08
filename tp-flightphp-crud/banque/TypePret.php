<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Types de Prêts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style2.css">
</head>
<body>
  <div class="app-container">
    <header class="app-header fade-in">
      <h1 class="app-title"><i class="fas fa-hand-holding-usd me-2"></i>Gestion des Types de Prêts</h1>
      <p class="app-subtitle">Créez et gérez les différents types de prêts proposés par votre institution financière</p>
    </header>
    
    <div class="stats-container">
      <div class="stat-card fade-in">
        <div class="stat-icon">
          <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-value">5</div>
        <div class="stat-label">Types de prêts</div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-value">42</div>
        <div class="stat-label">Prêts actifs</div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-value">28</div>
        <div class="stat-label">Clients satisfaits</div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value">97%</div>
        <div class="stat-label">Taux de réussite</div>
      </div>
    </div>
    
    <div class="card fade-in">
      <div class="card-header">
        <i class="fas fa-plus-circle me-2"></i>Créer un type de prêt
      </div>
      <div class="card-body">
        <form id="form-type-pret">
          <input type="hidden" id="id">
          
          <div class="row mb-4">
            <div class="col-md-12 mb-3">
              <label for="nom" class="form-label">Nom du prêt</label>
              <div class="input-icon-group">
                <i class="fas fa-file-signature input-icon"></i>
                <input type="text" id="nom" placeholder="Ex: Prêt personnel, Prêt immobilier..." class="form-control">
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="taux_interet" class="form-label">Taux d'intérêt (%)</label>
              <div class="input-icon-group">
                <i class="fas fa-percent input-icon"></i>
                <input type="number" id="taux_interet" placeholder="Ex: 3.5" step="0.01" class="form-control">
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="duree_max" class="form-label">Durée maximale (mois)</label>
              <div class="input-icon-group">
                <i class="fas fa-calendar-alt input-icon"></i>
                <input type="number" id="duree_max" placeholder="Ex: 60" step="1" class="form-control">
              </div>
            </div>
          </div>
          
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="montant_min" class="form-label">Montant minimal (€)</label>
              <div class="input-icon-group">
                <i class="fas fa-euro-sign input-icon"></i>
                <input type="number" id="montant_min" placeholder="Ex: 1000" step="0.01" class="form-control">
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="montant_max" class="form-label">Montant maximal (€)</label>
              <div class="input-icon-group">
                <i class="fas fa-euro-sign input-icon"></i>
                <input type="number" id="montant_max" placeholder="Ex: 50000" step="0.01" class="form-control">
              </div>
            </div>
          </div>
          
          <div class="d-flex gap-3">
            <button type="button" onclick="ajouterOuModifier()" class="btn btn-primary flex-grow-1 py-3">
              <i class="fas fa-save me-2"></i>Enregistrer
            </button>
            <button type="button" onclick="resetForm()" class="btn btn-outline-secondary py-3">
              <i class="fas fa-times me-2"></i>Annuler
            </button>
          </div>
        </form>
        
        <div id="message" class="mt-4"></div>
      </div>
    </div>
    
    <div class="card fade-in">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i>Liste des types de prêts</span>
        <button class="btn btn-sm btn-light" onclick="chargerTypePret()">
          <i class="fas fa-sync-alt"></i> Actualiser
        </button>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="table-type-prets">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Taux</th>
                <th>Durée</th>
                <th>Montant Min</th>
                <th>Montant Max</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Les données seront chargées dynamiquement ici -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <footer class="footer">
      <p>Système de gestion de prêts © 2023 - Tous droits réservés</p>
    </footer>
  </div>

  <script>
    const apiBase = "/tp-flightphp-crud/ws";

    function ajax(method, url, data, callback) {
      const xhr = new XMLHttpRequest();
      xhr.open(method, apiBase + url, true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
          callback(JSON.parse(xhr.responseText));
        }
      };
      xhr.send(data);
    }

    function chargerTypePret() {
      showMessage("Chargement des données en cours...", "success-message");
      ajax("GET", "/type-prets", null, (data) => {
        const tbody = document.querySelector("#table-type-prets tbody");
        tbody.innerHTML = "";
        
        data.forEach(e => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${e.id}</td>
            <td><strong>${e.nom}</strong></td>
            <td><span class="badge bg-primary">${e.taux_interet}%</span></td>
            <td>${e.duree_max} mois</td>
            <td>${e.montant_min.toLocaleString('fr-FR')} €</td>
            <td>${e.montant_max.toLocaleString('fr-FR')} €</td>
            <td>
              <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
              <button onclick='supprimerTypePret(${e.id})'>🗑️</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
        
        showMessage("Données chargées avec succès", "success-message");
      });
    }

    function ajouterOuModifier() {
      const id = document.getElementById("id").value;
      const nom = document.getElementById("nom").value;
      const taux_interet = document.getElementById("taux_interet").value;
      const duree_max = document.getElementById("duree_max").value;
      const montant_max = document.getElementById("montant_max").value;
      const montant_min = document.getElementById("montant_min").value;

      const data = `nom=${encodeURIComponent(nom)}&taux_interet=${encodeURIComponent(taux_interet)}&duree_max=${encodeURIComponent(duree_max)}&montant_max=${encodeURIComponent(montant_max)}&montant_min=${encodeURIComponent(montant_min)}`;

      if (id) {
        console.log("Modification du type de prêt avec ID:", id);
        ajax("PUT", `/type-prets/${id}`, data, () => {
          resetForm();
          chargerTypePret();
          showMessage("Type de prêt modifié avec succès", "success-message");
        });
      } else {
        showMessage("Ajout du nouveau type de prêt...", "success-message");
        ajax("POST", "/type-prets", data, () => {
          resetForm();
          chargerTypePret();
          showMessage("Type de prêt ajouté avec succès", "success-message");
        });
      }
    }

    function remplirFormulaire(e) {
      document.getElementById("id").value = e.id;
      document.getElementById("nom").value = e.nom;
      document.getElementById("taux_interet").value = e.taux_interet;
      document.getElementById("duree_max").value = e.duree_max;
      document.getElementById("montant_max").value = e.montant_max;
      document.getElementById("montant_min").value = e.montant_min;
      
      // Scroll vers le formulaire
      document.querySelector('.card').scrollIntoView({behavior: 'smooth'});
      showMessage("Formulaire rempli avec les données du prêt. Vous pouvez maintenant modifier.", "success-message");
    }

    function supprimerTypePret(id) {
      if (confirm("Supprimer ce type de prêt ?")) {
        ajax("DELETE", `/type-prets/${id}`, null, () => {
          chargerTypePret();
          showMessage("Type de prêt supprimé avec succès", "success-message");
        });
      }
    }

    function resetForm() {
      document.getElementById("id").value = "";
      document.getElementById("nom").value = "";
      document.getElementById("taux_interet").value = "";
      document.getElementById("duree_max").value = "";
      document.getElementById("montant_max").value = "";
      document.getElementById("montant_min").value = "";
      showMessage("Formulaire réinitialisé", "success-message");
    }

    function showMessage(text, className) {
      const message = document.getElementById("message");
      message.className = className;
      message.innerHTML = `<i class="fas fa-info-circle me-2"></i> ${text}`;
      setTimeout(() => {
        message.className = "";
        message.innerHTML = "";
      }, 4000);
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
      // Animation des éléments
      document.querySelectorAll('.fade-in').forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
      });
      
      // Charger les données initiales
      chargerTypePret();
    });
  </script>
</body>
</html>