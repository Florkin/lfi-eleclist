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
    /** @var HttpClientInterface */
    private HttpClientInterface $httpClient;

    /** @var array */
    private array $apiDataParams;

    /** @var array  */
    private array $csvMappingParams;

    /**
     * @param HttpClientInterface $httpClient
     * @param array $apiDataParams
     * @param array $csvMappingParams
     */
    public function __construct(
        HttpClientInterface $httpClient,
        array $apiDataParams = [],
        array $csvMappingParams = []
    ) {
        $this->httpClient = $httpClient;
        $this->apiDataParams = $apiDataParams;
        $this->csvMappingParams = $csvMappingParams;
    }

    public function request($csv): string
    {
        $dataArray['data'] = DataPart::fromPath($csv);
        $dataArray = $this->addColumnsToSend($dataArray);
        $dataArray = $this->addColumnsToReceive($dataArray);

        $formData = new FormDataPart($dataArray);

        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->httpClient->request(
            'POST',
            $this->apiDataParams['api_url'],
            [
                'headers' => $headers,
                'body' => $formData->bodyToIterable()
            ]
        );

        return $response->getContent();
    }

    private function addColumnsToSend($dataArray): array
    {
        foreach ($this->apiDataParams['fields_to_send'] as $field) {
            $dataArray['columns'][] = $this->csvMappingParams['column_headers'][$field];
        }

        return $dataArray;
    }

    private function addColumnsToReceive($dataArray): array
    {
        foreach ($this->apiDataParams['fields_to_receive'] as $field) {
            $dataArray['result_columns'][] = $field;
        }

        return $dataArray;
    }
}
