<?php
require_once '../ws/models/TypePret.php';
$clients = TypePret::getAllClient();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Gestion clients et prêts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #3f37c9;
      --accent: #4cc9f0;
      --light-bg: #f8f9fc;
      --dark-text: #2b2d42;
      --success: #4ade80;
      --warning: #f59e0b;
      --danger: #ef4444;
      --border: #e2e8f0;
    }
    
    body {
      background: linear-gradient(135deg, #f0f4f8 0%, #e6e9ff 100%);
      min-height: 100vh;
      padding: 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--dark-text);
    }
    
    .app-container {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .app-header {
      text-align: center;
      margin-bottom: 40px;
      padding: 20px 0;
      background: white;
      border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .app-title {
      font-weight: 700;
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 10px;
    }
    
    .card {
      border-radius: 16px;
      border: none;
      box-shadow: 0 10px 25px rgba(67, 97, 238, 0.15);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 30px;
      background: #ffffff;
    }
    
    .card-header {
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
      padding: 20px 25px;
      border-bottom: none;
      font-weight: 600;
      font-size: 1.4rem;
    }
    
    .table th {
      background-color: #f1f5fe;
      color: var(--primary);
      font-weight: 600;
    }
    
    .table-striped>tbody>tr:nth-of-type(odd) {
      background-color: rgba(67, 97, 238, 0.03);
    }
    
    .table-hover tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.08);
    }
    
    .client-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .client-row:hover {
      background-color: rgba(67, 97, 238, 0.1);
    }
    
    .pret-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .echeance-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .echeance-table th, .echeance-table td {
      padding: 10px;
      border: 1px solid var(--border);
    }
    
    .echeance-table th {
      background-color: #f8f9fc;
      font-weight: 600;
    }
    
    .status-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    
    .status-paye {
      background-color: rgba(74, 222, 128, 0.15);
      color: #15803d;
    }
    
    .status-non-paye {
      background-color: rgba(239, 68, 68, 0.15);
      color: #b91c1c;
    }
    
    .loading {
      text-align: center;
      padding: 30px;
      color: #64748b;
    }
    
    .no-data {
      text-align: center;
      padding: 30px;
      color: #64748b;
      background: #f8fafc;
      border-radius: 12px;
      margin: 20px 0;
    }
    
    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      text-align: center;
      margin-bottom: 20px;
    }
    
    .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
      margin: 10px 0;
    }
    
    .stat-label {
      color: #64748b;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="app-container">
    <header class="app-header">
      <h1 class="app-title"><i class="fas fa-hand-holding-usd me-2"></i>Gestion des Échéances Clients</h1>
      <p class="text-muted">Visualisez et gézrez les échéances de remboursement des prêts</p>
    </header>
    
    <div class="row">
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-users fa-2x text-primary"></i>
          </div>
          <div class="stat-value"><?= count($clients) ?></div>
          <div class="stat-label">Clients enregistrés</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
          </div>
          <div class="stat-value" id="total-prets">0</div>
          <div class="stat-label">Prêts actifs</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-exclamation-circle fa-2x text-primary"></i>
          </div>
          <div class="stat-value" id="total-echeances">0</div>
          <div class="stat-label">Échéances impayées</div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-5">
        <div class="card">
          <div class="card-header">
            <i class="fas fa-user-friends me-2"></i>Liste des clients
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($clients as $c): ?>
                    <tr class="client-row">
                      <td><?= htmlspecialchars($c['id']) ?></td>
                      <td><?= htmlspecialchars($c['nom']) ?></td>
                      <td><?= htmlspecialchars($c['prenom']) ?></td>
                      <td>
                        <button class="btn btn-sm btn-primary voir-pret" data-id="<?= $c['id'] ?>">
                          <i class="fas fa-eye me-1"></i> Voir prêts
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-7">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-file-invoice me-2"></i>Détails des prêts</span>
            <div id="client-title">Sélectionnez un client</div>
          </div>
          <div class="card-body">
            <div id="resultats" class="loading">
              <div class="text-center py-5">
                <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
                <p class="text-muted">Sélectionnez un client pour afficher ses prêts et échéances</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const apiBase = "http://localhost/ProjetFinal_S4_m/tp-flightphp-crud/ws";

    function ajax(method, url, data, callback) {
      const xhr = new XMLHttpRequest();
      xhr.open(method, apiBase + url, true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            callback(JSON.parse(xhr.responseText));
          } else {
            console.error("Erreur HTTP:", xhr.status);
            document.getElementById('resultats').innerHTML = `
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erreur lors du chargement des données: ${xhr.status} - ${xhr.statusText}
              </div>
            `;
          }
        }
      };
      xhr.send(data);
    }

    function afficherPrets(clientId, prenom) {
      const resultats = document.getElementById('resultats');
      const clientTitle = document.getElementById('client-title');
      
      clientTitle.innerHTML = `<strong>Client:</strong> ${prenom} (ID: ${clientId})`;
      resultats.innerHTML = `<div class="loading">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-3">Chargement des prêts du client...</p>
      </div>`;

      ajax("GET", `/remboursements-non-payes/${clientId}`, null, (rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
          resultats.innerHTML = `<div class="no-data">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h4>Aucune échéance impayée</h4>
            <p class="text-muted">Ce client n'a aucune échéance de remboursement impayée.</p>
          </div>`;
          return;
        }

        // Calculer les totaux pour les statistiques
        let totalPrets = 0;
        let totalEcheances = 0;
        const pretsMap = new Map();

        rows.forEach(row => {
          // Créer une structure pour chaque prêt
          if (!pretsMap.has(row.pret_id)) {
            pretsMap.set(row.pret_id, {
              id_pret: row.pret_id,
              montant: row.montant,
              duree: row.duree,
              date_debut: row.date_debut,
              statut: row.statut,
              echeances: []
            });
            totalPrets++;
          }
          
          // Ajouter l'échéance
          const echeance = {
            id: row.echeance_id,
            mois_annee: row.mois_annee,
            montant_total: row.montant_total,
            part_interet: row.part_interet,
            part_capital: row.part_capital,
            rassurance: row.rassurance,
            date_paiement_effectif: row.date_paiement_effectif,
            statut_paiement: row.statut_paiement || 'non payé'
          };
          
          pretsMap.get(row.pret_id).echeances.push(echeance);
          totalEcheances++;
        });

        // Mettre à jour les statistiques
        document.getElementById('total-prets').textContent = totalPrets;
        document.getElementById('total-echeances').textContent = totalEcheances;

        // Construire l'interface
        let html = '';
        pretsMap.forEach(pret => {
          html += `<div class="pret-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4>Prêt n°${pret.id_pret}</h4>
              <span class="badge bg-primary">${pret.echeances.length} échéance(s) impayée(s)</span>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-4">
                <div><strong>Montant:</strong> ${pret.montant.toLocaleString('fr-FR')} €</div>
              </div>
              <div class="col-md-4">
                <div><strong>Durée:</strong> ${pret.duree} mois</div>
              </div>
              <div class="col-md-4">
                <div><strong>Début:</strong> ${pret.date_debut}</div>
              </div>
            </div>
            
            <h5 class="mt-4 mb-3"><i class="fas fa-calendar-day me-2"></i>Échéances impayées</h5>
            <div class="table-responsive">
              <table class="echeance-table">
                <thead>
                  <tr>
                    <th>Date échéance</th>
                    <th>Total</th>
                    <th>Intérêt</th>
                    <th>Capital</th>
                    <th>Assurance</th>
                    <th>Date paiement</th>
                    <th>Statut</th>
                  </tr>
                </thead>
                <tbody>`;
                
          pret.echeances.forEach(e => {
            html += `<tr>
              <td>${e.mois_annee}</td>
              <td>${e.montant_total.toLocaleString('fr-FR')} €</td>
              <td>${e.part_interet.toLocaleString('fr-FR')} €</td>
              <td>${e.part_capital.toLocaleString('fr-FR')} €</td>
              <td>${e.rassurance.toLocaleString('fr-FR')} €</td>
              <td>${e.date_paiement_effectif || '—'}</td>
              <td>
                <span class="status-badge ${e.statut_paiement === 'payé' ? 'status-paye' : 'status-non-paye'}">
                  ${e.statut_paiement}
                </span>
              </td>
            </tr>`;
          });
          
          html += `</tbody>
              </table>
            </div>
          </div>`;
        });

        resultats.innerHTML = html;
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.voir-pret').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const clientId = this.dataset.id;
          const clientRow = this.closest('tr');
          const prenom = clientRow.cells[2].textContent;
          afficherPrets(clientId, prenom);
        });
      });
    });
  </script>
</body>
</html>