<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Pret.php';

class PretController {
    public static function getAll() {
        $prets = Pret::getAll();
        Flight::json($prets);
    }

    public static function getById($id) {
        $pret = Pret::getById($id);
        Flight::json($pret);
    }

    public static function create() {
    $db = getDB();
    $data = Flight::request()->data;
    
    // Vérification des fonds disponibles
    $fondActuel = self::getFondActuel();
    if ($data->montant > $fondActuel) {
        Flight::halt(400, json_encode([
            'message' => 'Fonds insuffisants',
            'fond_actuel' => $fondActuel,
            'montant_demande' => $data->montant
        ]));
    }

    // Récupération du taux d'intérêt
    $typePret = TypePret::getById($data->id_type);
    if (!$typePret) {
        Flight::halt(404, 'Type de prêt non trouvé');
    }

    // Calcul de l'annuité
    $annuite = self::calculerAnnuite(
        $data->montant,
        $typePret['taux_interet'],
        $data->duree,
        $data->frequence_remboursement
    );

    try {
        $db->beginTransaction();

        // Création du prêt
        $idPret = Pret::create($data);
        
        // Création du mouvement de fond (sortie)
        $stmt = $db->prepare("INSERT INTO fond (etablissement_id, montant, type_mouvement) VALUES (1, ?, 1)");
        $stmt->execute([$data->montant]);

        $db->commit();

        Flight::json([
            'message' => 'Prêt créé avec succès',
            'id_pret' => $idPret,
            'annuite' => $annuite,
            'fond_restant' => $fondActuel - $data->montant
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        Flight::halt(500, 'Erreur lors de la création du prêt: ' . $e->getMessage());
    }
}

    private static function calculerAnnuite($montant, $tauxAnnuel, $duree, $frequence) {
        $periodesParAn = self::getPeriodesParAn($frequence);
        $n = $duree * $periodesParAn;
        $i = $tauxAnnuel / 100 / $periodesParAn;

        return ($montant * $i) / (1 - pow(1 + $i, -$n));
    }

    private static function getPeriodesParAn($frequence) {
        switch ($frequence) {
            case 'mensuel': return 12;
            case 'trimestriel': return 4;
            case 'annuel': return 1;
            default: return 12;
        }
    }

    public static function testGenerationEcheance() {
        $pdo = getDB();

        // 🔧 Tu peux fixer l'ID ici pour test rapide
        $idPret = 1;

        EcheanceService::genererEcheancesMensuellesAnnuitesConstantes($pdo, $idPret);

        Flight::json(['message' => "✅ Échéances générées pour le prêt #$idPret"]);
    }

<<<<<<< Updated upstream
    public static function delete($id) {
        Pret::delete($id);
        Flight::json(['message' => 'Prêt supprimé']);
    }
    public static function getInteretsMensuels() {
        $idEf = Flight::request()->query['id_ef'];
        $debut = Flight::request()->query['debut']; 
        $fin = Flight::request()->query['fin'];     
        if (!$idEf || !$debut || !$fin) {
            Flight::json(['error' => 'Paramètres manquants (id_ef, debut, fin)'], 400);
            return;
        }

        $resultats = Pret::getInteretsMensuels($idEf, $debut, $fin);
        Flight::json($resultats);
    }
=======


>>>>>>> Stashed changes
}