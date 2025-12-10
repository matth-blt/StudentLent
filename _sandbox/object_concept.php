<?php
// ========== CLASS + FONCTION ==========
class Product {
    public $name;
    public $price;
    public $image;

    public function __construct($name = 'Inconnu', $price = 0, $image = 'image.jpg') {
        $this->name = $name;
        $this->price = $price;
        $this->image = $image;
    }

    public function getFormattedPrice() {
        return $this->price . ' €';
    }
};

function space() {
    echo '<br>';
}

// ========== CODE ==========

$product1 = new Product('Super T-shirt', 25, 'tshirt.jpg');
var_dump($product1);

space();

echo 'Le prix est de : ' . $product1->getFormattedPrice();

space();

$product2 = new Product('Mug de compétition', 15, 'mug.jpg');
var_dump($product2);

space();

echo 'Le prix est de : ' . $product2->getFormattedPrice();
