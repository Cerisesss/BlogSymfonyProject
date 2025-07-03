<?php

namespace App\Tests\Controller;

use App\Controller\ReportController;
use App\Entity\Post;
use App\Entity\Report;
use App\Entity\User;
use App\Form\ReportType;
use App\Services\Report\DTO\ReportDTO;
use App\Services\Report\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportControllerTest extends WebTestCase
{
    public function testReportUserGet()
    {
        $user = $this->createMock(User::class);
        $post = $this->createMock(Post::class);

        $postRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $postRepo->expects($this->once())
            ->method('find')
            ->willReturn($post);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Post::class)
            ->willReturn($postRepo);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('handleRequest');
        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(false);

        $controller = $this->getMockBuilder(ReportController::class)
            ->onlyMethods(['getUser', 'createForm', 'render'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);
        $controller->method('createForm')->willReturn($form);

        $controller->expects($this->once())
            ->method('render')
            ->with(
                'report/report.html.twig',
                $this->arrayHasKey('form')
            )
            ->willReturn(new Response());

        $request = new Request();

        $response = $controller->reportUser($entityManager, $request, 1, $this->createMock(ReportService::class));
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testReportUserPostValid()
    {
        $user = $this->createMock(User::class);
        $post = $this->createMock(Post::class);

        $postRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $postRepo->expects($this->once())
            ->method('find')
            ->willReturn($post);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Post::class)
            ->willReturn($postRepo);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('handleRequest');
        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        // Mock child form element for "reason"
        $reasonForm = $this->createMock(FormInterface::class);
        $reasonForm->expects($this->once())
            ->method('getData')
            ->willReturn('bad reason');

        $form->expects($this->once())
            ->method('get')
            ->with('reason')
            ->willReturn($reasonForm);

        // Mock profanity filter if needed
        if (!class_exists('\ConsoleTVs\Profanity\Builder')) {
            eval('namespace ConsoleTVs\Profanity; class Builder { public static function blocker($r) { return new class { public function filter() { return "clean reason"; } }; } }');
        }

        $reportService = $this->createMock(ReportService::class);
        $reportService->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(ReportDTO::class));

        $controller = $this->getMockBuilder(ReportController::class)
            ->onlyMethods(['getUser', 'createForm', 'redirectToRoute'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);
        $controller->method('createForm')->willReturn($form);

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('homePage')
            ->willReturn(new RedirectResponse('/'));

        $request = new Request();

        $response = $controller->reportUser($entityManager, $request, 1, $reportService);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testAllUser()
    {
        $reports = [new Report(), new Report()];
        $reportRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findAll'])
            ->getMock();
        $reportRepo->expects($this->once())
            ->method('findAll')
            ->willReturn($reports);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Report::class)
            ->willReturn($reportRepo);

        $controller = $this->getMockBuilder(ReportController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with(
                'report/allReport.html.twig',
                $this->callback(function ($context) use ($reports) {
                    return isset($context['reports']) && $context['reports'] === $reports;
                })
            )
            ->willReturn(new Response());

        $response = $controller->allUser($entityManager);
        $this->assertInstanceOf(Response::class, $response);
    }
}
