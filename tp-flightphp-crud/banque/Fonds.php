<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau des fonds disponibles</title>
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
</head>
<body>

  <h1>Analyse des fonds disponibles</h1>

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
    
    <button onclick="chargerFonds()">
      Afficher les données
    </button>
  </div>

  <table id="table-fonds">
    <thead>
      <tr>
        <th>Mois</th>
        <th>Fonds début mois (Ar)</th>
        <th>Prêts accordés (Ar)</th>
        <th>Remboursements (Ar)</th>
        <th>Fonds fin mois (Ar)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="6" class="no-data">Sélectionnez une période et cliquez sur "Afficher"</td>
      </tr>
    </tbody>
  </table>

  <script>
    const API_BASE = "/tp-flightphp-crud/ws"; // Adaptez cette URL à votre endpoint

    // Fonction pour formater les montants
    function formatMontant(montant) {
      return new Intl.NumberFormat('fr-FR', { 
        style: 'decimal', 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
      }).format(montant) + " Ar";
    }

    // Fonction pour formater l'affichage du mois
    function formatMois(moisStr) {
      if (!moisStr) return "Date inconnue";
      const [annee, mois] = moisStr.split('-');
      const moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
      return `${moisNoms[parseInt(mois) - 1]} ${annee}`;
    }

    // Fonction pour charger les données
    async function chargerFonds() {
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
    const url = `${API_BASE}/fonds/fonds-disponible?id_ef=${idEf}&debut=${debut}&fin=${fin}`;
    console.log("URL appelée:", url); // Debug 4
    
    const response = await fetch(url);
    console.log("Réponse reçue, status:", response.status); // Debug 5
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }
    
    const data = await response.json();
    console.log("Données reçues:", data); // Debug 6
    
    afficherTableau(data);
    // afficherGraphique(data);
  } catch (error) {
    console.error("Erreur complète:", error); // Debug 7
    alert("Une erreur est survenue. Voir la console pour les détails.");
  }
}

    // Fonction pour afficher le tableau
    function afficherTableau(data) {
      const tbody = document.querySelector("#table-fonds tbody");
      tbody.innerHTML = "";
      
      if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">Aucune donnée disponible</td></tr>';
        return;
      }

      data.forEach(ligne => {
        if (!ligne.mois) return;
        
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${formatMois(ligne.mois)}</td>
          <td>${formatMontant(ligne.fondDebut || 0)}</td>
          <td>${formatMontant(ligne.prets || 0)}</td>
          <td>${formatMontant(ligne.Remboursements || 0)}</td>
          <td>${formatMontant(ligne.fondFin || 0)}</td>
        `;
        tbody.appendChild(tr);
      });
    }

    // Chargement initial
    document.addEventListener('DOMContentLoaded', function() {
      // Vous pouvez activer ceci si vous voulez un chargement automatique au démarrage
      // chargerFonds();
    });
  </script>

</body>
</html>