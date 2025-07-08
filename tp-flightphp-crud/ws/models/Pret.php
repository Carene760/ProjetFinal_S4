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
}