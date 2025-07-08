<?php
require_once __DIR__ . '/../models/Echeance.php';

class EcheanceController {

    public static function getNonPayesParClient($id_client) {
        try {
            $echeances = Echeance::getRemboursementsNonPayesParClient($id_client);
            Flight::json($echeances);
        } catch (Exception $ex) {
            Flight::json(['error' => $ex->getMessage()], 500);
        }
    }
}

