<?php
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('GET /prets', ['PretController', 'getAll']);
Flight::route('GET /prets/@id', ['PretController', 'getById']);
Flight::route('POST /prets', ['PretController', 'create']);
Flight::route('PUT /prets/@id', ['PretController', 'update']);
Flight::route('DELETE /prets/@id', ['PretController', 'delete']);