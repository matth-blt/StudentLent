<?php 
session_start(); 

require_once(__DIR__ . "/../lib/users.php");
require_once(__DIR__ . "/../lib/auth_functions.php");

$error = '';

if (isLoggedIn()) {
    header('Location: ../home.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email_input = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (empty($email_input) || empty($password_input)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Appel avec UN seul argument maintenant
        $user = findUserByEmail($email_input);
        
        if ($user && $user->verifyPassword($password_input)) {
            loginUser($user);
            header('Location: ../home.php');
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - StudentLend</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo et Titre -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-600 mb-2">StudentLend</h1>
            <p class="text-gray-600">Connectez-vous à votre compte</p>
        </div>

        <!-- Formulaire de connexion -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Affichage conditionnel de l'erreur -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <!-- Champ Username -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        Email
                    </label>
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <!-- Champ Password -->
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">
                        Mot de passe
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <!-- Bouton Submit -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition duration-200"
                >
                    Se connecter
                </button>
            </form>

            <!-- Lien vers inscription -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Pas encore de compte ? 
                    <a href="signup.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        S'inscrire
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