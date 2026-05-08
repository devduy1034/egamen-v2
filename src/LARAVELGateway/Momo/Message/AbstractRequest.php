<?php



namespace LARAVEL\LARAVELGateway\Momo\Message;
use LARAVEL\LARAVELGateway\MoMo\Support\Arr;
use LARAVEL\LARAVELGateway\MoMo\Concerns\Parameters;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
abstract class AbstractRequest extends BaseAbstractRequest
{
    use Parameters;
    public function validate(...$parameters): void
    {
        $listParameters = $this->getParameters();

        foreach ($parameters as $parameter) {
            if (null === Arr::getValue($parameter, $listParameters)) {
                throw new InvalidRequestException(sprintf('The `%s` parameter is required', $parameter));
            }
        }
    }
}