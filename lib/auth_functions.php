<?php require_once(__DIR__ . "/users.php");
require_once(__DIR__ . '/database.php');

// function findUserByUsername($username, $users) {
//     foreach ($users as $u) {
//         if ($u->username === $username)
//             return $u;
//         else
//             echo 'erreur username non trouvé';
//     }
//     return null;
// }

// function findUserByEmail($email, $users) {
//     foreach ($users as $u) {
//         if ($u->email === $email)
//             return $u;
//     }
//     return null;
// }

function findUserByEmail(string $email): ?User {
    $pdo = getPDOConnection();
    // IMPORTANT : Requête préparée pour éviter les injections SQL
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $usersData = $stmt->fetch();
    // Si aucun résultat, fetch() retourne false
    if ($usersData === false) {
        return null;
    }
    return new User(
        $usersData['id'],
        $usersData['username'],
        $usersData['email'],
        $usersData['passwordHash']
    );
}

function isLoggedIn() : bool {
    if (isset($_SESSION['user_id'])) return true;
    else return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ./templates/login.php');
        exit;
    }
}

function loginUser($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user->id;
    $_SESSION['username'] = $user->username;
    $_SESSION['email'] = $user->email;
}

function logoutUser() {
    $_SESSION = [];
    session_destroy();
}

function registerUser(string $username, string $email, string $password): int|false {
    $existingUser = findUserByEmail($email);
    if ($existingUser !== null) {
        return false; // L'email existe déjà
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('INSERT INTO users (username, email, passwordHash) VALUES (:username, :email, :passwordHash)');
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'passwordHash' => $passwordHash
    ]);
    
    return (int) $pdo->lastInsertId();
}
