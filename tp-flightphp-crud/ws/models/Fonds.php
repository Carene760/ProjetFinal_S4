<?php
require_once __DIR__ . '/../db.php';

class Fonds{
    public static function getFondsDisponible($idEf, $debut, $fin) {
    $db = getDB(); // Votre connexion PDO
    
    try {
        // Préparation de l'appel
        $stmt = $db->prepare("CALL GetFondsDisponibles(:id_ef, :debut, :fin)");
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