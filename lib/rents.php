<?php
/**
 * Fonctions de gestion des locations (rents)
 */

require_once __DIR__ . '/database.php';

/**
 * Vérifie si un produit est disponible pour une période donnée.
 *
 * @param int $productId L'ID du produit
 * @param string $startDate Date de début (format Y-m-d)
 * @param string $endDate Date de fin (format Y-m-d)
 * @return bool True si disponible, False sinon
 */
function isProductAvailable(int $productId, string $startDate, string $endDate): bool {
    $pdo = getPDOConnection();
    
    // Vérifie s'il existe une location confirmée qui chevauche les dates demandées
    // Chevauchement : (start1 <= end2) AND (end1 >= start1)
    $sql = "SELECT COUNT(*) FROM rents 
            WHERE product_id = :product_id 
            AND status IN ('pending', 'confirmed') 
            AND start_date <= :end_date 
            AND end_date >= :start_date";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'product_id' => $productId,
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    
    return $stmt->fetchColumn() == 0;
}

/**
 * Récupère toutes les locations d'un produit.
 *
 * @param int $productId L'ID du produit
 * @return array Tableau des locations
 */
function getProductRents(int $productId): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, u.username 
        FROM rents r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = :product_id 
        ORDER BY r.start_date DESC
    ");
    $stmt->execute(['product_id' => $productId]);
    return $stmt->fetchAll();
}

/**
 * Récupère toutes les locations d'un utilisateur (en tant que locataire).
 *
 * @param int $userId L'ID de l'utilisateur
 * @return array Tableau des locations avec infos produit
 */
function getUserRents(int $userId): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, p.title, p.image, p.location as product_location, u.username as owner_name
        FROM rents r 
        JOIN products p ON r.product_id = p.id 
        JOIN users u ON p.user_id = u.id
        WHERE r.user_id = :user_id 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Récupère les locations des produits d'un propriétaire.
 *
 * @param int $ownerId L'ID du propriétaire
 * @return array Tableau des locations reçues
 */
function getOwnerRents(int $ownerId): array {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, p.title, p.image, u.username as renter_name
        FROM rents r 
        JOIN products p ON r.product_id = p.id 
        JOIN users u ON r.user_id = u.id
        WHERE p.user_id = :owner_id 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute(['owner_id' => $ownerId]);
    return $stmt->fetchAll();
}

/**
 * Calcule le prix total d'une location.
 *
 * @param float $dailyPrice Prix par jour
 * @param string $startDate Date de début (format Y-m-d)
 * @param string $endDate Date de fin (format Y-m-d)
 * @return float Prix total
 */
function calculateRentPrice(float $dailyPrice, string $startDate, string $endDate): float {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $days = $start->diff($end)->days + 1; // +1 car on compte le jour de début
    
    // Si 7 jours ou plus, appliquer 10% de réduction
    if ($days >= 7) {
        return round($dailyPrice * $days * 0.9, 2);
    }
    
    return round($dailyPrice * $days, 2);
}

/**
 * Calcule le nombre de jours de location.
 *
 * @param string $startDate Date de début (format Y-m-d)
 * @param string $endDate Date de fin (format Y-m-d)
 * @return int Nombre de jours
 */
function calculateRentDays(string $startDate, string $endDate): int {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    return $start->diff($end)->days + 1;
}

/**
 * Crée une nouvelle location.
 *
 * @param int $userId L'ID du locataire
 * @param int $productId L'ID du produit
 * @param string $startDate Date de début
 * @param string $endDate Date de fin
 * @param float $totalPrice Prix total calculé
 * @param string $status Statut initial (default: 'confirmed')
 * @return int|false L'ID de la location créée ou false en cas d'erreur
 */
function createRent(int $userId, int $productId, string $startDate, string $endDate, float $totalPrice, string $status = 'confirmed'): int|false {
    $pdo = getPDOConnection();
    
    // Vérification finale de disponibilité
    if (!isProductAvailable($productId, $startDate, $endDate)) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO rents (user_id, product_id, start_date, end_date, total_price, status) 
        VALUES (:user_id, :product_id, :start_date, :end_date, :total_price, :status)
    ");
    
    $result = $stmt->execute([
        'user_id' => $userId,
        'product_id' => $productId,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'total_price' => $totalPrice,
        'status' => $status
    ]);
    
    return $result ? (int) $pdo->lastInsertId() : false;
}

/**
 * Met à jour le statut d'une location.
 *
 * @param int $rentId L'ID de la location
 * @param string $status Le nouveau statut
 * @return bool True si succès
 */
function updateRentStatus(int $rentId, string $status): bool {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("UPDATE rents SET status = :status WHERE id = :id");
    return $stmt->execute(['id' => $rentId, 'status' => $status]);
}

/**
 * Récupère une location par son ID.
 *
 * @param int $rentId L'ID de la location
 * @return array|false Les données de la location ou false
 */
function getRentById(int $rentId): array|false {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, p.title, p.image, p.price as daily_price, p.location as product_location,
               owner.username as owner_name, renter.username as renter_name
        FROM rents r 
        JOIN products p ON r.product_id = p.id 
        JOIN users owner ON p.user_id = owner.id
        JOIN users renter ON r.user_id = renter.id
        WHERE r.id = :id
    ");
    $stmt->execute(['id' => $rentId]);
    return $stmt->fetch();
}

/**
 * Vérifie si un produit est actuellement loué.
 *
 * @param int $productId L'ID du produit
 * @return bool True si actuellement loué
 */
function isProductCurrentlyRented(int $productId): bool {
    $today = date('Y-m-d');
    return !isProductAvailable($productId, $today, $today);
}

/**
 * Récupère toutes les plages de dates réservées pour un produit.
 * Utilisé pour désactiver les dates dans le calendrier.
 *
 * @param int $productId L'ID du produit
 * @return array Tableau des plages de dates [['from' => 'Y-m-d', 'to' => 'Y-m-d'], ...]
 */
function getReservedDateRanges(int $productId): array {
    $pdo = getPDOConnection();
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT start_date, end_date FROM rents 
        WHERE product_id = :product_id 
        AND status IN ('pending', 'confirmed') 
        AND end_date >= :today
        ORDER BY start_date ASC
    ");
    $stmt->execute(['product_id' => $productId, 'today' => $today]);
    $results = $stmt->fetchAll();
    
    $ranges = [];
    foreach ($results as $row) {
        $ranges[] = [
            'from' => $row['start_date'],
            'to' => $row['end_date']
        ];
    }
    
    return $ranges;
}

/**
 * Récupère la prochaine disponibilité d'un produit.
 *
 * @param int $productId L'ID du produit
 * @return string|null Date de prochaine disponibilité ou null si disponible
 */
function getNextAvailability(int $productId): ?string {
    $pdo = getPDOConnection();
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT end_date FROM rents 
        WHERE product_id = :product_id 
        AND status IN ('pending', 'confirmed') 
        AND end_date >= :today
        ORDER BY end_date DESC
        LIMIT 1
    ");
    $stmt->execute(['product_id' => $productId, 'today' => $today]);
    $result = $stmt->fetch();
    
    if ($result) {
        // Retourne le jour après la fin de la dernière location
        $endDate = new DateTime($result['end_date']);
        $endDate->modify('+1 day');
        return $endDate->format('Y-m-d');
    }
    
    return null; // Disponible maintenant
}
