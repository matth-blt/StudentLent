<?php
session_start();

require_once("./lib/database.php");
require_once("./lib/products.php");
require_once("./lib/auth_functions.php");

// Vérifier que l'utilisateur est connecté
requireLogin();

$error = '';
$success = false;

// Récupérer les catégories depuis la base de données
$categories = getCategories();

// Traitement du formulaire produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    // Récupérer et nettoyer les données
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $newCategory = trim($_POST['new_category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $image = trim($_POST['image'] ?? '');
    
    // Si "Autre" est sélectionné, utiliser la nouvelle catégorie
    if ($category === '__other__' && !empty($newCategory)) {
        // Ajouter la nouvelle catégorie à la BDD
        $result = addCategory($newCategory);
        if ($result !== false) {
            $category = $newCategory; // Utiliser la nouvelle catégorie
            $categories = getCategories(); // Recharger les catégories
        } else {
            // La catégorie existe déjà, on peut quand même l'utiliser
            $category = $newCategory;
        }
    }
    
    // Image par défaut si vide
    if (empty($image)) {
        $image = 'https://images.unsplash.com/photo-1560393464-5c69a73c5770?w=400';
    }
    
    // Validation
    if (strlen($title) < 3) {
        $error = "Le titre doit contenir au moins 3 caractères.";
    } elseif ($category === '__other__' || empty($category)) {
        $error = "Veuillez sélectionner ou saisir une catégorie valide.";
    } elseif ($price < 0.01) {
        $error = "Le prix doit être d'au moins 0.01€.";
    } elseif (empty($location)) {
        $error = "Veuillez indiquer une localisation.";
    } else {
        // Tout est OK, on crée le produit avec l'ID de l'utilisateur connecté
        $newId = createProduct($title, $category, $price, $location, $image, $_SESSION['user_id']);
        
        if ($newId) {
            // Redirection vers le catalogue
            header('Location: ./catalogue.php');
            exit;
        } else {
            $error = "Une erreur est survenue lors de la création du produit.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une annonce - StudentLend</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<?php include __DIR__ . '/templates/nav.php'; ?>

<main class="max-w-2xl mx-auto px-4 py-12">
    
    <!-- Titre de la page -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Publier une annonce</h1>
        <p class="text-gray-600">Mettez votre équipement en location</p>
    </div>

    <!-- Formulaire -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        
        <!-- Message d'erreur -->
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_prod.php">
            
            <!-- Titre -->
            <div class="mb-5">
                <label for="title" class="block text-gray-700 font-semibold mb-2">
                    Titre de l'annonce *
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                    placeholder="Ex: Perceuse sans fil Bosch"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>

            <!-- Catégorie -->
            <div class="mb-5">
                <label for="category" class="block text-gray-700 font-semibold mb-2">
                    Catégorie *
                </label>
                <select 
                    id="category" 
                    name="category" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                    onchange="toggleNewCategory()"
                >
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (($_POST['category'] ?? '') === $cat) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__other__" <?php echo (($_POST['category'] ?? '') === '__other__') ? 'selected' : ''; ?>>
                        + Autre catégorie...
                    </option>
                </select>
            </div>
            
            <!-- Champ pour nouvelle catégorie (caché par défaut) -->
            <div id="newCategoryField" class="mb-5 hidden">
                <label for="new_category" class="block text-gray-700 font-semibold mb-2">
                    Nom de la nouvelle catégorie *
                </label>
                <input 
                    type="text" 
                    id="new_category" 
                    name="new_category" 
                    value="<?php echo htmlspecialchars($_POST['new_category'] ?? ''); ?>"
                    placeholder="Ex: Jardinage"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <p class="text-sm text-gray-500 mt-1">Cette catégorie sera ajoutée automatiquement</p>
            </div>

            <!-- Prix -->
            <div class="mb-5">
                <label for="price" class="block text-gray-700 font-semibold mb-2">
                    Prix par jour (€) *
                </label>
                <input 
                    type="number" 
                    id="price" 
                    name="price" 
                    value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                    placeholder="15"
                    min="0.01"
                    step="0.01"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>

            <!-- Localisation -->
            <div class="mb-5">
                <label for="location" class="block text-gray-700 font-semibold mb-2">
                    Localisation *
                </label>
                <input 
                    type="text" 
                    id="location" 
                    name="location" 
                    value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                    placeholder="Ex: Paris 15ème"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>

            <!-- URL de l'image -->
            <div class="mb-6">
                <label for="image" class="block text-gray-700 font-semibold mb-2">
                    URL de l'image (optionnel)
                </label>
                <input 
                    type="url" 
                    id="image" 
                    name="image" 
                    value="<?php echo htmlspecialchars($_POST['image'] ?? ''); ?>"
                    placeholder="https://exemple.com/image.jpg"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <p class="text-sm text-gray-500 mt-1">Laissez vide pour utiliser une image par défaut</p>
            </div>

            <!-- Bouton -->
            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition duration-200"
            >
                Publier mon annonce
            </button>
        </form>

        <!-- Lien retour -->
        <div class="text-center mt-6">
            <a href="./catalogue.php" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Retour au catalogue
            </a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/templates/site-footer.php'; ?>

<script>
function toggleNewCategory() {
    const select = document.getElementById('category');
    const newCategoryField = document.getElementById('newCategoryField');
    const newCategoryInput = document.getElementById('new_category');
    
    if (select.value === '__other__') {
        newCategoryField.classList.remove('hidden');
        newCategoryInput.required = true;
    } else {
        newCategoryField.classList.add('hidden');
        newCategoryInput.required = false;
        newCategoryInput.value = '';
    }
}

// Vérifier au chargement de la page (si erreur de validation)
document.addEventListener('DOMContentLoaded', toggleNewCategory);
</script>

</body>
</html>
