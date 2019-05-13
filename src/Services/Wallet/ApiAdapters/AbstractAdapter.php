<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use App\Exception\WrongApiResponseException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponsne;

abstract class AbstractAdapter implements AdapterInterface
{
    const SLUG = null;

    protected const ENDPOINT = null;

    /** @var ClientInterface  */
    protected $httpClient;

    /** @var string  */
    protected $baseUrl;

    /** @var string  */
    protected $fetchBalanceMethod;

    public function __construct(ClientInterface $client, string $baseUrl)
    {
        $this->httpClient = $client;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param Wallet $wallet
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchBalance(Wallet $wallet): float
    {
        // prepare data
        [$search, $replace] = $this->getEndpointOptionsByWallet($wallet);
        $url = $this->getAbsoluteUrl($search, $replace);
        $options = static::getEndpointOptions();

        //api call
        $response = $this->apiCall($url, static::getRequestMethod(), $options);
        $responseData = $this->parseResponse($response);

        $balance = $this->mappingResponseBalance($responseData);

        return (float) $balance;
    }

    /**
     * should override for endpoints that provide differet method. eg: POST, HEAD ...
     *
     * @return string
     */
    protected function getRequestMethod(): string
    {
        return Request::METHOD_GET;
    }

    /**
     * define extra options for api call
     *
     * @param Wallet $wallet
     *
     * @return array
     */
    abstract protected function getEndpointOptionsByWallet(Wallet $wallet): array;

    /**
     * Define rules for fetching balance from response
     * @param $responseData
     *
     * @return string|array
     */
    abstract protected function mappingResponseBalance($responseData);

    /**
     * @param Response $response
     *
     * @return array|string
     */
    protected function parseResponse(Response $response)
    {
        if ($response->getStatusCode() !== HttpResponsne::HTTP_OK) {
            throw new WrongApiResponseException();
        }

        $head = current($response->getHeader('Content-Type'));
        $bodyContent = $response->getBody()->getContents();
        if (preg_match('/^application\/json.*/', $head)) {
            $bodyContent = \GuzzleHttp\json_decode($bodyContent, true);
        }

        return $bodyContent;
    }

    protected function getEndpointOptions(): array
    {
        return [];
    }

    /**
     * @param string $url
     * @param array  $search
     * @param array  $replace
     *
     * @return string
     */
    protected function getAbsoluteUrl(array $search = [], array $replace = [])
    {
        $url = static::ENDPOINT;
        if (strpos($url, '/') !== 0 && substr($this->baseUrl, -1) !== '/') {
            $url = '/'.$url;
        }

        if (!(empty($search) && empty($replace))) {
            $url = str_replace($search, $replace, $url);
        }

        return $this->baseUrl.$url;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function apiCall(string $url, string $method = Request::METHOD_GET, array $options): ResponseInterface
    {
        return $this->httpClient->request($method, $url, $options);
    }
}
