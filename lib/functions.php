<?php
function calculatePrice(float $pricePerDay, int $durationDays, float $serviceFee): array {
    $gross = $pricePerDay * $durationDays;
    $subtotal = $gross;
    $discountMessage = 'Aucune promotion applicable.';

    if ($durationDays >= 7) {
        $discountAmount = $gross * 0.10;
        $subtotal -= $discountAmount;
        $discountMessage = 'Promotion de 10% (' . number_format($discountAmount, 2) . ' €) appliquée !';
    }

    $total = $subtotal + $serviceFee;

    return [
        'gross' => $gross,
        'discountMessage' => $discountMessage,
        'total' => $total,
    ];
}

function displayProductCard(Product $product): string {
    global $id_produit_choisi;
    $checked = ($id_produit_choisi === $product->id) ? 'checked' : '';

    $html  = '<label for="product-' . htmlspecialchars($product->id) . '">';
    $html .= '<input type="radio" name="product_id" id="product-' . htmlspecialchars($product->id) . '" value="' . htmlspecialchars($product->id) . '" ' . $checked . ' required>';
    $html .= '<div class="product-card">';
    $html .= '<div class="name">' . htmlspecialchars($product->name) . '</div>';
    $html .= '<div class="price">' . number_format($product->price, 2) . ' €/jour</div>';
    $html .= '</div>';
    $html .= '</label>';

    return $html;
}
