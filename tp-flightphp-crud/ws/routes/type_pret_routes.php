<?php
require_once __DIR__ . '/../controllers/TypePretController.php';

Flight::route('GET /type-prets', ['TypePretController', 'getAll']);
Flight::route('GET /type-prets/@id', ['TypePretController', 'getById']);
Flight::route('POST /type-prets', ['TypePretController', 'create']);    
Flight::route('PUT /type-prets/@id', ['TypePretController', 'update']);
Flight::route('DELETE /type-prets/@id', ['TypePretController', 'delete']);
?>