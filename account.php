<?php session_start();

require_once("./lib/auth_functions.php");
require_once("./lib/database.php");
require_once("./lib/products.php");
require_once("./lib/rents.php");

requireLogin();

// Récupérer les produits de l'utilisateur connecté
$userProducts = getProductsByUserId($_SESSION['user_id']);

// Récupérer les locations de l'utilisateur (en tant que locataire)
$userRents = getUserRents($_SESSION['user_id']);

// Récupérer les locations reçues (en tant que propriétaire)
$ownerRents = getOwnerRents($_SESSION['user_id']);

function getColor() {
    $colors = [
        'from-blue-500 to-purple-600',
        'from-green-500 to-teal-600',
        'from-pink-500 to-red-600',
        'from-yellow-500 to-orange-600'
    ];
    return $colors[$_SESSION['user_id'] % count($colors)];
}

function getStatusBadge($status) {
    switch ($status) {
        case 'confirmed':
            return '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Confirmée</span>';
        case 'pending':
            return '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">En attente</span>';
        case 'completed':
            return '<span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">Terminée</span>';
        case 'cancelled':
            return '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">Annulée</span>';
        default:
            return '<span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">' . $status . '</span>';
    }
}

?>

<?php include __DIR__ . '/templates/header.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-12">

    <!-- Étape 3 : En-tête avec avatar -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
        <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div class="w-20 h-20 bg-gradient-to-br <?= getColor(); ?> rounded-full flex items-center justify-center text-white text-3xl font-bold">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            
            <!-- Titre et message -->
            <div>
                <h1 class="text-3xl font-bold">Mon compte</h1>
                <p class="text-gray-600">
                    Bienvenue, <?= $_SESSION['username']; ?> !
                </p>
            </div>
        </div>
    </div>

    <!-- Étape 4 : Informations personnelles -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
        <h2 class="text-lg font-semibold mb-3">Informations personnelles</h2>
        <div class="space-y-2">
            <!-- ID utilisateur -->
            <div class="flex">
                <span class="font-medium text-gray-700 w-40">ID utilisateur :</span>
                <span class="text-gray-900">
                    <?= htmlspecialchars($_SESSION['user_id']); ?>
                </span>
            </div>
            
            <!-- Nom d'utilisateur -->
            <div class="flex">
                <span class="font-medium text-gray-700 w-40">Nom d'utilisateur :</span>
                <span class="text-gray-900">
                    <?= htmlspecialchars($_SESSION['username']); ?>
                </span>
            </div>
            
            <!-- Email -->
            <div class="flex">
                <span class="font-medium text-gray-700 w-40">Email :</span>
                <span class="text-gray-900">
                    <?= htmlspecialchars($_SESSION['email']); ?>
                </span>
            </div>

            <div class="flex">
                <span class="font-medium text-gray-700 w-40">Membre depuis :</span>
                <span class="text-gray-900">Janvier 2025</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Mes statistiques</h2>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-3xl font-bold text-blue-600"><?= count($userRents); ?></div>
                <div class="text-sm text-gray-600">Locations effectuées</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-green-600"><?= count($userProducts); ?></div>
                <div class="text-sm text-gray-600">Annonces</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-purple-600"><?= count($ownerRents); ?></div>
                <div class="text-sm text-gray-600">Locations reçues</div>
            </div>
        </div>
    </div>

    <!-- Section "Mes locations" (en tant que locataire) -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Mes locations</h2>
        
        <?php if (empty($userRents)): ?>
        <div class="text-center py-8 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p>Vous n'avez pas encore effectué de location</p>
            <a href="catalogue.php" class="inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Parcourir le catalogue
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($userRents as $rent): ?>
            <div class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-400 transition-colors">
                <img src="<?= htmlspecialchars($rent['image']); ?>" alt="" class="w-20 h-20 object-cover rounded-lg">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold"><?= htmlspecialchars($rent['title']); ?></h3>
                            <p class="text-sm text-gray-500">Propriétaire : <?= htmlspecialchars($rent['owner_name']); ?></p>
                        </div>
                        <?= getStatusBadge($rent['status']); ?>
                    </div>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                        <span>Du <?= date('d/m/Y', strtotime($rent['start_date'])); ?></span>
                        <span>au <?= date('d/m/Y', strtotime($rent['end_date'])); ?></span>
                        <span class="font-semibold text-blue-600"><?= number_format($rent['total_price'], 2, ',', ' '); ?> €</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Section "Demandes reçues" (en tant que propriétaire) -->
    <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Demandes de location reçues</h2>
        
        <?php if (empty($ownerRents)): ?>
        <div class="text-center py-8 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/>
            </svg>
            <p>Aucune demande de location reçue</p>
            <p class="text-sm mt-1">Publiez des annonces pour recevoir des demandes !</p>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($ownerRents as $rent): ?>
            <div class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-green-400 transition-colors">
                <img src="<?= htmlspecialchars($rent['image']); ?>" alt="" class="w-20 h-20 object-cover rounded-lg">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold"><?= htmlspecialchars($rent['title']); ?></h3>
                            <p class="text-sm text-gray-500">Locataire : <?= htmlspecialchars($rent['renter_name']); ?></p>
                        </div>
                        <?= getStatusBadge($rent['status']); ?>
                    </div>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                        <span>Du <?= date('d/m/Y', strtotime($rent['start_date'])); ?></span>
                        <span>au <?= date('d/m/Y', strtotime($rent['end_date'])); ?></span>
                        <span class="font-semibold text-green-600">+<?= number_format($rent['total_price'], 2, ',', ' '); ?> €</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Étape 6 : Section "Mes annonces" -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Mes annonces</h2>
            <a href="./add_prod.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                + Nouvelle annonce
            </a>
        </div>
        
        <?php if (empty($userProducts)): ?>
        <div class="text-center py-8 text-gray-500">
            <!-- Icône -->
            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            
            <!-- Messages -->
            <p>Aucune annonce publiée</p>
            <p class="text-sm mt-2">Commencez à publier vos équipements en location !</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($userProducts as $product): ?>
            <a href="./product.php?id=<?= $product->getId(); ?>" class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-400 hover:shadow-md transition-all">
                <!-- Image du produit -->
                <img src="<?= htmlspecialchars($product->image); ?>" alt="<?= htmlspecialchars($product->title); ?>" class="w-20 h-20 object-cover rounded-lg">
                
                <!-- Infos du produit -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($product->title); ?></h3>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($product->category); ?></p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-blue-600 font-bold"><?= number_format($product->price, 2, ',', ' '); ?>€</span>
                        <span class="text-gray-400">/jour</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</main>

<?php include __DIR__ . '/templates/footer.php'; ?>
