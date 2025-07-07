<?php
require 'vendor/autoload.php';
require 'db.php';
require 'routes/etudiant_routes.php';


Flight::route('POST /ajout-fond', function() {
    $db = getDB();
    $data = Flight::request()->data;

    // Récupération des autres valeurs du formulaire
    $etablissement_id= $data->etablissement_id;
    $montant = $data->montant;
    $type_mouvement = $data->type_mouvement;

    // etablissement_id est toujours 1
    $stmt = $db->prepare("INSERT INTO fond (etablissement_id, montant, type_mouvement) VALUES (?, ?, ?)");
    $stmt->execute([
       $etablissement_id,
        $montant,
        $type_mouvement
    ]);

    Flight::json(['message' => 'Fond ajouté avec succès']);
});

Flight::route('GET /fond-actuel', function () {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT
          COALESCE(SUM(CASE WHEN type_mouvement = 0 THEN montant ELSE 0 END), 0) -
          COALESCE(SUM(CASE WHEN type_mouvement = 1 THEN montant ELSE 0 END), 0) AS fond_actuel
        FROM fond
        WHERE etablissement_id = 1
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    Flight::json(['fond_actuel' => $result['fond_actuel']]);
});

Flight::start();