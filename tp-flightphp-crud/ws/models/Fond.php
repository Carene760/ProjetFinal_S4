<?php
require_once __DIR__ . '/../db.php';

class Fond {
    public static function getFondActuelParEtablissement($etablissement_id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type_mouvement = 0 THEN montant ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN type_mouvement = 1 THEN montant ELSE 0 END), 0) AS fond_actuel
            FROM fond
            WHERE etablissement_id = ?
        ");
        $stmt->execute([$etablissement_id]);
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function ajouterFond($etablissement_id, $montant, $type_mouvement) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO fond (etablissement_id, montant, type_mouvement) VALUES (?, ?, ?)");
        $stmt->execute([
            $etablissement_id,
            $montant,
            $type_mouvement
        ]);
    
        return [
            'message' => 'Fond ajouté avec succès'
        ];
    }
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