<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends AbstractController
{
    #[Route('/gallery', name: 'app_gallery')]
    public function index(): Response
    {
        $json = '[
            {
                "id": 1,
                "title": "Titre de la peinture",
                "height": 120,
                "width": 80,
                "description": "Description de la peinture",
                "quantity": 1,
                "createdAt": "2024-07-01T12:00:00Z",
                "method": "Oil on canvas",
                "prize":1200
            },
            {
                "id": 2,
                "title": "Titre de la peinture",
                "height": 120,
                "width": 80,
                "description": "Description de la peinture",
                "quantity": 1,
                "createdAt": "2024-07-01T12:00:00Z",
                "method": "Oil on canvas",
                "prize":1200
            }
        ]';

        // Convert JSON string to Array
        $artworks = json_decode($json, true);

        return $this->render('gallery/index.html.twig', [
            'artworks' => $artworks
        ]);
    }
}
