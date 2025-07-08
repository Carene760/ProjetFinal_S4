<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau des intérêts mensuels</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 30px;
      background-color: #f5f7fa;
      color: #333;
    }

    h1 {
      color: #2c3e50;
      margin-bottom: 25px;
      border-bottom: 2px solid #3498db;
      padding-bottom: 10px;
    }

    .controls {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 15px;
    }

    label {
      display: flex;
      align-items: center;
      font-weight: 500;
      color: #2c3e50;
    }

    input {
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-left: 8px;
      font-size: 14px;
    }

    button {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #2980b9;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      margin: 25px 0;
      box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      background-color: white;
    }

    th, td {
      padding: 12px 15px;
      text-align: right;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background-color: #3498db;
      color: white;
      text-align: center;
      position: sticky;
      top: 0;
    }

    td:first-child {
      text-align: left;
      font-weight: 500;
    }

    tr:hover {
      background-color: #f8f9fa;
    }

    .chart-container {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-top: 30px;
    }

    .no-data {
      text-align: center;
      padding: 20px;
      color: #7f8c8d;
      font-style: italic;
    }

    @media (max-width: 768px) {
      .controls {
        flex-direction: column;
        align-items: stretch;
      }
      
      label {
        flex-direction: column;
        align-items: flex-start;
      }
      
      input {
        margin-left: 0;
        margin-top: 5px;
        width: 100%;
      }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

  <h1>Analyse des intérêts mensuels</h1>

  <div class="controls">
    <label>
      ID Établissement Financier
      <input type="number" id="id_ef" value="1" min="1" placeholder="ID EF" />
    </label>
    
    <label>
      Période de début
      <input type="month" id="debut" value="2023-01" />
    </label>
    
    <label>
      Période de fin
      <input type="month" id="fin" value="2023-06" />
    </label>
    
    <button onclick="chargerInterets()">
      <i class="fas fa-search"></i> Afficher les données
    </button>
  </div>

  <table id="table-interets">
    <thead>
      <tr>
        <th>Mois</th>
        <th>Intérêts reçus (Ar)</th>
        <th>Intérêts courus (Ar)</th>
        <th>Total intérêts (Ar)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="4" class="no-data">Sélectionnez une période et cliquez sur "Afficher"</td>
      </tr>
    </tbody>
    <tfoot id="table-footer" style="display: none;">
      <tr>
        <th>Total</th>
        <th id="total-recus">0 Ar</th>
        <th id="total-courus">0 Ar</th>
        <th id="total-general">0 Ar</th>
      </tr>
    </tfoot>
  </table>

  <div class="chart-container">
    <h2>Visualisation graphique</h2>
    <canvas id="graphique-interets" height="350"></canvas>
  </div>

  <script>
    const API_BASE = "/tp-flightphp-crud/ws"; // Adaptez cette URL
    let chart = null;

    // Fonction pour formater les montants
    function formatMontant(montant) {
      return new Intl.NumberFormat('fr-FR', { 
        style: 'decimal', 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
      }).format(montant) + " Ar";
    }

    // Fonction pour charger les données
    async function chargerInterets() {
  console.log("Début de chargement..."); // Debug 1
  
  const idEf = document.getElementById("id_ef").value;
  const debut = document.getElementById("debut").value;
  const fin = document.getElementById("fin").value;
  
  console.log("Paramètres:", {idEf, debut, fin}); // Debug 2

  if (!idEf || !debut || !fin) {
    console.error("Champs manquants"); // Debug 3
    alert("Veuillez remplir tous les champs.");
    return;
  }

  try {
    const url = `${API_BASE}/pret/interets-mensuels?id_ef=${idEf}&debut=${debut}&fin=${fin}`;
    console.log("URL appelée:", url); // Debug 4
    
    const response = await fetch(url);
    console.log("Réponse reçue, status:", response.status); // Debug 5
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }
    
    const data = await response.json();
    console.log("Données reçues:", data); // Debug 6
    
    afficherTableau(data);
    afficherGraphique(data);
  } catch (error) {
    console.error("Erreur complète:", error); // Debug 7
    alert("Une erreur est survenue. Voir la console pour les détails.");
  }
}

    // Fonction pour afficher le tableau
    function afficherTableau(data) {
    const tbody = document.querySelector("#table-interets tbody");
    const footer = document.getElementById("table-footer");
    
    tbody.innerHTML = "";
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">Aucune donnée disponible</td></tr>';
        footer.style.display = 'none';
        return;
    }

    let totalRecus = 0;
    let totalCourus = 0;
    let totalGeneral = 0;

    data.forEach(ligne => {
        // Filtre les lignes avec mois null
        if (!ligne.mois) return;
        
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${formatMois(ligne.mois)}</td>
            <td>${formatMontant(ligne.interets_recus)}</td>
            <td>${formatMontant(ligne.interets_courus)}</td>
            <td>${formatMontant(ligne.total_interets)}</td>
        `;
        tbody.appendChild(tr);

        totalRecus += parseFloat(ligne.interets_recus);
        totalCourus += parseFloat(ligne.interets_courus);
        totalGeneral += parseFloat(ligne.total_interets);
    });

    // Mise à jour des totaux
    document.getElementById("total-recus").textContent = formatMontant(totalRecus);
    document.getElementById("total-courus").textContent = formatMontant(totalCourus);
    document.getElementById("total-general").textContent = formatMontant(totalGeneral);
    footer.style.display = '';
}

    // Fonction pour formater l'affichage du mois
    function formatMois(moisStr) {
    if (!moisStr) return "Date inconnue"; // Gestion du cas null/undefined
    
    const [annee, mois] = moisStr.split('-');
    const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                     'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    return `${moisNoms[parseInt(mois) - 1]} ${annee}`;
}


    // Fonction pour afficher le graphique
    function afficherGraphique(data) {
      if (!data || data.length === 0) {
        if (chart) chart.destroy();
        return;
      }

      const ctx = document.getElementById("graphique-interets").getContext("2d");
      
      // Destruction du graphique existant
      if (chart) chart.destroy();

      // Préparation des données
      const labels = data.map(ligne => formatMois(ligne.mois));
      const recusData = data.map(ligne => parseFloat(ligne.interets_recus));
      const courusData = data.map(ligne => parseFloat(ligne.interets_courus));
      const totauxData = data.map(ligne => parseFloat(ligne.total_interets));

      // Création du graphique
      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Intérêts reçus',
              data: recusData,
              backgroundColor: 'rgba(54, 162, 235, 0.7)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            },
            {
              label: 'Intérêts courus',
              data: courusData,
              backgroundColor: 'rgba(255, 206, 86, 0.7)',
              borderColor: 'rgba(255, 206, 86, 1)',
              borderWidth: 1
            },
            {
              label: 'Total intérêts',
              data: totauxData,
              backgroundColor: 'rgba(75, 192, 192, 0.7)',
              borderColor: 'rgba(75, 192, 192, 1)',
              borderWidth: 1,
              type: 'line',
              fill: false
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Évolution des intérêts mensuels',
              font: {
                size: 16
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': ' + formatMontant(context.raw);
                }
              }
            },
            legend: {
              position: 'top',
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return formatMontant(value);
                }
              },
              title: {
                display: true,
                text: 'Montant (Ar)'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Mois'
              }
            }
          }
        }
      });
    }

    // Chargement initial
    document.addEventListener('DOMContentLoaded', function() {
      // Vous pouvez activer ceci si vous voulez un chargement automatique au démarrage
      // chargerInterets();
    });
  </script>

</body>
</html>