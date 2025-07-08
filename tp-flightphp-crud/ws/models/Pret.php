<?php
require_once __DIR__ . '/../db.php';

class Pret {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM pret");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM pret WHERE id_pret = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pret (id_client, id_type, id_ef, montant, duree, date_debut, frequence_remboursement, statut, pourcentage_assurance, delai_mois, est_valide) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data->id_client, 
            $data->id_type, 
            $data->id_ef, 
            $data->montant, 
            $data->duree, 
            $data->date_debut, 
            $data->frequence_remboursement, 
            $data->statut ?? 'en cours',
            $data->pourcentage_assurance ?? 0.00,
            $data->delai_mois ?? 0,
            $data->est_valide ?? false
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret SET 
            id_client = ?, 
            id_type = ?, 
            id_ef = ?, 
            montant = ?, 
            duree = ?, 
            date_debut = ?, 
            frequence_remboursement = ?, 
            statut = ?,
            pourcentage_assurance = ?,
            delai_mois = ?,
            est_valide = ?
            WHERE id_pret = ?");
        $stmt->execute([
            $data->id_client, 
            $data->id_type, 
            $data->id_ef, 
            $data->montant, 
            $data->duree, 
            $data->date_debut,
            $data->frequence_remboursement, 
            $data->statut, 
            $data->pourcentage_assurance ?? 0.00,
            $data->delai_mois ?? 0,
            $data->est_valide ?? false,
            $id
        ]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret WHERE id_pret = ?");
        $stmt->execute([$id]);
    }

    public static function getFondActuel() {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT
            COALESCE(SUM(CASE WHEN type_mouvement = 0 THEN montant ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN type_mouvement = 1 THEN montant ELSE 0 END), 0) AS fond_actuel
            FROM fond
            WHERE etablissement_id = 1
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Récupération du résultat
        return $result['fond_actuel'] ?? 0; // Retourne 0 si aucun résultat
    }

    function genererEcheancesMensuellesAnnuitesConstantes(PDO $pdo, $id_pret) {
        // 🔍 Récupération des infos du prêt
        $stmt = $pdo->prepare("
            SELECT pret.*, type_pret.taux_interet
            FROM pret
            JOIN type_pret ON pret.id_type = type_pret.id_type
            WHERE id_pret = ?
        ");
        $stmt->execute([$id_pret]);
        $pret = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pret) {
            echo "❌ Prêt introuvable.";
            return;
        }

        // ✅ Données utiles
        $capital = $pret['montant'];
        $duree = $pret['duree']; // en mois
        $taux_annuel = $pret['taux_interet'];
        $frequence = $pret['frequence_remboursement'];
        $date_debut = new DateTime($pret['date_debut']);
        $delai = intval($pret['delai_grace']);

        // ✅ Appliquer le délai de grâce
        $date_debut->modify("+{$delai} months");

        // ✅ Calcul du taux mensuel
        $taux_mensuel = ($taux_annuel / 100) / 12;

        // ✅ Annuité constante (mensualité)
        $mensualite = $capital * $taux_mensuel / (1 - pow(1 + $taux_mensuel, -$duree));

        // Boucle sur chaque mois
        for ($i = 0; $i < $duree; $i++) {
            $date_echeance = $date_debut->format('Y-m-d');

            // Intérêt = capital restant * taux mensuel
            $interet = $capital * $taux_mensuel;
            $part_capital = $mensualite - $interet;

            // Insertion dans la table echeance_remboursement
            $insert = $pdo->prepare("
                INSERT INTO echeance_remboursement (
                    id_pret, mois_annee, montant_total, part_interet, part_capital
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $insert->execute([
                $id_pret,
                $date_echeance,
                round($mensualite, 2),
                round($interet, 2),
                round($part_capital, 2)
            ]);

            // Mettre à jour le capital restant
            $capital -= $part_capital;

            // Mois suivant
            $date_debut->modify('+1 month');
        }

        echo "✅ Échéances générées avec succès pour le prêt #{$id_pret}";
    }
    public static function getInteretsMensuels($idEf, $debut, $fin) {
    $db = getDB(); // Votre connexion PDO
    
    try {
        // Préparation de l'appel
        $stmt = $db->prepare("CALL CalculInteretsMensuels(:id_ef, :debut, :fin)");
        // Liaison des paramètres
        $stmt->bindParam(':id_ef', $idEf, PDO::PARAM_INT);
        $stmt->bindParam(':debut', $debut, PDO::PARAM_STR);
        $stmt->bindParam(':fin', $fin, PDO::PARAM_STR);
        
        // Exécution
        $stmt->execute();
        
        // Récupération des résultats
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fermeture du curseur (important pour MariaDB/MySQL)
        $stmt->closeCursor();
        
        return $resultats;
        
    } catch (PDOException $e) {
        error_log("Erreur lors de l'appel de la procédure stockée: " . $e->getMessage());
        return [];
    }
}


    
}