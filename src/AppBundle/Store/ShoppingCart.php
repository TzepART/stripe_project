<?php

namespace AppBundle\Store;

use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ShoppingCart
 * @package AppBundle\Store
 */
class ShoppingCart
{
    const CART_PRODUCTS_KEY = '_shopping_cart.products';

    /**
     * @var Session
     */
    private $session;
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var
     */
    private $products;

    /**
     * ShoppingCart constructor.
     * @param Session $session
     * @param EntityManager $em
     */
    public function __construct(Session $session, EntityManager $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @param Product $product
     */
    public function addProduct(Product $product)
    {
        $products = $this->getProducts();

        if (!in_array($product, $products)) {
            $products[] = $product;
        }

        $this->updateProducts($products);
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        if ($this->products === null) {
            $productRepo = $this->em->getRepository('AppBundle:Product');
            $ids = $this->session->get(self::CART_PRODUCTS_KEY, []);
            $products = [];
            foreach ($ids as $id) {
                $product = $productRepo->find($id);

                // in case a product becomes deleted
                if ($product) {
                    $products[] = $product;
                }
            }

            $this->products = $products;
        }

        return $this->products;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->getProducts() as $product) {
            $total += $product->getPrice();
        }

        return $total;
    }

    /**
     *
     */
    public function emptyCart()
    {
        $this->updateProducts([]);
    }

    /**
     * @param Product[] $products
     */
    private function updateProducts(array $products)
    {
        $this->products = $products;

        $ids = array_map(function(Product $item) {
            return $item->getId();
        }, $products);

        $this->session->set(self::CART_PRODUCTS_KEY, $ids);
    }
}