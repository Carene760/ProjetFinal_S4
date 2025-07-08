<?php
require_once '../ws/db.php';
require_once '../ws/models/rembourser.php';
$id = $_GET['id'] ?? null;

if (!$id) {
    die("❌ ID client manquant.");
}
else{
$prets = TypePret::getEcheancesNonPayeesParClient($id);
}

?>
<h2>Prêts du client #<?= htmlspecialchars($id) ?></h2>

<?php if (empty($prets)): ?>
    <p>Aucun prêt trouvé pour ce client.</p>
<?php else: ?>
    <?php foreach ($prets as $pret): ?>
        <h3>Prêt n° <?= htmlspecialchars($pret['id_pret']) ?> - Montant : <?= htmlspecialchars($pret['montant']) ?> €</h3>
        <p>Durée : <?= htmlspecialchars($pret['duree']) ?> mois | Statut : <?= htmlspecialchars($pret['statut']) ?></p>

        <?php if (empty($pret['echeances'])): ?>
            <p>Toutes les échéances sont payées pour ce prêt.</p>
        <?php else: ?>
            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date échéance</th>
                        <th>Montant total</th>
                        <th>Part intérêt</th>
                        <th>Part capital</th>
                        <th>Assurance</th>
                        <th>Statut paiement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pret['echeances'] as $echeance): ?>
                        <tr>
                            <td><?= htmlspecialchars($echeance['mois_annee']) ?></td>
                            <td><?= htmlspecialchars($echeance['montant_total']) ?> €</td>
                            <td><?= htmlspecialchars($echeance['part_interet']) ?> €</td>
                            <td><?= htmlspecialchars($echeance['part_capital']) ?> €</td>
                            <td><?= htmlspecialchars($echeance['rassurance']) ?> €</td>
                            <td><?= htmlspecialchars($echeance['statut_paiement']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
