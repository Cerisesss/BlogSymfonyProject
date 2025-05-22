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

    #[Route('/login', name: 'login_user', methods: ['GET', 'POST'])]
    public function connexion(Request $request, EntityManagerInterface $entityManager)
    {
        $user = new User();
        $form = $this->createForm(UserConnectionForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formEmail = $user->getEmail();
            $formPassword = $user->getPassword();

            try {
                $response = $this->client->request('POST', 'http://nginx_web/api/login_check', [
                    'json' => [
                        'username' => $formEmail,
                        'password' => $formPassword,
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = $response->toArray();
                    $jwt = $data['token'];

                    $dbUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $formEmail]);

                    // Stock le token et l'id user en session
                    $request->getSession()->set('token', $jwt);
                    $request->getSession()->set('user_id', $dbUser->getId());
                    $request->getSession()->set('user_role', $dbUser->getRoles());

                    return $this->redirectToRoute('homePage');
                } else {
                    throw new \Exception("Login failed ");
                }
            } catch (\Exception $e) {
                dump($e->getMessage());
            }
        }

        return $this->render(
            'user/create.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Connexion'
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

    #[Route('/logout', name: 'logout_user', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear(); 

        return $this->redirectToRoute('login_user');
    }

    #[Route('/admin/allUser', name: 'all_user', methods: ['GET'])]
    public function allUser(Request $request): Response
    {
        return $this->redirectToRoute('homepage');
    }
}
