<?php

namespace App\Controller;

use DateMalformedStringException;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class CryptoController extends AbstractController
{
    private const API_URL = 'https://api.coingecko.com/api';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    #[Route('/api/crypto_ping', name: 'app_crypto_ping')]
    public function ping(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v3/ping'));
    }

    /**
     * @throws GuzzleException
     */
    private function fetchData(string $endpoint, array $queryParameters = []): array
    {
        $queryParameters['apikey'] = $_ENV['CRYPTO_APIKEY'];
        $response = $this->client->request('GET', self::API_URL . $endpoint, [
            'query' => $queryParameters
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    #[Route('/api/crypto_list', name: 'app_crypto_list')]
    public function list(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v3/coins/list'));
    }

    /**
     * @throws DateMalformedStringException
     * @throws GuzzleException
     */
    #[Route('/api/crypto_historical', name: 'app_crypto_historical')]
    public function historical(
        #[MapQueryParameter] string $id,
        #[MapQueryParameter] string $date = '',
    ): JsonResponse
    {
        if (empty($id)) {
            return new JsonResponse([
                'error' => 'No id provided'
            ], 400);
        }

        $dateObject = new DateTime($date ?: 'today');

        return new JsonResponse($this->fetchData("/v3/coins/$id/history", [
            'date' => $dateObject->format('d-m-Y')
        ]));
    }
}
