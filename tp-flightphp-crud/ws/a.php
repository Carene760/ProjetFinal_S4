<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require 'db.php';

function genererEcheancesMensuellesAnnuitesConstantes(PDO $pdo, $id_pret) {
    $stmt = $pdo->prepare("
        SELECT pret.*, type_pret.taux_interet
        FROM pret
        JOIN type_pret ON pret.id_type = type_pret.id
        WHERE id_pret = ?
    ");
    $stmt->execute([$id_pret]);
    $pret = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pret) {
        throw new Exception('Prêt introuvable');
    }

    echo "Prêt trouvé : ID $id_pret\n";

    $capital = $pret['montant'];
    $duree = $pret['duree'];
    $taux_annuel = $pret['taux_interet'];
    $date_debut = new DateTime($pret['date_debut']);
    $delai = intval($pret['delai_mois']);
    $taux_assurance = floatval($pret['pourcentage_assurance']);

    $date_debut->modify("+{$delai} months");
    $taux_mensuel = ($taux_annuel / 100) / 12;
    $mensualite = $capital * $taux_mensuel / (1 - pow(1 + $taux_mensuel, -$duree));
    $total_assurance = $capital * $taux_assurance / 100;
    $mensualite_assurance = round($total_assurance / ($duree + $delai), 2);

    for ($i = 0; $i < $duree + $delai; $i++) {
        $date_echeance = $date_debut->format('Y-m-d');

        if ($i < $delai) {
            $interet = $capital * $taux_mensuel;
            $part_capital = 0;
            $montant_total = $interet + $mensualite_assurance;
        } else {
            $interet = $capital * $taux_mensuel;
            $part_capital = $mensualite - $interet;
            $montant_total = $mensualite + $mensualite_assurance;
            $capital -= $part_capital;
        }

        echo "[$date_echeance] Total: $montant_total | Intérêt: $interet | Capital: $part_capital | Assurance: $mensualite_assurance\n";

        $insert = $pdo->prepare("
            INSERT INTO echeance_remboursement (
                id_pret, mois_annee, montant_total, part_interet, part_capital, rassurance
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        if (!$insert->execute([
            $id_pret,
            $date_echeance,
            round($montant_total, 2),
            round($interet, 2),
            round($part_capital, 2),
            $mensualite_assurance
        ])) {
            throw new Exception("Erreur insertion : " . implode(" | ", $insert->errorInfo()));
        }

        $date_debut->modify('+1 month');
    }

    echo "\n✅ Échéances générées avec succès.\n";
}

// Lancer le test ici (par exemple avec id_pret = 1)
try {
    $pdo = getDB();
    genererEcheancesMensuellesAnnuitesConstantes($pdo, 1);
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
