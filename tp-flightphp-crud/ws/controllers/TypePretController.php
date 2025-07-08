<?php
require_once __DIR__ . '/../models/TypePret.php';
require_once __DIR__ . '/../helpers/Utils.php';

class TypePretController {
    public static function getAll() {
        $types = TypePret::getAll();
        Flight::json($types);
    }

    public static function getById($id) {
        $type = TypePret::getById($id);
        Flight::json($type);
    }

    public static function create() {
        $data = Flight::request()->data;
        $id = TypePret::create($data);
        Flight::json(['message' => 'Type de prêt ajouté', 'id' => $id]);
    }

    public static function update($id) {
        $data = Flight::request()->data;
        Pret::update($id, $data);
        Flight::json(['message' => 'Prêt modifié']);
    }


    public static function delete($id) {
        TypePret::delete($id);
        Flight::json(['message' => 'Type de prêt supprimé']);
    }

    public static function getFondActuel() {
        $fondActuel = Pret::getFondActuel(); 
        Flight::json(['fond_actuel' => $fondActuel]);
    }

}

?>