<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ParsingController extends AbstractController
{
    /**
     * @Route("/parsing", name="parsing")
     */
    public function index(): JsonResponse
    {


        return $this->json(['working' => 'I am working good!']);
    }
}
