<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\HistoricalExchangeRate;
use App\Repository\CurrencyRepository;
use App\Repository\HistoricalExchangeRateRepository;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CurrencyController extends AbstractController
{
    private const API_URL = 'https://api.freecurrencyapi.com';
    private Client $client;

    public function __construct(
        private readonly CurrencyRepository               $currencyRepository,
        private readonly HistoricalExchangeRateRepository $historicalExchangeRateRepository,
        private readonly EntityManagerInterface           $entityManager,
        private readonly SerializerInterface              $serializer
    )
    {
        $this->client = new Client();
    }

    #[Route('/api/currency_status', name: 'app_currency_status')]
    public function status(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/status'));
    }

    /**
     * @throws GuzzleException
     */
    private function fetchData(string $endpoint, array $queryParameters = []): array
    {
        $queryParameters['apikey'] = $_ENV['CURRENCY_APIKEY'];
        $response = $this->client->request('GET', self::API_URL . $endpoint, [
            'query' => $queryParameters
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    #[Route('/api/currency_list', name: 'app_currency_list')]
    public function list(#[MapQueryParameter] string $currencies = ''): JsonResponse
    {
    

        return new JsonResponse($this->fetchData('/v1/currencies', ['currencies' => $currencies]));
    }

    #[Route('/api/currency_latest', name: 'app_currency_latest')]
    public function latest(
        #[MapQueryParameter] string $base_currency = 'USD',
        #[MapQueryParameter] string $currencies = '',
    ): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/latest', [
            'base_currency' => $base_currency,
            'currencies' => $currencies
        ]));
    }

    /**
     * @throws DateMalformedStringException
     * @throws GuzzleException
     */
    #[Route('/api/currency_historical', name: 'app_currency_historical')]
    public function historical(
        #[MapQueryParameter] string $date = '',
        #[MapQueryParameter] string $base_currency = 'USD',
        #[MapQueryParameter] string $currencies = '',
    ): JsonResponse
    {
        $dateObject = new DateTime($date ?: 'yesterday');
        $dateToday = new DateTime();

        if ($dateObject > $dateToday || $dateObject->format('Y-m-d') === $dateToday->format('Y-m-d')) {
            return new JsonResponse(['error' => 'Invalid date'], 400);
        }

        // Fetch the data from the API and save it to the database


        return new JsonResponse($this->fetchData('/v1/historical', [
            'date' => $dateObject->format('Y-m-d'),
            'base_currency' => $base_currency,
            'currencies' => $currencies
        ]));
    }
}
