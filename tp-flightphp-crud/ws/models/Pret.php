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


    
}