<?php
require_once __DIR__ . '/../models/Pret.php';

class PretController {
    public static function getAll() {
        $prets = Pret::getAll();
        // Calcul de l'annuité pour chaque prêt
        foreach ($prets as &$pret) {
            $pret['annuite'] = self::calculerAnnuite($pret);
        }
        Flight::json($prets);
    }

    public static function getById($id) {
        $pret = Pret::getById($id);
        $pret['annuite'] = self::calculerAnnuite($pret);
        Flight::json($pret);
    }

    public static function create() {
        $data = Flight::request()->data;
        
        // Vérification des fonds disponibles
        $fondActuel = self::getFondActuel();
        if ($data->montant > $fondActuel) {
            Flight::json(['error' => 'Fonds insuffisants. Fonds disponibles: ' . $fondActuel], 400);
            return;
        }
        
        $id = Pret::create($data);
        $pret = Pret::getById($id);
        $pret['annuite'] = self::calculerAnnuite($pret);
        
        Flight::json([
            'message' => 'Prêt ajouté',
            'id' => $id,
            'annuite' => $pret['annuite']
        ]);
    }

    private static function calculerAnnuite($pret) {
        $montant = $pret['montant'];
        $duree = $pret['duree'];
        $tauxAnnuel = 5; // Taux d'intérêt par défaut (à adapter)
        
        // Conversion selon la fréquence de remboursement
        switch ($pret['frequence_remboursement']) {
            case 'mensuel':
                $periodesParAn = 12;
                break;
            case 'trimestriel':
                $periodesParAn = 4;
                break;
            case 'annuel':
                $periodesParAn = 1;
                break;
            default:
                $periodesParAn = 12;
        }
        
        $n = $duree * $periodesParAn;
        $i = $tauxAnnuel / 100 / $periodesParAn;
        
        if ($i == 0) return $montant / $n; // Cas sans intérêts
        
        return ($montant * $i) / (1 - pow(1 + $i, -$n));
    }

    private static function getFondActuel() {
        $result = Flight::request()
            ->curl('http://localhost/api/fond-actuel')
            ->get()
            ->json();
        return $result['fond_actuel'];
    }
}