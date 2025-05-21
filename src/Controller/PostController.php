<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Likes;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Services\Comment\CommentService;
use App\Services\Comment\DTO\CommentDTO;
use ConsoleTVs\Profanity\Facades\Profanity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class PostController extends AbstractController
{
    #[Route('/post/{id}/{nbLike}', name: 'postDetail', methods: ['GET', 'POST'])]
    public function getPostDetail(Request $request, EntityManagerInterface $entityManager, CommentService $commentService, int $id, int $nbLike): Response
    {
        $userId = $request->getSession()->get('user_id');

        $post = $entityManager->getRepository(Post::class)->find($id);

        $comments = $entityManager->getRepository(Comment::class)->getCommentByPost($post);
        $commentWithLikes = [];

        $formView = null;

        foreach ($comments as $comment) {
            $commentLike = $entityManager->getRepository(Likes::class)->totalLikesPerComment($comment->getId());
            $commentWithLikes[] = [
                'comment' => $comment,
                'commentLike' => $commentLike,
            ];
        }

        if ($userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);

            $comment = new Comment();

            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $content = $form->get('content')->getData();

                $cleanContent = Profanity::blocker($content)->filter();

                $commentDTO = new CommentDTO();
                $commentDTO->content = $cleanContent;
                $commentDTO->post = $post;
                $commentDTO->user = $user;
                $commentDTO->created_at =  new \DateTimeImmutable();

                $commentService->create($commentDTO);

                return $this->redirectToRoute('postDetail', [
                    'id' => $post->getId(),
                    'nbLike' => $nbLike
                ]);
            }

            $formView = $form->createView();
        }

        return $this->render('post/postDetail.html.twig', [
            'post' => $post,
            'nbLike' => $nbLike,
            'comments' => $commentWithLikes,
            'form' => $formView,
        ]);
    }

    // #[Route('/createPost', name: 'create_post', methods: ['GET', 'POST'])]
    // public function createPost(Request $request, EntityManagerInterface $entityManager): Response
    // {

    // }

    /*#[Route('/updatePost/{id}', name: 'update_post', methods: ['GET', 'POST'])]
    public function updatePost(Request $request, EntityManagerInterface $entityManager): Response
    {

    }*/
}
