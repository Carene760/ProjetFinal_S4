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
}