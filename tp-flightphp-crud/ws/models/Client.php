<?php
require_once __DIR__ . '/../db.php';

class Client {

    public static function getById($idClient) {
        $db = getDB();
        $stmt = $db -> prepare("SELECT * FROM client WHERE id = ?");
        $stmt = execute($idClient);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}