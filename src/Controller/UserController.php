<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Post;
use App\Entity\User;
use App\Form\UserConnectionForm;
use App\Form\UserCreateForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Route('/', name: 'default')]
    public function index(): Response
    {
        return $this->redirectToRoute('homePage');
    }


    #[Route('/createUser', name: 'create_user', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserCreateForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );

            $user->setPassword($hashedPassword);
            $user->setRoles(["ROLE_USER"]);
            $user->setIsActive(true);
            $user->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('login_user');
        }

        return $this->render(
            'user/create.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Créer un compte'
            ]
        );
    }

    #[Route('/homePage', name: 'homePage', methods: ['GET'])]
    public function success(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();

        $postWithLikes = [];

        foreach ($posts as $post) {
            $totalLikes = $entityManager->getRepository(Likes::class)->totalLikesPerPost($post->getId());
            $postWithLikes[] = [
                'post' => $post,
                'totalLikes' => $totalLikes,
            ];
        }

        return $this->render('user/homePage.html.twig',  [
            'posts' => $postWithLikes,
        ]);
    }

    #[Route('/admin/allUser', name: 'all_user', methods: ['GET'])]
    public function allUser(Request $request): Response
    {
        return $this->redirectToRoute('homePage');
    }
}
