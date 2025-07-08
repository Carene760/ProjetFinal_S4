<?php
require_once __DIR__ . '/../db.php';

class EF {

    public static function getById($idEf) {
        $db = getDB();
        $stmt = $db -> prepare("SELECT * FROM etablissement_financier WHERE id = ?");
        $stmt = execute($idEf);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}