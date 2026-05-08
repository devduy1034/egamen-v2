<?php



namespace LARAVEL\LARAVELGateway\Momo\Message\AllInOne;
use Symfony\Component\HttpFoundation\ParameterBag;
use LARAVEL\LARAVELGateway\MoMo\Message\AbstractIncomingRequest as BaseAbstractIncomingRequest;
abstract class AbstractIncomingRequest extends BaseAbstractIncomingRequest
{
    public function sendData($data): IncomingResponse
    {
        return $this->response = new IncomingResponse($this, $data);
    }
    protected function getIncomingParameters(): array
    {
        $data = [];
        $params = [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'orderInfo', 'orderType', 'transId',
            'message', 'localMessage', 'responseTime', 'errorCode', 'extraData', 'signature', 'payType',
        ];
        $bag = $this->getIncomingParametersBag();

        foreach ($params as $param) {
            $data[$param] = $bag->get($param);
        }

        return $data;
    }
    abstract protected function getIncomingParametersBag(): ParameterBag;
}