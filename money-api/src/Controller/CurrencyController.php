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

        if (!$this->currencyRepository->findAll()) {
            return new JsonResponse(['error' => 'No currencies found in the database'], 400);
        }

        foreach (explode(',', $currencies) as $currency) {
            if ($currency && !$this->currencyRepository->findOneBy(['code' => $currency])) {
                return new JsonResponse(['error' => "Currency not found: $currency"], 400);
            }
        }

        // If no currencies are provided, get all the currencies from the database
        $currencies = $currencies ?: implode(',', array_map(fn($currency) => $currency->getCode(), $this->currencyRepository->findAll()));

        // Check if the historical exchange rates are already in the database by date and base currency
        $historicalExchangeRates = $this->historicalExchangeRateRepository->findBy([
            'date' => $dateObject,
            'base_currency' => $this->currencyRepository->findOneBy(['code' => $base_currency]),
        ]);

        // Return the data from the database if it exists
        if ($historicalExchangeRates) {
            $data = array_reduce($historicalExchangeRates, function ($acc, $rate) {
                $acc[$rate->getDate()->format('Y-m-d')][$rate->getCurrency()->getCode()] = $rate->getValue();
                return $acc;
            }, []);

            return new JsonResponse($this->serializer->normalize(['data' => $data], 'json'));
        }

        // Fetch the data from the API and save it to the database
        $response = $this->fetchData('/v1/historical', [
            'date' => $dateObject->format('Y-m-d'),
            'base_currency' => $base_currency,
            'currencies' => $currencies
        ]);

        foreach ($response['data'][$dateObject->format('Y-m-d')] as $currency => $value) {
            $historicalExchangeRate = (new HistoricalExchangeRate())
                ->setDate($dateObject)
                ->setBaseCurrency($this->currencyRepository->findOneBy(['code' => $base_currency]))
                ->setCurrency($this->currencyRepository->findOneBy(['code' => $currency]))
                ->setValue($value);
            $this->entityManager->persist($historicalExchangeRate);
        }

        $this->entityManager->flush();

        return new JsonResponse($this->serializer->normalize($response, 'json'));
    }
}
