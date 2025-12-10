<?php 
require_once __DIR__ . '/lib/database.php';
require_once __DIR__ . '/lib/products.php';

// Récupérer la catégorie filtrée (si présente)
$categoryFilter = $_GET['category'] ?? null;

// Récupérer les catégories disponibles
$categories = getCategories();

// Récupérer les produits (filtrés ou tous)
if ($categoryFilter && $categoryFilter !== 'all') {
    $products = getProductsByCategory($categoryFilter);
} else {
    $products = getProducts();
}
?>
<?php include __DIR__ . '/templates/header.php'; ?>

<!-- Section titre et recherche -->
<section class="bg-gradient-to-b from-indigo-50 to-white py-12">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-3">Catalogue d'équipements</h1>
        <p class="text-gray-600 text-center mb-6">Découvrez tous les équipements disponibles à la location</p>

        <!-- Barre de recherche -->
        <div class="max-w-2xl mx-auto">
            <div class="p-3 bg-white rounded-xl shadow-lg">
                <div class="flex gap-2 items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" placeholder="Rechercher un équipement..." class="flex-1 p-2 outline-none">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Rechercher</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filtres par catégorie -->
<section class="py-6 border-b border-gray-200 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex gap-2 flex-wrap justify-center">
            <a href="catalogue.php" class="px-4 py-2 <?php echo !$categoryFilter || $categoryFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-full text-sm">Tous</a>
            <?php foreach ($categories as $cat): ?>
            <a href="catalogue.php?category=<?php echo urlencode($cat); ?>" class="px-4 py-2 <?php echo $categoryFilter === $cat ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-full text-sm">
                <?php echo htmlspecialchars($cat); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Grille de produits -->
<section class="py-12 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600"><?php echo count($products); ?> équipements disponibles</p>
            <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option>Trier par : Plus récents</option>
                <option>Prix croissant</option>
                <option>Prix décroissant</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <a href="product.php?id=<?php echo $product->id; ?>" class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow overflow-hidden group">
                <!-- Image du produit -->
                <div class="relative aspect-square overflow-hidden">
                    <img src="<?php echo $product->image; ?>" alt="<?php echo $product->title; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-3 left-3">
                        <span class="px-3 py-1 bg-white text-xs font-medium rounded-full shadow">
                            <?php echo $product->category; ?>
                        </span>
                    </div>
                    <button class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full shadow hover:bg-gray-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Informations du produit -->
                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2 line-clamp-2"><?php echo $product->title; ?></h3>

                    <div class="flex items-center gap-1 text-sm text-gray-600 mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span><?php echo $product->location; ?></span>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <div>
                            <span class="text-2xl font-bold text-blue-600"><?php echo $product->
                            price; ?>€</span>
                            <span class="text-sm text-gray-600">/jour</span>
                        </div>
                        <span class="text-sm px-3 py-1 bg-green-50 text-green-600 rounded-full">Disponible</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
