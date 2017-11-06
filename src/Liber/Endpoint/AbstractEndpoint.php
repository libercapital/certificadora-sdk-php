<?php

declare(strict_types = 1);

namespace Liber\Endpoint;

use GuzzleHttp\Client;
use Liber\Exception\SDKError;
use Liber\Exception\SDKException;

abstract class AbstractEndpoint implements EndpointInterface {
    /**
     * API Key.
     *
     * @var string
     */
    protected $apiKey;
    /**
     * GuzzleHttp\Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;
    /**
     * API base URL.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Sends the request to the api.
     *
     * @param string $method
     * @param array  $query
     * @param mixed  $body
     * @param array  $headers
     *
     * @return mixed response
     */
    private function sendRequest(
        string $method,
        string $uri,
        array $query = [],
        $body = null,
        array $headers = []
    ) {
        $uri = sprintf('%s%s', $this->baseUrl, ltrim($uri, '/'));

        $options = [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->apiKey)
            ],
            'http_errors' => false
        ];

        if (! empty($query)) {
            $options['query'] = $query;
        }

        if (! empty($body)) {
            $target = 'body';
            if ((empty($headers['Content-Type'])) && (is_array($body))) {
                $target = 'json';
            }

            $options[$target] = $body;
        }

        if (! empty($headers)) {
            $options['headers'] = array_merge($options['headers'], $headers);
        }

        $response = $this->client->request(
            $method,
            $uri,
            $options
        );

        if ($response->hasHeader('content-type')) {
            $header = $response->getHeader('content-type');
            if (is_array($header)) {
                $header = $header[0];
            }

            if (preg_match('/^application\/json/', $header)) {
                $json = json_decode((string) $response->getBody(), true);

                if ($json === null) {
                    throw new SDKError();
                }

                if (empty($json['status'])) {
                    if (! empty($json['reason'])) {
                        throw new SDKException($json['reason']);
                    }

                    if (! empty($json['message'])) {
                        throw new SDKException($json['message']);
                    }

                    if (empty($json['exception'])) {
                        throw new SDKException('Unknown SDK exception');
                    }

                    throw new SDKException($json['exception']);
                }

                return $json;
            }
        }

        return (string) $response->getBody();
    }

    /**
     * Sends GET request.
     *
     * @param string $uri
     * @param array  $query
     * @param array  $headers
     *
     * @return mixed response
     */
    protected function sendGet(
        string $uri,
        array $query = [],
        array $headers = []
    ) {
        return $this->sendRequest(
            'GET',
            $uri,
            $query,
            [],
            $headers
        );
    }

    /**
     * Sends a POST request.
     *
     * @param string $uri
     * @param array  $query
     * @param mixed  $body
     * @param array  $headers
     *
     * @return mixed response
     */
    protected function sendPost(
        string $uri,
        array $query = [],
        $body = null,
        array $headers = []
    ) {
        return $this->sendRequest(
            'POST',
            $uri,
            $query,
            $body,
            $headers
        );
    }

    /**
     * Sends a PATCH request.
     *
     * @param string $uri
     * @param array  $query
     * @param mixed  $body
     * @param array  $headers
     *
     * @return mixed response
     */
    protected function sendPatch(
        string $uri,
        array $query = [],
        $body = null,
        array $headers = []
    ) {
        return $this->sendRequest(
            'PATCH',
            $uri,
            $query,
            $body,
            $headers
        );
    }

    /**
     * Sends a PUT request.
     *
     * @param string $uri
     * @param array  $query
     * @param mixed  $body
     * @param array  $headers
     *
     * @return mixed response
     */
    protected function sendPut(
        string $uri,
        array $query = [],
        $body = null,
        array $headers = []
    ) {
        return $this->sendRequest(
            'PUT',
            $uri,
            $query,
            $body,
            $headers
        );
    }

    /**
     * Sends a DELETE request.
     *
     * @param string $uri
     * @param array  $query
     * @param array  $headers
     *
     * @return mixed response
     */
    protected function sendDelete(
        string $uri,
        array $query = [],
        array $headers = []
    ) {
        return $this->sendRequest(
            'DELETE',
            $uri,
            $query,
            [],
            $headers
        );
    }

    /**
     * Constructor Class.
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
        string $baseUrl
    ) {
        $this->apiKey  = $apiKey;
        $this->client  = $client;
        $this->baseUrl = $baseUrl;
    }
}
