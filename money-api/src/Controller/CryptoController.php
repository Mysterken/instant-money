<?php

namespace App\Controller;

use App\Entity\Coin;
use App\Repository\CoinRepository;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly CoinRepository         $coinRepository,
        private readonly SerializerInterface    $serializer
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
        ini_set('memory_limit', '256M');

        $DBcoins = $this->coinRepository->findAll();

        if (empty($DBcoins)) {
            foreach ($this->fetchData('/v3/coins/list') as $coin) {
                $coinEntity = new Coin();
                $coinEntity->setId($coin['id']);
                $coinEntity->setSymbol($coin['symbol']);
                $coinEntity->setName($coin['name']);

                $this->entityManager->persist($coinEntity);
                $DBcoins[] = $coinEntity;
            }

            $this->entityManager->flush();
        }

        return new JsonResponse($this->serializer->normalize($DBcoins, 'json'));
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