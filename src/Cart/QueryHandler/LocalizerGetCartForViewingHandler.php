<?php

declare(strict_types=1);

namespace PrestaYar\Localizer\Cart\QueryHandler;

use Cart;
use Customer;
use Order;
use PrestaShop\PrestaShop\Core\Domain\Cart\Query\GetCartForViewing;
use PrestaShop\PrestaShop\Core\Domain\Cart\QueryHandler\GetCartForViewingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Cart\QueryResult\CartView;

class LocalizerGetCartForViewingHandler implements GetCartForViewingHandlerInterface
{
    private $innerHandler;

    public function __construct(GetCartForViewingHandlerInterface $innerHandler)
    {
        $this->innerHandler = $innerHandler;
    }

    public function handle(GetCartForViewing $query)
    {
        $cartView = $this->innerHandler->handle($query);
        $cart = new Cart($cartView->getCartId());

        $customerInformation = $cartView->getCustomerInformation();
        $customer = new Customer($cart->id_customer);
        $customerInformation['registration_date'] = \Tools::displayDate($customer->date_add);

        $orderInformation = $cartView->getOrderInformation();
        $order = new Order((int) Order::getIdByCartId($cart->id));
        if (\Validate::isLoadedObject($order)) {
            $orderInformation['placed_date'] = \Tools::displayDate($order->date_add, true);
        }

        $cartSummary = $cartView->getCartSummary();
        $cartSummary['date_add'] = \Tools::displayDate($cart->date_add, true);
        $cartSummary['date_upd'] = \Tools::displayDate($cart->date_upd, true);

        return new CartView(
            $cartView->getCartId(),
            $cartView->getCartCurrencyId(),
            $customerInformation,
            $orderInformation,
            $cartSummary
        );
    }
}
