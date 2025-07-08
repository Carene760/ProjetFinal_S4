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
        $stmt = $db->prepare("INSERT INTO pret (id_client, id_type, id_ef, montant, duree, date_heure, frequence_remboursement, statut, est_valide) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data->id_client, $data->id_type, $data->id_ef, $data->montant, $data->duree, $data->date_heure, $data->frequence_remboursement, $data->statut, $data->est_valide]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret SET id_client = ?, id_type = ?, id_ef = ?, montant = ?, duree = ?, date_heure = ?, frequence_remboursement = ?, statut = ?, est_valide = ? WHERE id_pret = ?");
        $stmt->execute([$data->id_client, $data->id_type, $data->id_ef, $data->montant, $data->duree, $data->date_heure, $data->frequence_remboursement, $data->statut, $data->est_valide]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM pret WHERE id_pret = ?");
        $stmt->execute([$id]);
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
?>

