<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReportController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Route('/admin/allReport', name: 'all_report', methods: ['GET'])]
    public function allUser(EntityManagerInterface $entityManager, Request $request): Response
    {
        return $this->redirectToRoute('homepage');
    }
}
