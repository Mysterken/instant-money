<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    private const API_URL = 'https://api.freecurrencyapi.com';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     */
    private function fetchData(string $endpoint): array
    {
        $response = $this->client->request('GET', self::API_URL . $endpoint, [
            'query' => ['apikey' => $_ENV['CURRENCY_APIKEY']]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    #[Route('/api/currency_status', name: 'app_currency_status')]
    public function status(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/status'));
    }

    #[Route('/api/currency_list', name: 'app_currency_list')]
    public function list(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/currencies'));
    }

    #[Route('/api/currency_latest', name: 'app_currency_latest')]
    public function latest(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/latest'));
    }

    #[Route('/api/currency_historical', name: 'app_currency_historical')]
    public function historical(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/historical'));
    }
}
