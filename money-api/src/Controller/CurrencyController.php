<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Repository\CurrencyRepository;
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
    public function list(
        #[MapQueryParameter] string $currencies = 'EUR',

    ): JsonResponse
    {

        if (empty($currencies)) {
            $DBcurrencies = $this->currencyRepository->findAll();
        } else {
            $currencies = explode(',', $currencies);
            $DBcurrencies = $this->currencyRepository->findBy(['code' => $currencies]);
        }

        if (!empty($DBcurrencies)) {
            $DBcurrencies = $this->serializer->normalize($DBcurrencies, 'json');
            return new JsonResponse($DBcurrencies);
        }

        // if the currencies are not in the database, we fetch them from the API
        $response = $this->fetchData('/v1/currencies', [
            'currencies' => $currencies
        ]);

        $currencies = [];
        foreach ($response['data'] as $currencyData) {
            $currency = new Currency();
            $currency->setCode($currencyData['code']);
            $currency->setName($currencyData['name']);
            $currency->setSymbol($currencyData['symbol']);
            $currency->setSymbolNative($currencyData['symbol_native']);
            $currency->setDecimalDigits($currencyData['decimal_digits']);
            $currency->setRounding($currencyData['rounding']);
            $currency->setNamePlural($currencyData['name_plural']);

            $this->entityManager->persist($currency);
            $currencies[] = $currency;
        }

        $this->entityManager->flush();

        $currencies = $this->serializer->normalize($currencies, 'json');
        return new JsonResponse($currencies);
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
