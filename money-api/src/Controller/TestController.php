<?php

namespace App\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
