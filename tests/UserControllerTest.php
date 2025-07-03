<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Likes;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends TestCase
{
    public function testGetPostsWithLikes()
    {
        $postMock = $this->createMock(Post::class);
        $postMock->method('getId')->willReturn(10);

        $postRepositoryMock = $this->createMock(ObjectRepository::class);
        $postRepositoryMock->method('findAll')->willReturn([$postMock]);

        $likesRepositoryMock = $this->getMockBuilder(ObjectRepository::class)
            ->addMethods(['totalLikesPerPost'])
            ->getMock();
        $likesRepositoryMock->method('totalLikesPerPost')->with(10)->willReturn(4);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        // configure getRepository() selon la classe passée en argument
        $entityManagerMock->method('getRepository')->willReturnCallback(function ($entityClass) use ($postRepositoryMock, $likesRepositoryMock) {
            if ($entityClass === Post::class) {
                return $postRepositoryMock;
            }
            if ($entityClass === Likes::class) {
                return $likesRepositoryMock;
            }
            return null;
        });

        $controller = new class extends UserController {
            public function render(string $view, array $parameters = [], Response $response = null): Response
            {
                return new Response('', 200);
            }
        };

        $response = $controller->success($entityManagerMock);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateUserFormPostValid()
    {
        $user = new User();
        $user->setPassword('plainPassword');

        $formView = new FormView();

        $form = $this->createMock(FormInterface::class);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($user);
        $form->method('createView')->willReturn($formView);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed_pw');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($user);
        $em->expects($this->once())->method('flush');

        $request = new Request([], [
            'user_create_form' => [
                'email' => 'test@example.com',
                'password' => [
                    'first' => 'plainPassword',
                    'second' => 'plainPassword',
                ],
            ]
        ]);

        $controller = new class($form) extends UserController {
            private $formMock;
            public function __construct($formMock)
            {
                $this->formMock = $formMock;
            }

            public function createForm(string $type, mixed $data = null, array $options = []): FormInterface
            {
                return $this->formMock;
            }

            public function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse('/', $status);
            }

            public function render(string $view, array $parameters = [], Response $response = null): Response
            {
                return new Response('rendered_view');
            }
        };

        // Call controller method
        $response = $controller->create($request, $em, $passwordHasher);

        // Assertions
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }
}
