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
        $stmt = $db->prepare("INSERT INTO pret (id_client, id_type, id_ef, montant, duree, date_debut, date_fin, frequence_remboursement, statut) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data->id_client, 
            $data->id_type, 
            $data->id_ef, 
            $data->montant, 
            $data->duree, 
            $data->date_debut, 
            $data->date_fin, 
            $data->frequence_remboursement, 
            $data->statut ?? 'en cours'
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
            date_fin = ?, 
            frequence_remboursement = ?, 
            statut = ? 
            WHERE id_pret = ?");
        $stmt->execute([
            $data->id_client, 
            $data->id_type, 
            $data->id_ef, 
            $data->montant, 
            $data->duree, 
            $data->date_debut, 
            $data->date_fin, 
            $data->frequence_remboursement, 
            $data->statut, 
            $id
        ]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret WHERE id_pret = ?");
        $stmt->execute([$id]);
    }

    public static function getByIdWithDetails($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, 
                   t.nom_type, t.taux_interet,
                   c.nom AS client_nom,
                   ef.nom AS etablissement_nom
            FROM pret p
            JOIN type_pret t ON p.id_type = t.id_type
            JOIN client c ON p.id_client = c.id
            JOIN etablissement_financier ef ON p.id_ef = ef.id
            WHERE p.id_pret = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*function genererEcheancesMensuellesAnnuitesConstantes(PDO $pdo, $id_pret) {
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
    }*/
    function genererEcheancesMensuellesAnnuitesConstantes(PDO $pdo, $id_pret) {
    // 🔍 Récupération des infos du prêt
    $stmt = $pdo->prepare("
        SELECT pret.*, type_pret.taux_interet
        FROM pret
        JOIN type_pret ON pret.id_type = type_pret.id
        WHERE id_pret = ?
    ");
    $stmt->execute([$id_pret]);
    $pret = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pret) {
        echo "❌ Prêt introuvable.";
        return;
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

    // ✅ Données utiles
    $capital = $pret['montant'];
    $duree = $pret['duree']; // en mois
    $taux_annuel = $pret['taux_interet'];
    $frequence = $pret['frequence_remboursement'];
    $date_debut = new DateTime($pret['date_debut']);
    $delai = intval($pret['delai_mois']);
    $taux_assurance = floatval($pret['pourcentage_assurance']);

    // ✅ Appliquer le délai de grâce
    $date_debut->modify("+{$delai} months");

    // ✅ Calcul du taux mensuel
    $taux_mensuel = ($taux_annuel / 100) / 12;

    // ✅ Annuité constante (mensualité)
    $mensualite = $capital * $taux_mensuel / (1 - pow(1 + $taux_mensuel, -$duree));

    // ✅ Assurance répartie par mois
    $total_assurance = $capital * $taux_assurance / 100;
    $mensualite_assurance = round($total_assurance / ($duree + $delai), 2); // même pendant le délai

    // Boucle sur chaque mois (inclut les mois de délai)
    for ($i = 0; $i < $duree + $delai; $i++) {
        $date_echeance = $date_debut->format('Y-m-d');

        if ($i < $delai) {
            // 🔹 Délai : on ne paye que les intérêts et l’assurance
            $interet = $capital * $taux_mensuel;
            $part_capital = 0;
            $montant_total = $interet + $mensualite_assurance;
        } else {
            // 🔸 Échéance normale : remboursement capital + intérêt + assurance
            $interet = $capital * $taux_mensuel;
            $part_capital = $mensualite - $interet;
            $montant_total = $mensualite + $mensualite_assurance;
            $capital -= $part_capital;
        }

        // ➕ Insertion
        $insert = $pdo->prepare("
            INSERT INTO echeance_remboursement (
                id_pret, mois_annee, montant_total, part_interet, part_capital, rassurance
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $id_pret,
            $date_echeance,
            round($montant_total, 2),
            round($interet, 2),
            round($part_capital, 2),
            $mensualite_assurance
        ]);

        // Mois suivant
        $date_debut->modify('+1 month');
    }

    echo "✅ Échéances générées avec assurance pour le prêt #{$id_pret}";
}
    
}