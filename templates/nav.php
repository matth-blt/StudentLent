<?php require_once(__DIR__ . '/../lib/auth_functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<header class="py-4 border-b border-gray-200 bg-white">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-4">
        <div class="flex items-center gap-2 font-bold">
            <a href="<?php echo dirname($_SERVER['PHP_SELF']) === '/templates' ? '../home.php' : './home.php'; ?>" class="flex items-center gap-2 font-bold hover:opacity-80 transition-opacity">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>StudentLend</span>
            </a>
        </div>
        <nav class="flex gap-8 text-sm items-center">
            <?php $prefix = dirname($_SERVER['PHP_SELF']) === '/templates' ? '../' : './'; ?>
            <a class="hover:underline" href="<?php echo $prefix; ?>catalogue.php">Catalogue</a>
            <a class="hover:underline" href="#process">Comment ça marche</a>
            <a class="hover:underline" href="#about">À propos</a>
            
            <?php if (isLoggedIn()): ?>
                <!-- Navigation pour utilisateur CONNECTÉ -->
                <a class="hover:underline" href="<?php echo $prefix; ?>account.php">Mon compte</a>
                <span class="text-gray-700">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700" href="<?php echo $prefix; ?>templates/logout.php">Déconnexion</a>
            <?php else: ?>
                <!-- Navigation pour utilisateur NON CONNECTÉ -->
                <a class="hover:underline" href="<?php echo $prefix; ?>templates/login.php">Connexion</a>
                <a class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700" href="<?php echo $prefix; ?>templates/signup.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

