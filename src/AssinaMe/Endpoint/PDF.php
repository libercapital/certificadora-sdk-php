<?php

declare(strict_types = 1);

namespace AssinaMe\Endpoint;

use AssinaMe\Exception\SDKException;
use AssinaMe\Utils;

/**
 * PDF Endpoint class.
 */
class PDF extends AbstractEndpoint {
    /**
     * PDF signature visual representation setup.
     *
     * @param array $setup
     *
     * @throws \AssinaMe\Exception\SDKError
     * @throws \AssinaMe\Exception\SDKException
     *
     * @return string The document token ($documentToken) to be used on upload/sign methods
     */
    public function setup(array $setup) : string {
        $response = $this->sendPost(
            '/pdf',
            [],
            $setup
        );

        if (! is_array($response)) {
            throw new SDKException('Invalid API response format');
        }

        if (empty($response['document_token'])) {
            throw new SDKException('API response does not contain "document_token"');
        }

        return $response['document_token'];
    }

    /**
     * PDF file upload.
     *
     * @param string $documentToken
     * @param string $originalFilePath
     *
     * @throws \AssinaMe\Exception\SDKError
     * @throws \AssinaMe\Exception\SDKException
     *
     * @return void
     */
    public function upload(string $documentToken, string $originalFilePath) {
        if (! is_readable($originalFilePath)) {
            throw new SDKException(
                sprintf(
                    'Cannot read "%s", check if file exists and is readable',
                    $originalFilePath
                )
            );
        }

        $response = $this->sendPut(
            sprintf('/pdf/%s/file', $documentToken),
            [],
            file_get_contents($originalFilePath),
            [
                'Content-Type' => 'application/pdf'
            ]
        );

        if (! is_array($response)) {
            throw new SDKException('Invalid API response format');
        }

        if (! $response['status']) {
            throw new SDKException('Invalid API response');
        }
    }

    /**
     * Creates a PDF file to be signed.
     *
     * @param array  $setup
     * @param string $originalFilePath
     *
     * @throws \AssinaMe\Exception\SDKError
     * @throws \AssinaMe\Exception\SDKException
     *
     * @return string The document token to be used by the JavaScript SDK
     */
    public function create(array $setup, string $originalFilePath) : string {
        $documentToken = $this->setup($setup);
        $this->upload($documentToken, $originalFilePath);

        return $documentToken;
    }

    /**
     * Checks a PDF status (created, signed etc).
     *
     * @param string $documentToken
     *
     * @throws \AssinaMe\Exception\SDKError
     * @throws \AssinaMe\Exception\SDKException
     *
     * @return string The PDF status
     */
    public function status(string $documentToken) : string {
        $response = $this->sendGet(
            sprintf('/pdf/%s', $documentToken)
        );

        if (! is_array($response)) {
            throw new SDKException('Invalid API response format');
        }

        return $response;
    }

    /**
     * Downloads a signed PDF file.
     *
     * @param string $documentToken
     * @param string $signedFilePath
     *
     * @throws \AssinaMe\Exception\SDKError
     * @throws \AssinaMe\Exception\SDKException
     *
     * @return void
     */
    public function download(string $documentToken, string $signedFilePath) {
        $response = $this->sendGet(
            sprintf('/pdf/%s/file', $documentToken),
            [],
            [
                'Accept' => 'application/pdf'
            ]
        );

        if (empty($response)) {
            throw new SDKException('Invalid API response');
        }

        file_put_contents($signedFilePath, $response);
    }
}
