<?php
session_start();

require_once __DIR__ . '/lib/database.php';
require_once __DIR__ . '/lib/products.php';
require_once __DIR__ . '/lib/rents.php';
require_once __DIR__ . '/lib/auth_functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Récupérer les paramètres
$productId = (int) ($_GET['product_id'] ?? 0);
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$error = '';

// Récupérer le produit
$product = findProductById($productId);

if (!$product) {
    header('Location: catalogue.php');
    exit;
}

// Vérifications
if (empty($startDate) || empty($endDate)) {
    $error = "Veuillez sélectionner les dates de location.";
} elseif ($startDate > $endDate) {
    $error = "La date de fin doit être après la date de début.";
} elseif ($startDate < date('Y-m-d')) {
    $error = "La date de début ne peut pas être dans le passé.";
} elseif ((int)$_SESSION['user_id'] === $product->getUserId()) {
    $error = "Vous ne pouvez pas louer votre propre produit.";
} elseif (!isProductAvailable($productId, $startDate, $endDate)) {
    $error = "Ce produit n'est pas disponible pour les dates sélectionnées.";
}

// Calculs
$days = 0;
$totalPrice = 0;
$hasDiscount = false;

if (empty($error)) {
    $days = calculateRentDays($startDate, $endDate);
    $totalPrice = calculateRentPrice($product->price, $startDate, $endDate);
    $hasDiscount = $days >= 7;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif de location - StudentLend</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<?php include __DIR__ . '/templates/nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-12">

    <!-- Titre -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Récapitulatif de votre location</h1>
        <p class="text-gray-600">Vérifiez les détails avant de confirmer</p>
    </div>

    <?php if ($error): ?>
    <!-- Message d'erreur -->
    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Impossible de continuer</h2>
        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error); ?></p>
        <a href="product.php?id=<?php echo $productId; ?>" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Retour au produit
        </a>
    </div>

    <?php else: ?>
    <!-- Récapitulatif -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        
        <!-- Produit -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Produit</h2>
            <div class="flex gap-4">
                <img src="<?php echo htmlspecialchars($product->image); ?>" alt="<?php echo htmlspecialchars($product->title); ?>" class="w-24 h-24 object-cover rounded-lg">
                <div>
                    <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($product->title); ?></h3>
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($product->category); ?></p>
                    <p class="text-gray-500 text-sm flex items-center gap-1 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <?php echo htmlspecialchars($product->location); ?>
                    </p>
                    <p class="text-gray-500 text-sm mt-1">Propriétaire : <?php echo htmlspecialchars($product->getUsername()); ?></p>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Période de location</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Date de début</p>
                    <p class="font-semibold text-lg"><?php echo date('d/m/Y', strtotime($startDate)); ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Date de fin</p>
                    <p class="font-semibold text-lg"><?php echo date('d/m/Y', strtotime($endDate)); ?></p>
                </div>
            </div>
            <p class="text-center text-gray-600 mt-4">
                Durée : <span class="font-semibold"><?php echo $days; ?> jour<?php echo $days > 1 ? 's' : ''; ?></span>
            </p>
        </div>

        <!-- Prix -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Détail du prix</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Prix par jour</span>
                    <span><?php echo number_format($product->price, 2, ',', ' '); ?> €</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Nombre de jours</span>
                    <span><?php echo $days; ?></span>
                </div>
                <?php if ($hasDiscount): ?>
                <div class="flex justify-between text-green-600">
                    <span>Réduction semaine (-10%)</span>
                    <span>-<?php echo number_format($product->price * $days * 0.1, 2, ',', ' '); ?> €</span>
                </div>
                <?php endif; ?>
                <div class="border-t pt-3 flex justify-between text-xl font-bold">
                    <span>Total</span>
                    <span class="text-blue-600"><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span>
                </div>
            </div>
        </div>

        <!-- Caution -->
        <div class="p-6 border-b border-gray-200 bg-yellow-50">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-semibold text-yellow-800">Caution</p>
                    <p class="text-sm text-yellow-700">Une caution de 50€ sera demandée et remboursée après retour du matériel en bon état.</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="p-6">
            <form action="process_rent.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                <input type="hidden" name="total_price" value="<?php echo $totalPrice; ?>">
                
                <button type="submit" class="w-full py-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirmer et payer <?php echo number_format($totalPrice, 2, ',', ' '); ?> €
                </button>
            </form>
            
            <a href="product.php?id=<?php echo $productId; ?>" class="block text-center mt-4 text-gray-600 hover:text-gray-800">
                ← Modifier les dates
            </a>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php include __DIR__ . '/templates/site-footer.php'; ?>

</body>
</html>
