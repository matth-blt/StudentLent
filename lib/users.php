<?php 

class User {
    public string $id;
    public string $username;
    public string $email;
    private string $password;

    public function __construct(string $id = '', string $username = '', string $email = '', string $password = '') {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    public function verifyPassword(string $passwordInput): bool {
        return password_verify($passwordInput, $this->password);
    }

    public function getPassword(): string {
        return $this->password;
    }
}

// $users = [
//     new User(
//         '123',
//         'BOB',
//         'bob.razowski@gmail.com',
//         password_hash('bob', PASSWORD_DEFAULT)
//     ),
//     new User(
//         '456', 
//         'SULLIVAN', 
//         'jacques.sullivan@gmail.com', 
//         password_hash('sulli', PASSWORD_DEFAULT)
//     )
// ];

function getUser(): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->query('SELECT * FROM users ORDER BY id DESC');
    $usersData = $stmt->fetchAll();
    $users = [];
    foreach ($usersData as $u) {
        $users[] = new User(
            $u['id'],
            $u['username'],
            $u['email'],
            $u['password']
        );
    }
    return $users;
}