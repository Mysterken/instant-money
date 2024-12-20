<?php

namespace App\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/api/test', name: 'app_test')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TestController.php',
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/api/test/routes', name: 'app_test_routes')]
    public function routes(): JsonResponse
    {
        $routes = $this->container->get('router')->getRouteCollection();

        $data = [];
        foreach ($routes as $route) {
            $data[] = [
                'path' => $route->getPath(),
                'name' => $route->getDefault('_controller'),
                'methods' => $route->getMethods(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/test/mercure', name: 'app_test_mercure')]
    public function mercure(HubInterface $hub): Response
    {
        $update = new Update(
            'mercure',
            json_encode(['status' => 'mercure is amazing!'])
        );

        $hub->publish($update);

        return new Response('published!');
    }
}
