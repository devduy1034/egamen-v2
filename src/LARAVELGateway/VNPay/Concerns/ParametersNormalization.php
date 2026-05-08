<?php



namespace LARAVEL\LARAVELGateway\VNPay\Concerns;

trait ParametersNormalization
{
    protected function normalizeParameters(array $parameters): array
    {
        $normalizedParameters = [];

        foreach ($parameters as $parameter => $value) {
            $parameter = str_replace('_', '', $parameter);
            $normalizedParameters[$parameter] = $value;
        }

        return $normalizedParameters;
    }
}