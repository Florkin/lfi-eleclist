<?php
/**
 * ApiRequestService.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AddressRequestHandler
{
    /** @var HttpClientInterface  */
    private HttpClientInterface $httpClient;

    /** @var array  */
    private array $params;

    /**
     * @param HttpClientInterface $httpClient
     * @param array $params
     */
    public function __construct(HttpClientInterface $httpClient, array $params)
    {
        $this->httpClient = $httpClient;
        $this->params = $params;
    }

    public function request($csv)
    {
        $formData = new FormDataPart([
            'columns' => 'libellé de voie',
            'data' => DataPart::fromPath($csv)
        ]);

        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->httpClient->request(
            'POST',
            $this->params['api_url'],
            [
                'headers' => $headers,
                'body' => $formData->bodyToIterable()
            ]
        );

        dd($response->getContent());
    }
}
