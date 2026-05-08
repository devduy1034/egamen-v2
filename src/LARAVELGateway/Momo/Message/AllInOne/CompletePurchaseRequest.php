<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;
use Symfony\Component\HttpFoundation\ParameterBag;
class CompletePurchaseRequest extends AbstractIncomingRequest
{
    protected function getIncomingParametersBag(): ParameterBag
    {
        return $this->httpRequest->query;
    }
}