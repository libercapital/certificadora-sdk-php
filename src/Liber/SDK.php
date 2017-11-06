<?php

declare(strict_types = 1);

namespace Liber;

use GuzzleHttp\Client;
use Liber\Endpoint\EndpointInterface;

class SDK {
    /**
     * API Key.
     *
     * @var string
     */
    private $apiKey;
    /**
     * Guzzle Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * API base URL.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Factory method, creates a SDK instance.
     *
     * @param string $apiKey
     *
     * @return self
     */
    public static function create(string $apiKey) {
        return new static(
            $apiKey,
            new Client()
        );
    }

    /**
     * Class constructor.
     *
     * @param string             $apiKey
     * @param \GuzzleHttp\Client $client
     * @param string             $baseUrl
     *
     * @return void
     */
    public function __construct(
        string $apiKey,
        Client $client,
        string $baseUrl = 'https://certificadora.libercapital.com.br/api/v1'
    ) {
        $this->apiKey = $apiKey;
        $this
            ->setClient($client)
            ->setBaseUrl($baseUrl);
    }

    /**
     * Sets the GuzzleHttp\Client instance.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return self
     */
    public function setClient(Client $client) : self {
        $this->client = $client;
        return $this;
    }

    /**
     * Returns the GuzzleHttp\Client instance.
     *
     * @return \GuzzeHttp\Client client
     */
    public function getClient() : Client {
        return $this->client;
    }

    /**
     * Sets the API base URL.
     *
     * @param string $baseUrl
     *
     * @return self
     */
    public function setBaseUrl(string $baseUrl) : self {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';

        return $this;
    }

    /**
     * Returns the API base URL.
     *
     * @return string $baseUrl
     */
    public function getBaseUrl() : string {
        return $this->baseUrl;
    }

    /**
     * Gets an instance of an endpoint class.
     *
     * @param string $name
     *
     * @return \Liber\Endpoint\EndpointInterface
     */
    public function __get(string $name) : EndpointInterface {
        $className = $this->getEndpointClassName($name);

        return new $className(
            $this->apiKey,
            $this->client,
            $this->baseUrl
        );
    }

    /**
     * Returns the name of the endpoint class.
     *
     * @param string $name
     *
     * @return string className
     */
    protected function getEndpointClassName(string $name) : string {
        $className = sprintf(
            '%s\\%s\\%s',
            'Liber',
            'Endpoint',
            ucfirst($name)
        );

        if (! class_exists($className)) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid endpoint name "%s" (%s)',
                    $name,
                    $className
                )
            );
        }

        return $className;
    }
}
