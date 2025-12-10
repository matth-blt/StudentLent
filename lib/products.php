<?php require_once __DIR__ . '/database.php';
class Product {
    public string $id;
    public string $title;
    public string $category;
    public float $price;
    public string $location;
    public string $image;
    public int $user_id;
    public string $username;

    public function __construct(string $id = '', string $title = '', string $category = '', float $price = 0.0, string $location = '', string $image = '', int $user_id = 0, string $username = '') {
        $this->id = $id;
        $this->title = $title;
        $this->category = $category;
        $this->price = $price;
        $this->location = $location;
        $this->image = $image;
        $this->user_id = $user_id;
        $this->username = $username;
    }

    public function getId(): string {
        return $this->id;
    }
    public function getTitle(): string {
        return $this->title;
    }
    public function getCategory(): string {
        return $this->category;
    }  
    public function getPrice(): float {
        return $this->price;
    } 
    public function getLocation(): string {
        return $this->location;
    }   
    public function getImage(): string {
        return $this->image;
    }
    public function getUserId(): int {
        return $this->user_id;
    }
    public function getUsername(): string {
        return $this->username;
    }
}

function getProducts(): array {
    $pdo = getPDOConnection();
    // JOIN pour récupérer le nom du propriétaire
    $stmt = $pdo->query('SELECT p.*, u.username FROM products p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.id DESC');
    $productsData = $stmt->fetchAll();
    // Transformer les tableaux en objets Product
    $products = [];
    foreach ($productsData as $p) {
        $products[] = new Product(
            $p['id'],
            $p['title'],
            $p['category'],
            $p['price'],
            $p['location'],
            $p['image'],
            $p['user_id'],
            $p['username'] ?? 'Inconnu'
        );
    }
    return $products;
}

/**
* @param int $id L'ID du produit à trouver
* @return Product|null
*/
function findProductById(int $id): ?Product {
    $pdo = getPDOConnection();
    // JOIN pour récupérer le nom du propriétaire
    $stmt = $pdo->prepare('SELECT p.*, u.username FROM products p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = :id');
    $stmt->execute(['id' => $id]);
    $productData = $stmt->fetch();
    // Si aucun résultat, fetch() retourne false
    if ($productData === false) {
        return null;
    }
    return new Product(
        $productData['id'],
        $productData['title'],
        $productData['category'],
        $productData['price'],
        $productData['location'],
        $productData['image'],
        $productData['user_id'],
        $productData['username'] ?? 'Inconnu'
    );
}

/**
* Crée un nouveau produit dans la base de données.
*
* @param string $title
* @param string $category
* @param float $price
* @param string $location
* @param string|null $image
* @param int $user_id L'ID de l'utilisateur propriétaire
* @return int L'ID du nouveau produit créé
*/
function createProduct(string $title, string $category, float $price, string $location, ?string $image, int $user_id): int {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('INSERT INTO products (title, category, price, location, image, user_id) VALUES (:title, :category, :price, :location, :image, :user_id)');
    $stmt->execute([
        'title' => $title,
        'category' => $category,
        'price' => $price,
        'location' => $location,
        'image' => $image,
        'user_id' => $user_id
    ]);
    return (int) $pdo->lastInsertId();
}

/**
* Supprime un produit de la base de données.
* Vérifie que l'utilisateur est bien le propriétaire du produit.
*
* @param int $id L'ID du produit à supprimer
* @param int $user_id L'ID de l'utilisateur qui demande la suppression
* @return bool True si la suppression a réussi, False sinon
*/
function deleteProduct(int $id, int $user_id): bool {
    $pdo = getPDOConnection();
    // Vérifie que le produit appartient bien à l'utilisateur
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    return $stmt->rowCount() > 0;
}

/**
* Récupère tous les produits d'un utilisateur donné.
*
* @param int $userId L'ID de l'utilisateur
* @return array<Product> Tableau d'objets Product
*/
function getProductsByUserId(int $userId): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('SELECT p.*, u.username FROM products p LEFT JOIN users u ON p.user_id = u.id WHERE p.user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    $productsData = $stmt->fetchAll();
    
    $products = [];
    foreach ($productsData as $productData) {
        $products[] = new Product(
            $productData['id'],
            $productData['title'],
            $productData['category'],
            $productData['price'],
            $productData['location'],
            $productData['image'],
            $productData['user_id'],
            $productData['username'] ?? 'Inconnu'
        );
    }
    return $products;
}

/**
* Calcule le prix pour une semaine de location (7 jours avec 10% de réduction).
*
* @param int $productId L'ID du produit
* @return float|null Le prix semaine ou null si le produit n'existe pas
*/
function getWeeklyPrice(int $productId): ?float {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('SELECT price FROM products WHERE id = :id');
    $stmt->execute(['id' => $productId]);
    $result = $stmt->fetch();
    
    if ($result === false) {
        return null;
    }
    
    // Prix semaine = prix jour × 7 jours × 0.9 (10% de réduction)
    $dailyPrice = (float) $result['price'];
    return round($dailyPrice * 7 * 0.9, 2);
}

/**
* Récupère toutes les catégories disponibles.
*
* @return array<string> Tableau des noms de catégories
*/
function getCategories(): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->query('SELECT name FROM categories ORDER BY name');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
* Ajoute une nouvelle catégorie.
*
* @param string $name Le nom de la catégorie
* @return int|false L'ID de la catégorie créée ou false si elle existe déjà
*/
function addCategory(string $name): int|false {
    $pdo = getPDOConnection();
    
    // Vérifier si la catégorie existe déjà
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name');
    $stmt->execute(['name' => $name]);
    if ($stmt->fetch()) {
        return false; // La catégorie existe déjà
    }
    
    $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
    $stmt->execute(['name' => $name]);
    return (int) $pdo->lastInsertId();
}

/**
* Récupère les produits filtrés par catégorie.
*
* @param string $category Le nom de la catégorie
* @return array<Product> Tableau d'objets Product
*/
function getProductsByCategory(string $category): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('SELECT p.*, u.username FROM products p LEFT JOIN users u ON p.user_id = u.id WHERE p.category = :category');
    $stmt->execute(['category' => $category]);
    $productsData = $stmt->fetchAll();
    
    $products = [];
    foreach ($productsData as $productData) {
        $products[] = new Product(
            $productData['id'],
            $productData['title'],
            $productData['category'],
            $productData['price'],
            $productData['location'],
            $productData['image'],
            $productData['user_id'],
            $productData['username'] ?? 'Inconnu'
        );
    }
    return $products;
}
