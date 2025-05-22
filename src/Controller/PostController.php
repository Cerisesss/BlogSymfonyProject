<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Likes;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Services\Comment\CommentService;
use App\Services\Comment\DTO\CommentDTO;
use App\Services\Post\DTO\PostDTO;
use App\Services\Post\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PostController extends AbstractController
{
    #[Route('/post/{id}/{nbLike}', name: 'postDetail', methods: ['GET', 'POST'])]
    public function getPostDetail(Request $request, EntityManagerInterface $entityManager, CommentService $commentService, int $id, int $nbLike): Response
    {
        $userSession = $this->getUser();
        $userId = ($userSession instanceof \App\Entity\User) ? $userSession->getId() : null;

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

                $cleanContent = \ConsoleTVs\Profanity\Builder::blocker($content)->filter();

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

    #[IsGranted('ROLE_USER')]
    #[Route('/postUser', name: 'post_user', methods: ['GET', 'POST'])]
    public function allUserPost(Request $request, EntityManagerInterface $entityManager, PostService $postService): Response
    {
        $userSession = $this->getUser();
        $userId = ($userSession instanceof \App\Entity\User) ? $userSession->getId() : null;

        $user = $entityManager->getRepository(User::class)->find($userId);
        $posts = $user->getPosts();

        $postsLikes = [];

        foreach ($posts as $post) {
            $totalLikes = $entityManager->getRepository(Likes::class)->totalLikesPerPost($post->getId());
            $postsLikes[] = [
                'post' => $post,
                'totalLikes' => $totalLikes,
            ];
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();
            $title = $form->get('title')->getData();

            $cleanContent = \ConsoleTVs\Profanity\Builder::blocker($content)->filter();
            $cleanTitle = \ConsoleTVs\Profanity\Builder::blocker($title)->filter();

            $postDTO = new PostDTO();
            $postDTO->title = $cleanTitle;
            $postDTO->content = $cleanContent;
            $postDTO->user = $user;
            $postDTO->created_at = new \DateTimeImmutable();

            $newPost = $postService->create($postDTO);

            return $this->redirectToRoute('postDetail', [
                'id' => $newPost->getId(),
                'nbLike' => 0,
            ]);
        }

        return $this->render('post/userPosts.html.twig', [
            'posts' => $postsLikes,
            'form' => $form->createView(),
        ]);
    }


    /*#[Route('/updatePost/{id}', name: 'update_post', methods: ['GET', 'POST'])]
    public function updatePost(Request $request, EntityManagerInterface $entityManager): Response
    {

    }*/
}
