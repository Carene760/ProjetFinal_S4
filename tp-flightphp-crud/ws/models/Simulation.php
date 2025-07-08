<?php
require_once __DIR__ . '/../db.php';

class Simulation {
    public static function ajouterSimulation($id_pret) {
        $db = getDB();
        
        // Vérifier que le prêt existe et n'est pas déjà validé
        $stmt = $db->prepare("SELECT est_valide FROM pret WHERE id_pret = ?");
        $stmt->execute([$id_pret]);
        $pret = $stmt->fetch();

        if (!$pret) {
            throw new Exception("Prêt non trouvé");
        }

        if ($pret['est_valide']) {
            throw new Exception("Impossible de simuler un prêt déjà validé");
        }

        // Insérer uniquement l'id_pret
        $stmt = $db->prepare("INSERT INTO simulation (id_pret) VALUES (?)");
        $stmt->execute([$id_pret]);
        
        return $db->lastInsertId();
    }

    public static function getByPretId($id_pret) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM simulation WHERE id_pret = ?");
        $stmt->execute([$id_pret]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}