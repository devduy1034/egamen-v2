<?php



namespace LARAVEL\Cart;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use LARAVEL\Cart\Contracts\Buyable;
use LARAVEL\Cart\Contracts\InstanceIdentifier;
use LARAVEL\Core\Session\Session;

class Cart
{
    use Macroable;
    const DEFAULT_INSTANCE = 'default';
    private Session $session;
    private mixed $instance;
    private float $discount = 0;
    private $taxRate = 0;
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->taxRate = config('cart.tax');
        $this->instance(self::DEFAULT_INSTANCE);
    }
    public function instance($instance = null): static
    {
        $instance = $instance ?: self::DEFAULT_INSTANCE;
        if ($instance instanceof InstanceIdentifier) {
            $this->discount = $instance->getInstanceGlobalDiscount();
            $instance = $instance->getInstanceIdentifier();
        }
        $this->instance = 'cart_'.$instance;
        return $this;
    }
    public function currentInstance(): array|string{
        return str_replace('cart_', '', $this->instance);
    }

    /**
     * @throws \Exception
     */
    public function add($id, $name = null, $qty = null, $price = null, $weight = 0, array $options = [])
    {
        if ($this->isMulti($id)) {
            return array_map(function ($item) {
                return $this->add($item);
            }, $id);
        }
        $cartItem = $this->createCartItem($id, $name, $qty, $price, $weight, $options);

        return $this->addCartItem($cartItem);
    }
    public function addCartItem($item, $keepDiscount = false, $keepTax = false)
    {
        if (!$keepDiscount) {
            $item->setDiscountRate($this->discount);
        }
        if (!$keepTax) {
            $item->setTaxRate($this->taxRate);
        }
        $content = $this->getContent();
        if ($content->has($item->rowId)) {
            $item->qty += $content->get($item->rowId)->qty;
        }
        $content->put($item->rowId, $item);
        $this->session->put($this->instance, $content);

        return $item;
    }
    /**
     * Update the cart item with the given rowId.
     *
     * @param string $rowId
     * @param mixed  $qty
     *
     * @return CartItem
     */
    public function update($rowId, $qty): CartItem
    {
        $cartItem = $this->get($rowId);
        if ($qty instanceof Buyable) {
            $cartItem->updateFromBuyable($qty);
        } elseif (is_array($qty)) {
            $cartItem->updateFromArray($qty);
        } else {
            $cartItem->qty = $qty;
        }
        $content = $this->getContent();
        if ($rowId !== $cartItem->rowId) {
            $itemOldIndex = $content->keys()->search($rowId);
            $content->pull($rowId);
            if ($content->has($cartItem->rowId)) {
                $existingCartItem = $this->get($cartItem->rowId);
                $cartItem->setQuantity($existingCartItem->qty + $cartItem->qty);
            }
        }
        if ($cartItem->qty <= 0) {
            $this->remove($cartItem->rowId);
            return '';
        } else {
            if (isset($itemOldIndex)) {
                $content = $content->slice(0, $itemOldIndex)
                    ->merge([$cartItem->rowId => $cartItem])
                    ->merge($content->slice($itemOldIndex));
            } else {
                $content->put($cartItem->rowId, $cartItem);
            }
        }
        $this->session->put($this->instance, $content);
        return $cartItem;
    }
    public function remove($rowId): void
    {
        $cartItem = $this->get($rowId);
        $content = $this->getContent();
        $content->pull($cartItem->rowId);
        $this->session->put($this->instance, $content);
    }

    /**
     * Get a cart item from the cart by its rowId.
     *
     * @param string $rowId
     *
     * @return CartItem
     * @throws \Exception
     */
    public function get($rowId): CartItem
    {
        $content = $this->getContent();
        if (!$content->has($rowId)) {
            throw new \Exception("The cart does not contain rowId {$rowId}.");
        }
        return $content->get($rowId);
    }
    public function destroy(): void
    {
        $this->session->unset($this->instance);
    }
    public function content()
    {
        if (is_null($this->session->get($this->instance))) {
            return new Collection([]);
        }
        return $this->session->get($this->instance)[$this->instance];
    }
    public function count()
    {
        return $this->getContent()->sum('qty');
    }
    public function countItems()
    {
        return $this->getContent()->count();
    }
    public function totalFloat()
    {
        return $this->getContent()->reduce(function ($total, CartItem $cartItem) {
            return $total + $cartItem->total;
        }, 0);
    }
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->totalFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function taxFloat()
    {
        return $this->getContent()->reduce(function ($tax, CartItem $cartItem) {
            return $tax + $cartItem->taxTotal;
        }, 0);
    }
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->taxFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function subtotalFloat()
    {
        return $this->getContent()->reduce(function ($subTotal, CartItem $cartItem) {
            return $subTotal + $cartItem->subtotal;
        }, 0);
    }
    public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->subtotalFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function discountFloat()
    {
        return $this->getContent()->reduce(function ($discount, CartItem $cartItem) {
            return $discount + $cartItem->discountTotal;
        }, 0);
    }
    public function discount($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->discountFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function initialFloat()
    {
        return $this->getContent()->reduce(function ($initial, CartItem $cartItem) {
            return $initial + ($cartItem->qty * $cartItem->price);
        }, 0);
    }
    public function initial($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->initialFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function priceTotalFloat()
    {
        return $this->getContent()->reduce(function ($initial, CartItem $cartItem) {
            return $initial + $cartItem->priceTotal;
        }, 0);
    }
    protected function getContent()
    {
        if ($this->session->has($this->instance)) {
            $content = $this->session->get($this->instance);
            return $content[$this->instance];
        }
        return new Collection();
    }
    public function priceTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->priceTotalFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function weightFloat()
    {
        return $this->getContent()->reduce(function ($total, CartItem $cartItem) {
            return $total + ($cartItem->qty * $cartItem->weight);
        }, 0);
    }
    public function weight($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->weightFloat(), $decimals, $decimalPoint, $thousandSeperator);
    }
    public function search(Closure $search)
    {
        return $this->getContent()->filter($search);
    }
    public function associate($rowId, $model): void
    {
        if (is_string($model) && !class_exists($model)) {
            throw new \Exception("The supplied model {$model} does not exist.");
        }
        $cartItem = $this->get($rowId);
        $cartItem->associate($model);
        $content = $this->getContent();
        $content->put($cartItem->rowId, $cartItem);
        $this->session->put($this->instance, $content);
    }
    public function setTax($rowId, $taxRate): void
    {
        $cartItem = $this->get($rowId);
        $cartItem->setTaxRate($taxRate);
        $content = $this->getContent();
        $content->put($cartItem->rowId, $cartItem);
        $this->session->put($this->instance, $content);
    }
    public function setGlobalTax($taxRate): void
    {
        $this->taxRate = $taxRate;
        $content = $this->getContent();
        if ($content && $content->count()) {
            $content->each(function ($item, $key) {
                $item->setTaxRate($this->taxRate);
            });
        }
    }
    public function setDiscount($rowId, $discount): void
    {
        $cartItem = $this->get($rowId);
        $cartItem->setDiscountRate($discount);
        $content = $this->getContent();
        $content->put($cartItem->rowId, $cartItem);
        $this->session->put($this->instance, $content);
    }
    public function setGlobalDiscount($discount): void
    {
        $this->discount = $discount;
        $content = $this->getContent();
        if ($content && $content->count()) {
            $content->each(function ($item, $key) {
                $item->setDiscountRate($this->discount);
            });
        }
    }
    public function __get($attribute)
    {
        return match ($attribute) {
            'total' => $this->total(),
            'tax' => $this->tax(),
            'subtotal' => $this->subtotal(),
            default => null,
        };
    }
    /**
     * @throws \Exception
     */
    private function createCartItem($id, $name, $qty, $price, $weight, array $options): CartItem
    {
        if ($id instanceof Buyable) {
            $cartItem = CartItem::fromBuyable($id, $qty ?: []);
            $cartItem->setQuantity($name ?: 1);
            $cartItem->associate($id);
        } elseif (is_array($id)) {
            $cartItem = CartItem::fromArray($id);
            $cartItem->setQuantity($id['qty']);
        } else {
            $cartItem = CartItem::fromAttributes($id, $name, $price, $weight, $options);
            $cartItem->setQuantity($qty);
        }
        $cartItem->setInstance($this->currentInstance());
        return $cartItem;
    }
    private function isMulti($item): bool
    {
        if (!is_array($item)) {
            return false;
        }
        return is_array(head($item)) || head($item) instanceof Buyable;
    }
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator): string
    {
        if (is_null($decimals)) {
            $decimals = config('cart.format.decimals', 2);
        }
        if (is_null($decimalPoint)) {
            $decimalPoint = config('cart.format.decimal_point', '.');
        }
        if (is_null($thousandSeperator)) {
            $thousandSeperator = config('cart.format.thousand_separator', ',');
        }
        return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
    }
}