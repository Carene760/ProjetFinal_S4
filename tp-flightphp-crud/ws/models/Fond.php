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

}