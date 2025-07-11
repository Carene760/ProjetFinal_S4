<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/Fond.php';
require_once __DIR__ . '/../models/TypePret.php';
require_once __DIR__ . '/../models/Remboursement.php';
require_once __DIR__ . '/../models/Simulation.php';


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

        $id = Pret::create($data);
        Flight::json($id);
    }

    // Ajoutez cette méthode dans la classe PretController
    public static function getDetailsPret($id) {
        $pret = Pret::getById($id);
        
        if (!$pret) {
            Flight::json(['message' => 'Prêt non trouvé'], 404);
            return;
        }

        $type = TypePret::getById($pret['id_type']);
        $fond_actuel = Fond::getFondActuelParEtablissement($pret['id_ef']);
        
        // Calcul des éléments financiers
        $annuite = self::calculerAnnuite(
            $pret['montant'],
            $type['taux_interet'],
            $pret['duree'],
            $pret['frequence_remboursement']
        );

        $assurance = self::calculerAssurance(
            $pret['montant'],
            $pret['pourcentage_assurance'] ?? 0,
            $pret['frequence_remboursement']
        );

        // Calcul des dates
        $date_debut = new DateTime($pret['date_debut']);
        $date_fin = clone $date_debut;
        $date_fin->add(new DateInterval('P' . ($pret['duree'] + ($pret['delai_mois'] ?? 0)) . 'M'));

        // Tableau d'amortissement
        $tableau_amortissement = self::calculerTableauAmortissement(
            $pret['montant'],
            $type['taux_interet'],
            $pret['duree'],
            $pret['frequence_remboursement'],
            $pret['delai_mois'] ?? 0,
            $date_debut,
            $assurance
        );

        $response = [
            'pret' => [
                'id_client' => $pret['id_client'],
                'montant' => $pret['montant'],
                'duree' => $pret['duree'],
                'date_debut' => $pret['date_debut'], // Note: cette clé est utilisée directement dans la réponse
                'frequence_remboursement' => $pret['frequence_remboursement'],
                'pourcentage_assurance' => $pret['pourcentage_assurance'] ?? 0,
                'delai_mois' => $pret['delai_mois'] ?? 0,
                'statut' => $pret['statut'],
                'est_valide' => $pret['est_valide'] // Notez le underscore
            ],
            'type_pret' => [
                'nom' => $type['nom'],
                'taux_interet' => $type['taux_interet']
            ],
            'annuite' => $annuite,
            'assurance' => $assurance,
            'montant_total' => $annuite * $pret['duree'] * self::getPeriodesParAn($pret['frequence_remboursement']),
            'date_debut' => $date_debut->format('Y-m-d'), // Cette clé est dupliquée - problème potentiel
            'date_fin' => $date_fin->format('Y-m-d'),
            'tableau_amortissement' => $tableau_amortissement
        ];
    
        Flight::json($response);
    }

    private static function calculerTableauAmortissement($montant, $tauxAnnuel, $duree, $frequence, $delai_mois, $date_debut, $assurance) {
        $periodesParAn = self::getPeriodesParAn($frequence);
        $n =($duree/$periodesParAn) * $periodesParAn;
        $i = $tauxAnnuel / 100 / $periodesParAn;
        $annuite = ($montant * $i) / (1 - pow(1 + $i, -$n));
        
        $tableau = [];
        $capital_restant = $montant;
        $date = clone $date_debut;
        
        // Période de délai (si delai_mois > 0)
        for ($m = 1; $m <= $delai_mois; $m++) {
            $interet = $capital_restant * $i;
            $date->add(new DateInterval('P1M'));
            
            $tableau[] = [
                'periode' => $m,
                'date' => $date->format('Y-m-d'),
                'capital' => 0,
                'interet' => $interet,
                'assurance' => $assurance,
                'montant_paye' => $interet + $assurance,
                'capital_restant' => $capital_restant
            ];
        }
        
        // Période de remboursement
        for ($p = 1; $p <= $n; $p++) {
            $interet = $capital_restant * $i;
            $capital = $annuite - $interet;
            $capital_restant -= $capital;
            
            if ($capital_restant < 0) $capital_restant = 0;
            
            $date->add(new DateInterval('P1M'));
            
            $tableau[] = [
                'periode' => $delai_mois + $p,
                'date' => $date->format('Y-m-d'),
                'capital' => $capital,
                'interet' => $interet,
                'assurance' => $assurance,
                'montant_paye' => $annuite + $assurance,
                'capital_restant' => $capital_restant
            ];
        }
        
        return $tableau;
    }

    public static function validerPret($id) {
        $db = getDB();
        try {
            $db->beginTransaction();
    
            // Récupération du prêt
            $stmt = $db->prepare("SELECT * FROM pret WHERE id_pret = ?");
            $stmt->execute([$id]);
            $pret = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$pret) {
                throw new Exception("Prêt introuvable.");
            }
    
            if ($pret['est_valide']) {
                throw new Exception("Prêt déjà validé.");
            }
    
            $fondActuel = Fond::getFondActuelParEtablissement($pret['id_ef']);
            if ($pret['montant'] > $fondActuel['fond_actuel']) {
                throw new Exception("Fonds insuffisants dans l'établissement.");
            }
    
            // Étape 1 : MAJ est_valide
            self::updateEstValide($id);
    
            // Étape 2 : sortie de fonds
            self::sortirFond($pret);
    
            // // Étape 3 : échéances
            self::genererEcheances($pret);
    
            $db->commit();
            Flight::json(['success' => true, 'message' => 'Prêt validé avec succès']);
        } catch (Exception $e) {
            $db->rollBack();
            Flight::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public static function ajouterSimulation($id) {
        try {
            $simulationId = Simulation::ajouterSimulation($id);
            Flight::json([
                'success' => true,
                'message' => 'Simulation enregistrée',
                'id_simulation' => $simulationId
            ]);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    private static function updateEstValide($id) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE pret SET est_valide = 1 WHERE id_pret = ?");
        $stmt->execute([$id]);
    }
    
    private static function sortirFond($pret) {
        Fond::ajouterFond($pret['id_ef'], $pret['montant'], 1); // 1 = SORTIE
    }

    private static function genererEcheances($pret) {
        $type = TypePret::getById($pret['id_type']);
        $date_debut = new DateTime($pret['date_debut']);
    
        $assurance = self::calculerAssurance(
            $pret['montant'],
            $pret['pourcentage_assurance'] ?? 0,
            $pret['frequence_remboursement']
        );
    
        $tableau = self::calculerTableauAmortissement(
            $pret['montant'],
            $type['taux_interet'],
            $pret['duree'],
            $pret['frequence_remboursement'],
            $pret['delai_mois'] ?? 0,
            $date_debut,
            $assurance
        );
    
        foreach ($tableau as $ligne) {
            $mois_annee = (new DateTime($ligne['date']))->format('Y-m-d');
    
            $data = (object) [
                'id_pret' => $pret['id_pret'],
                'mois_annee' => $mois_annee,
                'montant_total' => round($ligne['montant_paye'], 2),
                'part_interet' => round($ligne['interet'], 2),
                'part_capital' => round($ligne['capital'], 2),
                'statut_paiement' => 'non payé',
                'date_paiement_effectif' => null,
                'rassurance' => round($ligne['assurance'], 2)
            ];
    
            Remboursement::insert($data);
        }
    }

    public static function getSimulationsByPret($id) {
        $simulations = Simulation::getByPretId($id);
        Flight::json($simulations);
    }
    
    private static function calculerAnnuite($montant, $tauxAnnuel, $duree, $frequence) {
        $periodesParAn = self::getPeriodesParAn($frequence);
        $n = ($duree/$periodesParAn) * $periodesParAn;
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
}