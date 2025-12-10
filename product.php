<?php 
require_once('lib/database.php');
require_once('lib/products.php');
require_once('lib/rents.php');
require_once('./lib/auth_functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = $_GET['id'] ?? null;
$product = null;

if ($id) {
    $product = findProductById($id);
}

if (!$product) {
    header('Location: catalogue.php');
    exit;
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    if (isLoggedIn() && (int)$_SESSION['user_id'] === $product->getUserId()) {
        $deleted = deleteProduct((int)$id, (int)$_SESSION['user_id']);
        if ($deleted) {
            header('Location: catalogue.php');
            exit;
        }
    }
}

// Vérifier si l'utilisateur connecté est le propriétaire du produit
$isOwner = isLoggedIn() && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $product->getUserId();

// Calculer le prix semaine
$weeklyPrice = getWeeklyPrice($product->getId());

// Vérifier la disponibilité actuelle du produit
$isCurrentlyRented = isProductCurrentlyRented($product->getId());
$nextAvailability = $isCurrentlyRented ? getNextAvailability($product->getId()) : null;
$today = date('Y-m-d');

// Récupérer les dates réservées pour le calendrier
$reservedRanges = getReservedDateRanges($product->getId());
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product->title); ?> - StudentLend</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flatpickr CSS et JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <style>
        /* Style pour les dates réservées (en rouge) */
        .flatpickr-day.reserved {
            background-color: #FEE2E2 !important;
            color: #DC2626 !important;
            text-decoration: line-through;
            pointer-events: none;
        }
        .flatpickr-day.reserved:hover {
            background-color: #FECACA !important;
        }
        /* Style pour les dates sélectionnées */
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background-color: #2563EB !important;
            border-color: #2563EB !important;
        }
        .flatpickr-day.inRange {
            background-color: #DBEAFE !important;
            box-shadow: -5px 0 0 #DBEAFE, 5px 0 0 #DBEAFE;
        }
        /* Légende */
        .calendar-legend {
            display: flex;
            gap: 16px;
            font-size: 12px;
            margin-top: 8px;
        }
        .calendar-legend span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .legend-available { background-color: #22C55E; }
        .legend-reserved { background-color: #DC2626; }
        .legend-selected { background-color: #2563EB; }
    </style>
</head>
<body class="bg-gray-50">

<!-- Navigation spécifique produit -->
<header class="py-4 border-b border-gray-200 bg-white">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-4">
        <a href="catalogue.php" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Retour</span>
        </a>
        <div class="flex items-center gap-2 font-bold">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span>StudentLend</span>
        </div>
        <div class="flex gap-4 text-sm items-center">
            <?php if (isLoggedIn()): ?>
                <!-- Navigation pour utilisateur CONNECTÉ -->
                <a class="hover:underline" href="account.php">Mon compte</a>
                <span class="text-gray-700">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700" href="templates/logout.php">Déconnexion</a>
            <?php else: ?>
                <!-- Navigation pour utilisateur NON CONNECTÉ -->
                <a class="hover:underline" href="templates/login.php">Connexion</a>
                <a class="px-3 py-1 bg-blue-600 text-white rounded-md" href="#signup">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Contenu principal -->
<main class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Colonne gauche : Galerie d'images -->
        <div>
            <div class="relative bg-white rounded-xl overflow-hidden shadow">
                <img src="<?php echo htmlspecialchars($product->image); ?>" alt="<?php echo htmlspecialchars($product->title); ?>" class="w-full aspect-square object-cover">
                <div class="absolute top-4 right-4 flex gap-2">
                    <button class="w-10 h-10 bg-white rounded-full shadow hover:bg-gray-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                    <?php if ($isOwner): ?>
                    <!-- Bouton Supprimer (visible uniquement pour le propriétaire) -->
                    <button type="button" onclick="openDeleteModal()" class="w-10 h-10 bg-white rounded-full shadow hover:bg-red-50 flex items-center justify-center" title="Supprimer">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Miniatures -->
            <div class="grid grid-cols-4 gap-2 mt-4">
                <div class="border-2 border-blue-600 rounded-lg overflow-hidden cursor-pointer">
                    <img src="<?php echo htmlspecialchars($product->image); ?>" alt="Vue 1" class="w-full aspect-square object-cover">
                </div>
            </div>
        </div>

        <!-- Colonne droite : Informations produit -->
        <div>
            <div class="bg-white rounded-xl shadow p-6">
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-600 text-xs font-medium rounded-full mb-3"><?php echo htmlspecialchars($product->category); ?></span>

                <h1 class="text-2xl font-bold mb-3"><?php echo htmlspecialchars($product->title); ?></h1>

                <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Par <?php echo htmlspecialchars($product->getUsername()); ?></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span><?php echo htmlspecialchars($product->location); ?></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Publié il y a 3 jours</span>
                    </div>
                </div>

                <!-- Prix -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-end justify-between mb-2">
                        <div>
                            <span class="text-3xl font-bold"><?php echo number_format($product->price, 2, ',', ' '); ?>€</span>
                            <span class="text-gray-600"> /jour</span>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Prix semaine (-10%)</div>
                            <div class="text-xl font-bold text-blue-600"><?php echo number_format($weeklyPrice, 2, ',', ' '); ?>€</div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Caution: 50€ remboursée après retour</p>
                </div>

                <!-- Formulaire de demande -->
                <div class="border-t pt-6">
                    <h3 class="font-semibold mb-4">Demander une location</h3>

                    <form action="rent_summary.php" method="GET">
                        <input type="hidden" name="product_id" value="<?php echo $product->getId(); ?>">
                        
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Date de début</label>
                                <input type="text" id="start_date" name="start_date" placeholder="Sélectionner" required readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm cursor-pointer bg-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Date de fin</label>
                                <input type="text" id="end_date" name="end_date" placeholder="Sélectionner" required readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm cursor-pointer bg-white">
                            </div>
                        </div>

                        <!-- Légende du calendrier -->
                        <div class="calendar-legend mb-4 text-gray-600">
                            <span><span class="legend-dot legend-available"></span> Disponible</span>
                            <span><span class="legend-dot legend-reserved"></span> Réservé</span>
                            <span><span class="legend-dot legend-selected"></span> Sélectionné</span>
                        </div>

                        <?php if ($isCurrentlyRented): ?>
                        <div class="flex items-start gap-2 p-3 bg-orange-50 rounded-lg mb-4">
                            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="text-sm">
                                <div class="font-medium text-orange-900">Actuellement loué</div>
                                <p class="text-orange-700">Ce produit sera disponible à partir du <?php echo date('d/m/Y', strtotime($nextAvailability)); ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="flex items-start gap-2 p-3 bg-green-50 rounded-lg mb-4">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm">
                                <div class="font-medium text-green-900">Disponible</div>
                                <p class="text-green-700">Ce produit est disponible immédiatement pour la location.</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (isLoggedIn() && !$isOwner): ?>
                        <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            Voir le récapitulatif
                        </button>
                        <?php elseif ($isOwner): ?>
                        <button type="button" disabled class="w-full py-3 bg-gray-300 text-gray-500 rounded-lg font-medium cursor-not-allowed">
                            Vous êtes le propriétaire
                        </button>
                        <?php else: ?>
                        <a href="templates/login.php" class="block w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors text-center">
                            Connectez-vous pour louer
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="mt-8 bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Description</h2>
        <div class="text-gray-700 space-y-3 text-sm leading-relaxed">
            <p>Perceuse-visseuse sans fil professionnelle Bosch, idéale pour tous vos travaux de bricolage et d'aménagement. Cet outil polyvalent combine puissance et précision pour percer et visser dans différents matériaux.</p>
            <p>Parfaite pour monter des meubles, fixer des étagères, ou réaliser des petits travaux de rénovation. L'équipement est fourni avec 2 batteries pour une autonomie prolongée, un chargeur rapide, et une mallette de rangement.</p>
            <p>Matériel très bien entretenu, utilisé uniquement pour des projets personnels. Nettoyage complet après chaque utilisation.</p>
        </div>
    </div>

    <!-- Location sécurisée -->
    <div class="mt-6 bg-blue-50 rounded-xl p-6">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-lg mb-2">Location sécurisée</h3>
                <p class="text-gray-700 text-sm">Toutes les transactions sont protégées. En cas de problème, notre équipe de support est disponible pour vous accompagner. Un système de caution garantit la protection du matériel pour le propriétaire et votre tranquillité en tant que locataire.</p>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/templates/site-footer.php'; ?>

<?php if ($isOwner): ?>
<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDeleteModal()"></div>
    
    <!-- Contenu de la modal -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all scale-95 opacity-0" id="modalContent">
            <!-- Header avec icône -->
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Supprimer ce produit ?</h3>
                <p class="text-gray-600">
                    Êtes-vous sûr de vouloir supprimer <span class="font-semibold">"<?php echo htmlspecialchars($product->title); ?>"</span> ? 
                    Cette action est irréversible.
                </p>
            </div>
            
            <!-- Boutons -->
            <div class="flex border-t border-gray-200">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 py-4 text-gray-700 font-semibold hover:bg-gray-50 transition-colors rounded-bl-2xl">
                    Annuler
                </button>
                <form method="POST" action="product.php?id=<?php echo $product->id; ?>" class="flex-1 border-l border-gray-200">
                    <input type="hidden" name="delete_product" value="1">
                    <button type="submit" class="w-full py-4 text-red-600 font-semibold hover:bg-red-50 transition-colors rounded-br-2xl">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const content = document.getElementById('modalContent');
        
        modal.classList.remove('hidden');
        
        // Animation d'entrée
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const content = document.getElementById('modalContent');
        
        // Animation de sortie
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }
    
    // Fermer avec la touche Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
<?php endif; ?>

<!-- Script d'initialisation de Flatpickr -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dates réservées récupérées depuis PHP
    const reservedRanges = <?php echo json_encode($reservedRanges); ?>;
    const today = new Date().toISOString().split('T')[0];
    
    // Fonction pour vérifier si une date est dans une plage réservée
    function isDateReserved(date) {
        const dateStr = date.toISOString().split('T')[0];
        for (let range of reservedRanges) {
            if (dateStr >= range.from && dateStr <= range.to) {
                return true;
            }
        }
        return false;
    }
    
    // Configuration commune pour les deux datepickers
    const commonConfig = {
        locale: 'fr',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        minDate: 'today',
        disableMobile: true,
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            // Marquer les dates réservées en rouge
            const date = dayElem.dateObj;
            if (isDateReserved(date)) {
                dayElem.classList.add('reserved');
                dayElem.title = 'Date réservée';
            }
        }
    };
    
    // Datepicker pour la date de début
    const startPicker = flatpickr('#start_date', {
        ...commonConfig,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates.length > 0) {
                // Mettre à jour la date minimum du datepicker de fin
                endPicker.set('minDate', dateStr);
                
                // Si la date de fin est avant la date de début, la réinitialiser
                const endDate = endPicker.selectedDates[0];
                if (endDate && endDate < selectedDates[0]) {
                    endPicker.clear();
                }
            }
        }
    });
    
    // Datepicker pour la date de fin
    const endPicker = flatpickr('#end_date', {
        ...commonConfig,
        onChange: function(selectedDates, dateStr) {
            // Validation supplémentaire si nécessaire
        }
    });
});
</script>

</body>
</html>
