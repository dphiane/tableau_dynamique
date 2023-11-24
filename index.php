<?php

use App\NumberHelper;
use App\TableHelper;
use App\URLHelper;

require '../tableau_dynamique/elements/header.php';
require __DIR__ . '/vendor/autoload.php';

define('PER_PAGE', 20);

$pdo = new PDO("sqlite:./products.db", null, null, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$query = "SELECT * FROM products";
$queryCount = "SELECT COUNT(id) as count FROM products";
$params = [];
$sortable = ['id', 'name', 'city', 'price', 'address'];
// Recherche par Ville
if (!empty($_GET["q"])) {
    $query .= " WHERE city LIKE :city";
    $queryCount .= " WHERE city LIKE :city";
    $params['city'] = '%' . $_GET['q'] . '%';
}
//Organisation

if (!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)) {
    $direction = $_GET['dir'] ?? 'asc';
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $query .= " ORDER BY " . $_GET['sort'] . " $direction";
}
// Pagination
$page = $_GET['p'] ?? 1;
$offset = ($page - 1) * PER_PAGE;

$query .= " LIMIT " . PER_PAGE . " OFFSET $offset";
$statement = $pdo->prepare($query);
$statement->execute($params);
$products = $statement->fetchAll();

$statement = $pdo->prepare($queryCount);
$statement->execute($params);
$count = (int)$statement->fetch()['count'];
$pages = ceil($count / PER_PAGE);
?>
<h1>Les biens immobiliers</h1>
<form action="" class="mb-4">
    <div class="form-group">
        <input class="form-control" type="text" name="q" placeholder="Rechercher par ville" value="<?= isset($_GET['q']) ? htmlentities($_GET['q']) : '' ?>">
    </div>
    <button type="submit" class="btn btn-primary">Rechercher</button>
</form>


<table class="table table-striped">
    <thead>
        <tr>
            <th><?= TableHelper::sort('id', 'ID', $_GET)?></th>
            <th><?= TableHelper::sort('name', 'Nom', $_GET)?></th>
            <th><?= TableHelper::sort('price', 'Prix', $_GET)?></th>
            <th><?= TableHelper::sort('city', 'Ville', $_GET)?></th>
            <th><?= TableHelper::sort('address', 'Adresse', $_GET)?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product) : ?>
            <tr>
                <td>#<?= $product['id'] ?></td>
                <td><?= $product['name'] ?></td>
                <td><?= NumberHelper::price($product['price']) ?></td>
                <td><?= $product['city'] ?></td>
                <td><?= $product['address'] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php if ($pages > 1 && $page > 1) : ?>
    <a href="?<?= URLHelper::withParam($_GET, "p", $page - 1) ?>" name="p" class="btn btn-primary">Page Précédente</a>
<?php endif ?>
<?php if ($pages > 1 && $page < $pages) : ?>
    <a href="?<?= URLHelper::withParam($_GET, "p", $page + 1) ?>" name="p" class="btn btn-primary">Page Suivante</a>
<?php endif ?>
<?php
require '../tableau_dynamique/elements/footer.php';

?>