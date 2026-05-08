<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;
use Symfony\Component\HttpFoundation\ParameterBag;
class NotificationRequest extends AbstractIncomingRequest
{
    protected function getIncomingParametersBag(): ParameterBag
    {
        return $this->httpRequest->request;
    }
}