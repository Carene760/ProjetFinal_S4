<?php 

class Remboursement {
    public static function getRemboursementsByPretId($pret_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM remboursement WHERE id_pret = ?");
        $stmt->execute([$pret_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insert($data) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO echeance_remboursement (
                id_pret, mois_annee, montant_total, part_interet, part_capital, statut_paiement, date_paiement_effectif
,rassurance            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data->id_pret,
            $data->mois_annee,
            $data->montant_total,
            $data->part_interet,
            $data->part_capital,
            $data->statut_paiement ?? 'non payé',
            $data->date_paiement_effectif ?? null,
            $data->rassurance ?? 0.00
        ]);
        return $db->lastInsertId();
    }
}