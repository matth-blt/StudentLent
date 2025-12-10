<?php
session_start();

require_once __DIR__ . '/lib/database.php';
require_once __DIR__ . '/lib/products.php';
require_once __DIR__ . '/lib/rents.php';
require_once __DIR__ . '/lib/auth_functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogue.php');
    exit;
}

// Récupérer les données
$productId = (int) ($_POST['product_id'] ?? 0);
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$totalPrice = (float) ($_POST['total_price'] ?? 0);

$error = '';
$success = false;
$rentId = null;

// Récupérer le produit
$product = findProductById($productId);

if (!$product) {
    $error = "Produit introuvable.";
} elseif (empty($startDate) || empty($endDate)) {
    $error = "Dates invalides.";
} elseif ($startDate > $endDate) {
    $error = "La date de fin doit être après la date de début.";
} elseif ($startDate < date('Y-m-d')) {
    $error = "La date de début ne peut pas être dans le passé.";
} elseif ((int)$_SESSION['user_id'] === $product->getUserId()) {
    $error = "Vous ne pouvez pas louer votre propre produit.";
} elseif (!isProductAvailable($productId, $startDate, $endDate)) {
    $error = "Ce produit n'est plus disponible pour les dates sélectionnées.";
} else {
    // Recalculer le prix pour vérification de sécurité
    $calculatedPrice = calculateRentPrice($product->price, $startDate, $endDate);
    
    // Tolérance de 0.01€ pour les arrondis
    if (abs($calculatedPrice - $totalPrice) > 0.01) {
        $error = "Erreur de calcul du prix. Veuillez réessayer.";
    } else {
        // Simulation du paiement (toujours succès)
        $paymentSuccess = true;
        
        if ($paymentSuccess) {
            // Créer la location
            $rentId = createRent(
                (int) $_SESSION['user_id'],
                $productId,
                $startDate,
                $endDate,
                $calculatedPrice,
                'confirmed'
            );
            
            if ($rentId) {
                $success = true;
            } else {
                $error = "Une erreur est survenue lors de la création de la location.";
            }
        } else {
            $error = "Le paiement a échoué. Veuillez réessayer.";
        }
    }
}

// Récupérer les détails de la location si succès
$rent = $success ? getRentById($rentId) : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Location confirmée' : 'Erreur'; ?> - StudentLend</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<?php include __DIR__ . '/templates/nav.php'; ?>

<main class="max-w-2xl mx-auto px-4 py-12">

    <?php if ($success && $rent): ?>
    <!-- Succès -->
    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <!-- Animation de succès -->
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Location confirmée !</h1>
        <p class="text-gray-600 mb-6">Votre demande de location a été validée avec succès.</p>

        <!-- Récapitulatif -->
        <div class="bg-gray-50 rounded-lg p-6 text-left mb-6">
            <h2 class="font-semibold mb-4">Détails de votre location</h2>
            
            <div class="flex gap-4 mb-4">
                <img src="<?php echo htmlspecialchars($rent['image']); ?>" alt="" class="w-16 h-16 object-cover rounded-lg">
                <div>
                    <h3 class="font-semibold"><?php echo htmlspecialchars($rent['title']); ?></h3>
                    <p class="text-sm text-gray-500">Propriétaire : <?php echo htmlspecialchars($rent['owner_name']); ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Du</p>
                    <p class="font-semibold"><?php echo date('d/m/Y', strtotime($rent['start_date'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Au</p>
                    <p class="font-semibold"><?php echo date('d/m/Y', strtotime($rent['end_date'])); ?></p>
                </div>
            </div>
            
            <div class="border-t mt-4 pt-4 flex justify-between">
                <span class="text-gray-600">Montant payé</span>
                <span class="font-bold text-green-600"><?php echo number_format($rent['total_price'], 2, ',', ' '); ?> €</span>
            </div>
            
            <div class="mt-2 text-sm text-gray-500">
                Référence : #<?php echo str_pad($rentId, 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <!-- Info contact -->
        <div class="bg-blue-50 rounded-lg p-4 text-left mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-blue-900">Prochaine étape</p>
                    <p class="text-blue-700">Le propriétaire va vous contacter pour organiser la remise du matériel.</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="account.php" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Voir mes locations
            </a>
            <a href="catalogue.php" class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Retour au catalogue
            </a>
        </div>
    </div>

    <?php else: ?>
    <!-- Erreur -->
    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Échec de la location</h1>
        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error); ?></p>

        <div class="flex gap-4">
            <?php if ($product): ?>
            <a href="product.php?id=<?php echo $productId; ?>" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Réessayer
            </a>
            <?php endif; ?>
            <a href="catalogue.php" class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Retour au catalogue
            </a>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php include __DIR__ . '/templates/site-footer.php'; ?>

</body>
</html>
