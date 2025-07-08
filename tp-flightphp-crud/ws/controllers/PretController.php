<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/Fond.php';
require_once __DIR__ . '/../models/TypePret.php';


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
        $data = Flight::request()->data;

        $fond_actuel = Fond::getFondActuelParEtablissement($data->id_ef);
        if ($data->montant > $fond_actuel['fond_actuel']) {
            Flight::json([
                'message' => "Le montant demande ($data->montant) depasse le fond actuel ($fond_actuel[fond_actuel])."
            ], 400);
            return;
        }

        $type = TypePret::getById($data->id_type);
        if($type) {
            if($data->montant < $type['montant_min'] || $data->montant > $type['montant_max']) {
                Flight::json([
                    'message' => "Le montant doit être compris entre {$type['montant_min']} et {$type['montant_max']}."
                ], 400);
                return;
            }
        }

        $annuite = self::calculerAnnuite(
            $data->montant,
            $type['taux_interet'],
            $data->duree,
            $data->frequence_remboursement
        );

        $assurance = self::calculerAssurance(
            $data->montant,
            $data->pourcentage_assurance ?? 0, // 1% par défaut
            $data->frequence_remboursement
        );

        // Génération du tableau d'amortissement avec délai
        $tableauAmortissement = self::genererTableauAmortissement(
            $data->montant,
            $type['taux_interet'],
            $data->duree,
            $annuite,
            $data->date_debut,
            $data->delai_mois ?? 0
        );

        try {
            $id = Pret::create($data);
            
            Flight::json([
                'success' => true,
                'id_pret' => $id,
                'details' => [
                    'montant' => $data->montant,
                    'duree' => $data->duree,
                    'taux_interet' => $type['taux_interet'],
                    'taux_assurance' => $type['taux_assurance'] ?? 1,
                    'frequence' => $data->frequence_remboursement,
                    'date_debut' => $data->date_debut,
                    'date_fin' => $data->date_debut + $data->duree + $data->delai_mois,
                    'annuite' => $annuite,
                    'assurance' => $assurance,
                    'total_remboursement' => $annuite * $data->duree,
                    'delai_mois' => $data->delai_mois ?? 0,
                    'tableau_amortissement' => $tableauAmortissement,
                    'is_valide' => false
                ]
            ]);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private static function genererTableauAmortissement($montant, $taux, $duree, $annuite, $dateDebut, $delaiMois = 0) {
        $tableau = [];
        $capitalRestant = $montant;
        $tauxMensuel = $taux / 100 / 12;
        $date = new DateTime($dateDebut);

        // Période de délai (paiement des intérêts seulement)
        for ($periode = 1; $periode <= $delaiMois; $periode++) {
            $interet = $capitalRestant * $tauxMensuel;
            $date->add(new DateInterval('P1M'));

            $tableau[] = [
                'periode' => $periode,
                'date' => $date->format('Y-m-d'),
                'capital_restant' => $capitalRestant,
                'interet' => $interet,
                'capital' => 0,
                'annuite' => $interet, // Seulement les intérêts
                'statut' => 'À payer',
                'phase' => 'delai'
            ];
        }

        // Période de remboursement normal
        for ($periode = $delaiMois + 1; $periode <= $duree + $delaiMois; $periode++) {
            $interet = $capitalRestant * $tauxMensuel;
            $capital = $annuite - $interet;
            $capitalRestant -= $capital;
            $date->add(new DateInterval('P1M'));

            $tableau[] = [
                'periode' => $periode,
                'date' => $date->format('Y-m-d'),
                'capital_restant' => max(0, $capitalRestant),
                'interet' => $interet,
                'capital' => $capital,
                'annuite' => $annuite,
                'statut' => 'À payer',
                'phase' => 'remboursement'
            ];
        }

        return $tableau;
    }

    public static function getByIdWithDetails($id) {
        $pret = Pret::getById($id);
        if(!$pret) {
            Flight::halt(404, json_encode(['message' => 'Prêt non trouvé']));
        }
    
        // Calcul des éléments financiers
        $pret['annuite'] = self::calculerAnnuite(
            $pret['montant'],
            $pret['taux_interet'],
            $pret['duree'],
            $pret['frequence_remboursement']
        );
    
        $pret['assurance'] = self::calculerAssurance(
            $pret['montant'],
            $pret['taux_assurance'] ?? 1,
            $pret['frequence_remboursement']
        );
    
        $pret['tableau_amortissement'] = self::genererTableauAmortissement(
            $pret['montant'],
            $pret['taux_interet'],
            $pret['duree'],
            $pret['annuite'],
            $pret['date_debut'],
            $pret['delai_mois'] ?? 0
        );
    
        Flight::json($pret);
    }

    public static function validerPret($id) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE pret SET is_valide = 1 WHERE id_pret = ?");
            $stmt->execute([$id]);
            
            Flight::json(['success' => true, 'message' => 'Prêt validé avec succès']);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private static function calculerAnnuite($montant, $tauxAnnuel, $duree, $frequence) {
        $periodesParAn = self::getPeriodesParAn($frequence);
        $n = $duree * $periodesParAn;
        $i = $tauxAnnuel / 100 / $periodesParAn;

        return ($montant * $i) / (1 - pow(1 + $i, -$n));
    }


    private static function calculerAssurance($montant, $pourcentage_assurance, $frequence) {
        $periodesParAn = self::getPeriodesParAn($frequence);
        $montantAssurance = ($montant * ($pourcentage_assurance / 100)) / $periodesParAn;
        return round($montantAssurance, 2);
    }

    private static function getPeriodesParAn($frequence) {
        switch ($frequence) {
            case 'mensuel': return 12;
            case 'trimestriel': return 4;
            case 'annuel': return 1;
            default: return 12;
        }
    }

    public static function update($id) {
        $data = Flight::request()->data;
        Pret::update($id, $data);
        Flight::json(['message' => 'Prêt modifié']);
    }

    public static function delete($id) {
        Pret::delete($id);
        Flight::json(['message' => 'Prêt supprimé']);
    }
}