<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../helpers/Utils.php';

class PretController {

    public static function getAll() {
        $types = Pret::getAll();
        Flight::json($types);
    }

    public static function getById($id) {
        $type = Pret::getById($id);
        Flight::json($type);
    }

    public static function create() {
        $data = Flight::request()->data;
        $id = Pret::create($data);
        Flight::json(['message' => 'Prêt ajouté', 'id' => $id]);
    }

    public static function update($id) {
        parse_str(file_get_contents("php://input"), $put_vars);
        $data = (object) $put_vars;

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

?>