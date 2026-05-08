<?php



namespace LARAVEL\Cart;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use LARAVEL\Cart\Calculation\DefaultCalculator;
use LARAVEL\Cart\Contracts\Buyable;
use LARAVEL\Cart\Contracts\Calculator;
use ReflectionClass;

/**
 * @property-read mixed discount
 * @property-read float discountTotal
 * @property-read float priceTarget
 * @property-read float priceNet
 * @property-read float priceTotal
 * @property-read float subtotal
 * @property-read float taxTotal
 * @property-read float tax
 * @property-read float total
 * @property-read float priceTax
 */
class CartItem implements Arrayable, Jsonable
{

    public $rowId;
    public $id;
    public $instance = null;
    /**
     * @var float|mixed|null
     */
    public $option;
    private $discountRate = 0;
    private $associatedModel = null;
    public $taxRate = 0;
    public $options;
    public $weight;
    public $price;
    public $name;
    public $qty;

    /**
     * @throws Exception
     */
    public function __construct($id, $name, $price, $weight = 0, array $options = [])
    {
        if (empty($id)) {
            throw new Exception('Please supply a valid identifier.');
        }
        if (empty($name)) {
            throw new Exception('Please supply a valid name.');
        }
        if (!is_numeric($price)) {
            throw new Exception('Please supply a valid price.');
        }
        if (!is_numeric($weight)) {
            throw new Exception('Please supply a valid weight.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = floatval($price);
        $this->weight = floatval($weight);
        $this->options = new CartItemOptions($options);
        $this->rowId = $this->generateRowId($id, $options);
    }
    public function weight($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->weight, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function price($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function priceTarget($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->priceTarget, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function priceTax($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->priceTax, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->subtotal, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->total, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->tax, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function taxTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->taxTotal, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function discount($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->discount, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function discountTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->discountTotal, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function priceTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
    {
        return $this->numberFormat($this->priceTotal, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function setQuantity($qty): void
    {
        if (empty($qty) || !is_numeric($qty)) {
            throw new \InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }
    public function updateFromBuyable(Buyable $item): void
    {
        $this->id = $item->getBuyableIdentifier($this->options);
        $this->name = $item->getBuyableDescription($this->options);
        $this->price = $item->getBuyablePrice($this->options);
    }
    public function updateFromArray(array $attributes): void
    {
        $this->id = Arr::get($attributes, 'id', $this->id);
        $this->qty = Arr::get($attributes, 'qty', $this->qty);
        $this->name = Arr::get($attributes, 'name', $this->name);
        $this->price = Arr::get($attributes, 'price', $this->price);
        $this->weight = Arr::get($attributes, 'weight', $this->weight);
        $this->options = new CartItemOptions(Arr::get($attributes, 'options', $this->options));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }
    public function associate($model): static
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
    }
    public function setTaxRate($taxRate): static
    {
        $this->taxRate = $taxRate;
        return $this;
    }
    public function setDiscountRate($discountRate): static
    {
        $this->discountRate = $discountRate;
        return $this;
    }
    public function setInstance($instance): static
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        $decimals = config('cart.format.decimals', 2);
        switch ($attribute) {
            case 'model':
                if (isset($this->associatedModel)) {
                    return with(new $this->associatedModel())->find($this->id);
                }
                break;
            case 'modelFQCN':
                if (isset($this->associatedModel)) {
                    return $this->associatedModel;
                }
                break;
            case 'weightTotal':
                return round($this->weight * $this->qty, $decimals);
        }

        $class = new ReflectionClass(config('cart.calculator', DefaultCalculator::class));
        if (!$class->implementsInterface(Calculator::class)) {
            throw new Exception('The configured Calculator seems to be invalid. Calculators have to implement the Calculator Contract.');
        }
        return call_user_func($class->getName().'::getAttribute', $attribute, $this);
    }

    /**
     * @throws Exception
     */
    public static function fromBuyable(Buyable $item, array $options = []): CartItem
    {
        return new self($item->getBuyableIdentifier($options), $item->getBuyableDescription($options), $item->getBuyablePrice($options), $item->getBuyableWeight($options), $options);
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $attributes): CartItem
    {
        $options = Arr::get($attributes, 'options', []);
        return new self($attributes['id'], $attributes['name'], $attributes['price'], $attributes['weight'], $options);
    }

    /**
     * @throws Exception
     */
    public static function fromAttributes($id, $name, $price, $weight, array $options = []): CartItem
    {
        return new self($id, $name, $price, $weight, $options);
    }
    protected function generateRowId($id, array $options): string
    {
        ksort($options);

        return md5($id.serialize($options));
    }
    public function toArray(): array
    {
        return [
            'rowId'    => $this->rowId,
            'id'       => $this->id,
            'name'     => $this->name,
            'qty'      => $this->qty,
            'price'    => $this->price,
            'weight'   => $this->weight,
            'options'  => is_object($this->options)
                ? $this->options->toArray()
                : $this->options,
            'discount' => $this->discount,
            'tax'      => $this->tax,
            'subtotal' => $this->subtotal,
        ];
    }
    public function toJson($options = 0): bool|string
    {
        return json_encode($this->toArray(), $options);
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
    public function getDiscountRate(): int
    {
        return $this->discountRate;
    }
}