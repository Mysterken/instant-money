<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\HistoricalExchangeRate;
use App\Repository\CurrencyRepository;
use App\Repository\HistoricalExchangeRateRepository;
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
        private readonly CurrencyRepository $currencyRepository,
        private readonly HistoricalExchangeRateRepository $historicalExchangeRateRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer
    )
    {
        $this->client = new Client();
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

    #[Route('/api/currency_status', name: 'app_currency_status')]
    public function status(): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/status'));
    }

    #[Route('/api/currency_list', name: 'app_currency_list')]
    public function list(#[MapQueryParameter] string $currencies = ''): JsonResponse
    {
        $DBcurrencies = empty($currencies)
            ? $this->currencyRepository->findAll()
            : $this->currencyRepository->findBy(['code' => explode(',', $currencies)]);

        if (!empty($DBcurrencies)) {
            // add the code of the currency as the key of the array and 'data' as the key above everything
            $DBcurrencies = array_reduce($DBcurrencies, function ($acc, $currency) {
                $acc[$currency->getCode()] = $currency;
                return $acc;
            }, []);
            return new JsonResponse($this->serializer->normalize(['data' => $DBcurrencies], 'json'));
        }

        $response = $this->fetchData('/v1/currencies', ['currencies' => $currencies]);
        $currencies = array_map(function ($currencyData) {
            $currency = (new Currency())
                ->setCode($currencyData['code'])
                ->setName($currencyData['name'])
                ->setSymbol($currencyData['symbol'])
                ->setSymbolNative($currencyData['symbol_native'])
                ->setDecimalDigits($currencyData['decimal_digits'])
                ->setRounding($currencyData['rounding'])
                ->setNamePlural($currencyData['name_plural']);
            $this->entityManager->persist($currency);
            return $currency;
        }, $response['data']);

        $this->entityManager->flush();

        return new JsonResponse($this->serializer->normalize(['data' => $currencies], 'json'));
    }

    #[Route('/api/currency_latest', name: 'app_currency_latest')]
    public function latest(
        #[MapQueryParameter] string $base_currency = '',
        #[MapQueryParameter] string $currencies = '',
    ): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/latest', [
            'base_currency' => $base_currency,
            'currencies' => $currencies
        ]));
    }

    #[Route('/api/currency_historical', name: 'app_currency_historical')]
    public function historical(
        #[MapQueryParameter] string $date = '',
        #[MapQueryParameter] string $base_currency = '',
        #[MapQueryParameter] string $currencies = '',
    ): JsonResponse
    {
        return new JsonResponse($this->fetchData('/v1/historical', [
            'date' => $date,
            'base_currency' => $base_currency,
            'currencies' => $currencies
        ]));
    }
}
