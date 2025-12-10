<?php 
session_start(); 

require_once(__DIR__ . "/../lib/users.php");
require_once(__DIR__ . "/../lib/auth_functions.php");

$error = '';
$success = '';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    header('Location: ../home.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username_input = trim($_POST['username'] ?? '');
    $email_input = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation des champs
    if (empty($username_input) || empty($email_input) || empty($password_input) || empty($password_confirm)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (strlen($username_input) < 3) {
        $error = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
    } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } elseif (strlen($password_input) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password_input !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Tentative d'inscription
        $newUserId = registerUser($username_input, $email_input, $password_input);
        
        if ($newUserId !== false) {
            // Inscription réussie - connecter l'utilisateur automatiquement
            $user = findUserByEmail($email_input);
            if ($user) {
                loginUser($user);
                header('Location: ../home.php');
                exit;
            }
        } else {
            $error = "Cette adresse email est déjà utilisée.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - StudentLend</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo et Titre -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-600 mb-2">StudentLend</h1>
            <p class="text-gray-600">Créez votre compte</p>
        </div>

        <!-- Formulaire d'inscription -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Affichage conditionnel de l'erreur -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="signup.php">
                <!-- Champ Nom d'utilisateur -->
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 font-semibold mb-2">
                        Nom d'utilisateur
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        placeholder="Ex: alice123"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <!-- Champ Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        placeholder="exemple@student.com"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <!-- Champ Mot de passe -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">
                        Mot de passe
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimum 6 caractères"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <!-- Champ Confirmation mot de passe -->
                <div class="mb-6">
                    <label for="password_confirm" class="block text-gray-700 font-semibold mb-2">
                        Confirmer le mot de passe
                    </label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        placeholder="Retapez votre mot de passe"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <!-- Bouton Submit -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition duration-200"
                >
                    S'inscrire
                </button>
            </form>

            <!-- Lien vers connexion -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Déjà un compte ? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>

        <!-- Lien de retour -->
        <div class="text-center mt-6">
            <a href="../home.php" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
