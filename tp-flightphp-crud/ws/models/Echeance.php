<?php
require_once __DIR__ . '/../db.php';

class Echeance {
    public static function getRemboursementsNonPayesParClient($id_client) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT 
              p.id_pret AS pret_id,
              p.id_client,
              p.id_type,
              p.id_ef,
              p.montant,
              p.duree,
              p.date_debut,
              p.statut,
              p.est_valide,
              p.delai_mois,
              er.id AS echeance_id,
              er.id_pret AS echeance_id_pret,
              er.mois_annee,
              er.montant_total,
              er.part_interet,
              er.part_capital,
              er.date_paiement_effectif,
              er.rassurance
            FROM pret p 
            JOIN echeance_remboursement er ON p.id_pret = er.id_pret 
            WHERE p.id_client = ?
            AND er.statut_paiement = 'non payé'
        ");
        $stmt->execute([$id_client]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}