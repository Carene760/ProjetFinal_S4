<?php
require_once __DIR__ . '/../controllers/FondsController.php';



Flight::route('GET /fonds/fonds-disponible', ['FondsController', 'getFondsDisponible']);

?>