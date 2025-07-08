<?php
function genererEcheancesMensuellesAnnuitesConstantes(PDO $pdo, $id_pret) {
    // Récupération des infos du prêt
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

    // Données utiles
    $capital = $pret['montant'];
    $duree = $pret['duree']; // en mois
    $taux_annuel = $pret['taux_interet'];
    $frequence = $pret['frequence_remboursement'];
    $date_debut = new DateTime($pret['date_debut']);
    $delai = intval($pret['delai_mois']);
    $taux_assurance = floatval($pret['pourcentage_assurance']);

    // Appliquer le délai de grâce
    $date_debut->modify("+{$delai} months");

    // Calcul du taux mensuel
    $taux_mensuel = ($taux_annuel / 100) / 12;

    // Annuité constante (mensualité)
    $mensualite = $capital * $taux_mensuel / (1 - pow(1 + $taux_mensuel, -$duree));

    // Assurance répartie par mois
    $total_assurance = $capital * $taux_assurance / 100;
    $mensualite_assurance = round($total_assurance / ($duree + $delai), 2);

    for ($i = 0; $i < $duree + $delai; $i++) {
        $date_echeance = $date_debut->format('Y-m-d');

        if ($i < $delai) {
            // Délai : seulement intérêt + assurance
            $interet = $capital * $taux_mensuel;
            $part_capital = 0;
            $montant_total = $interet + $mensualite_assurance;
        } else {
            // Échéance normale
            $interet = $capital * $taux_mensuel;
            $part_capital = $mensualite - $interet;
            $montant_total = $mensualite + $mensualite_assurance;
            $capital -= $part_capital;
        }

        // Insertion
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

        $date_debut->modify('+1 month');
    }

    echo "✅ Échéances générées avec succès pour le prêt #{$id_pret}";
}
