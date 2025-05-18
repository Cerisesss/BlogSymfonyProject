<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/createUser', name: 'create_user', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );

            $user->setPassword($hashedPassword);
            $user->setRole('user');
            $user->setIsActive(true);
            $user->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('connection_user');
        }

        return $this->render(
            'user/create.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Créer un compte'
            ]
        );
    }

    #[Route('/connection', name: 'connection_user', methods: ['GET', 'POST'])]
    public function connexion(Request $request, EntityManagerInterface $entityManager)
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData();

            $formEmail = $utilisateur->getEmail();
            $formPassword = $utilisateur->getPassword();

            $dbUser = $entityManager->getRepository(User::class)->findOneBy(
                [
                    'email' => $formEmail,
                ]
            );

            if ($dbUser && password_verify($formPassword, $dbUser->getPassword())) {
                return $this->redirectToRoute('homePage', ['id' => $dbUser->getId()]);
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

    #[Route('/homePage/{id}', name: 'homePage', methods: ['GET'])]
    public function success(EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        return $this->render('user/homePage.html.twig',  ['user' => $user]);
    }
}
