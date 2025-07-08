<?php
require_once __DIR__ . '/../controllers/EcheanceController.php';

Flight::route('GET /remboursements-non-payes/@id', ['EcheanceController', 'getNonPayesParClient']);
