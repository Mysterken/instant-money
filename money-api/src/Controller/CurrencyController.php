<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CurrencyController extends AbstractController
{
    const API_URL = 'https://api.freecurrencyapi.com';

    #[Route('/api/currency_status', name: 'app_currency_status')]
    public function status(): JsonResponse
    {
        $client = new Client();

        $res = $client->request('GET', self::API_URL . '/v1/status', [
            'query' => ['apikey' => $_ENV['CURRENCY_APIKEY']]
        ]);

        return new JsonResponse(json_decode($res->getBody()->getContents(), true));
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/api/currency_list', name: 'app_currency_list')]
    public function list(): JsonResponse
    {
        $client = new Client();

        $res = $client->request('GET', self::API_URL . '/v1/currencies', [
            'query' => ['apikey' => $_ENV['CURRENCY_APIKEY']]
        ]);

        return new JsonResponse(json_decode($res->getBody()->getContents(), true));
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/api/currency_latest', name: 'app_currency_latest')]
    public function latest(): JsonResponse
    {
        $client = new Client();

        $res = $client->request('GET', self::API_URL . '/v1/latest', [
            'query' => ['apikey' => $_ENV['CURRENCY_APIKEY']]
        ]);

        return new JsonResponse(json_decode($res->getBody()->getContents(), true));
    }

    #[Route('/api/currency_historical', name: 'app_currency_historical')]
    public function historical(): JsonResponse
    {
        $client = new Client();

        $res = $client->request('GET', self::API_URL . '/v1/historical', [
            'query' => ['apikey' => $_ENV['CURRENCY_APIKEY']]
        ]);

        return new JsonResponse(json_decode($res->getBody()->getContents(), true));
    }
}
