<?php 
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/Fond.php';
require_once __DIR__ . '/../helpers/Utils.php';

class FondsController
{
    public static function getFondsDisponible()
    {
        $idEf = Flight::request()->query['id_ef'];
        $debut = Flight::request()->query['debut']; 
        $fin = Flight::request()->query['fin'];     
        if (!$idEf || !$debut || !$fin) {
            Flight::json(['error' => 'Paramètres manquants (id_ef, debut, fin)'], 400);
            return;
        }

        $resultats = Fond::getFondsDisponible($idEf, $debut, $fin);
        Flight::json($resultats);
    }
}

?>