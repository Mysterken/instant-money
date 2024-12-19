<?php

namespace App\Controller;

use App\Entity\Coin;
use App\Entity\HistoricalCoinData;
use App\Repository\CoinRepository;
use App\Repository\HistoricalCoinDataRepository;
use DateMalformedStringException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CryptoController extends AbstractController
{
    private const API_URL = 'https://api.coingecko.com/api';
    private Client $client;

    public function __construct(
        private readonly EntityManagerInterface       $entityManager,
        private readonly HistoricalCoinDataRepository $historicalCoinDataRepository,
        private readonly CoinRepository               $coinRepository,
        private readonly SerializerInterface          $serializer
    )
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
        $DBcoins = $this->coinRepository->findAll() ?: $this->fetchAndPersistCoins();

        $DBcoins = array_map(fn($coin) => [
            'id' => $coin->getId(),
            'symbol' => $coin->getSymbol(),
            'name' => $coin->getName()
        ], $DBcoins);

        return new JsonResponse($this->serializer->normalize($DBcoins, 'json'));
    }

    private function fetchAndPersistCoins(): array
    {
        ini_set('memory_limit', '256M');
        $coins = $this->fetchData('/v3/coins/list');
        $DBcoins = [];

        foreach ($coins as $coin) {
            $coinEntity = (new Coin())
                ->setId($coin['id'])
                ->setSymbol($coin['symbol'])
                ->setName($coin['name']);

            $this->entityManager->persist($coinEntity);
            $DBcoins[] = $coinEntity;
        }

        $this->entityManager->flush();
        return $DBcoins;
    }

    /**
     * @throws DateMalformedStringException
     * @throws GuzzleException
     */
    #[Route('/api/crypto_historical', name: 'app_crypto_historical')]
    public function historical(
        #[MapQueryParameter] string $id,
        #[MapQueryParameter] string $date = '',
        #[MapQueryParameter] string $base_currency = 'USD,EUR,JPY'
    ): JsonResponse
    {
        if (empty($id)) {
            return new JsonResponse([
                'error' => 'No id provided'
            ], 400);
        }

        $dateObject = new DateTime($date ?: 'today');
        $base_currency = strtolower($base_currency);
        $base_currency = explode(',', $base_currency);

        // first check if the coin is in the database
        $coin = $this->coinRepository->findOneBy(['id' => $id]);
        if (!$coin) {
            return new JsonResponse([
                'error' => "Coin with id $id not found"
            ], 400);
        }

        $historicalCoinDatas = [];
        foreach ($base_currency as $bc) {
            $historicalCoinData = $this->historicalCoinDataRepository->findOneBy([
                'base_currency' => $bc,
                'currency' => $coin,
                'date' => $dateObject
            ]);

            if ($historicalCoinData) {
                $historicalCoinDatas[] = [
                    'base_currency' => $historicalCoinData->getBaseCurrency(),
                    'currency' => $historicalCoinData->getCurrency()->getId(),
                    'value' => $historicalCoinData->getValue(),
                    'date' => $historicalCoinData->getDate()->format('Y-m-d')
                ];
            } else {
                return new JsonResponse([
                    'error' => "Historical data not found for base currency $bc"
                ], 404);
            }
        }

        if ($historicalCoinDatas) {
            return new JsonResponse($this->serializer->normalize($historicalCoinDatas, 'json'));
        }

        // if the historical data is not in the database, fetch it from the API
        $response = $this->fetchData("/v3/coins/$id/history", [
            'date' => $dateObject->format('d-m-Y'),
            'localization' => 'false'
        ]);

        $historicalCoinDatas = [];
        foreach ($response['market_data']['current_price'] as $base_currency => $value) {
            $historicalCoinData = (new HistoricalCoinData())
                ->setBaseCurrency($base_currency)
                ->setCurrency($coin)
                ->setValue($value)
                ->setDate($dateObject);

            $this->entityManager->persist($historicalCoinData);
            $historicalCoinDatas[] = $historicalCoinData;
        }

        $this->entityManager->flush();

        $historicalCoinDatas = array_map(fn($historicalCoinData) => [
            'base_currency' => $historicalCoinData->getBaseCurrency(),
            'currency' => $historicalCoinData->getCurrency()->getId(),
            'value' => $historicalCoinData->getValue(),
            'date' => $historicalCoinData->getDate()->format('Y-m-d')
        ], $historicalCoinDatas);

        return new JsonResponse($this->serializer->normalize($historicalCoinDatas, 'json'));
    }
}
