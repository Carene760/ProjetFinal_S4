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
        parse_str(file_get_contents("php://input"), $put_vars);
        $data = (object) $put_vars;

        TypePret::update($id, $data);
        Flight::json(['message' => 'Type de prêt modifié']);
    }


    public static function delete($id) {
        TypePret::delete($id);
        Flight::json(['message' => 'Type de prêt supprimé']);
    }
}

?>