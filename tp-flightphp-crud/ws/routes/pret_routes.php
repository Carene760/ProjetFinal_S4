<?php
require_once __DIR__ . '/../controllers/PretController.php';

Flight::route('GET /prets', ['PretController', 'getAll']);
Flight::route('GET /prets/@id', ['PretController', 'getById']);
Flight::route('POST /prets', ['PretController', 'create']);    
Flight::route('PUT /prets/@id', ['PretController', 'update']);
Flight::route('DELETE /prets/@id', ['PretController', 'delete']);

Flight::route('GET /pret/interets-mensuels', ['PretController', 'getInteretsMensuels']);
// Flight::route('GET /pret/interets-mensuels', function() {
//     // Données de test
//     $testData = [
//         [
//             "mois" => "2023-01",
//             "interets_recus" => 1000.50,
//             "interets_courus" => 500.25,
//             "interets_totaux" => 1500.75
//         ],
//         [
//             "mois" => "2023-02",
//             "interets_recus" => 1200.00,
//             "interets_courus" => 600.00,
//             "interets_totaux" => 1800.00
//         ]
//     ];
    
//     Flight::json($testData);
// });
// Flight::route('GET /pret/interets-mensuels', function() {
//     try {
//         // Réponse de test minimale
//         $testData = [
//             ['mois' => '2023-01', 'interets' => 1000],
//             ['mois' => '2023-02', 'interets' => 1500]
//         ];
//         Flight::json($testData);
//     } catch (Exception $e) {
//         Flight::halt(500, json_encode([
//             'error' => $e->getMessage(),
//             'trace' => $e->getTrace() // Seulement en développement
//         ]));
//     }
// });
?>