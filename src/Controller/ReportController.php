<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Report;
use App\Form\ReportType;
use App\Services\Report\DTO\ReportDTO;
use App\Services\Report\ReportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReportController extends AbstractController
{
    //public function __construct(private HttpClientInterface $client) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/reportUser/{post_id}', name: 'report_User', methods: ['GET', 'POST'])]
    public function reportUser(EntityManagerInterface $entityManager, Request $request, int $post_id, ReportService $reportService): Response
    {
        $userSession = $this->getUser();
        $post = $entityManager->getRepository(Post::class)->find($post_id);

        $report = new Report();
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reason = $form->get('reason')->getData();

            $cleanReason = \ConsoleTVs\Profanity\Builder::blocker($reason)->filter();

            $reportDTO = new ReportDTO($userSession, $post, $cleanReason);

            $reportService->create($reportDTO);

            return $this->redirectToRoute('homePage');
        }

        return $this->render('report/report.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/allReport', name: 'all_report', methods: ['GET'])]
    public function allUser(EntityManagerInterface $entityManager): Response
    {
        $reports = $entityManager->getRepository(Report::class)->findAll();

        return $this->render('report/allReport.html.twig', [
            'reports' => $reports,
        ]);
    }
}
