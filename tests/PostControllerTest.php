<?php

namespace App\Tests\Controller;

use App\Controller\PostController;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use App\Services\Comment\CommentService;
use App\Services\Post\PostService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class PostControllerTest extends WebTestCase
{
    public function testGetPostDetailRendersTemplate()
    {
        $post = $this->createMock(Post::class);
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $comment = $this->createMock(Comment::class);
        $comment->method('getId')->willReturn(10);

        // Mock repository extending EntityRepository for correct typing
        $commentRepo = $this->getMockBuilder(\App\Repository\CommentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commentRepo->method('getCommentByPost')->willReturn([$comment]);

        $likesRepo = $this->getMockBuilder(\App\Repository\LikesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $likesRepo->method('totalLikesPerComment')->willReturn(5);

        // Properly mock repositories extending EntityRepository
        $postRepo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $postRepo->method('find')->willReturn($post);

        $userRepo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->method('find')->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnMap([
            [Post::class, $postRepo],
            [Comment::class, $commentRepo],
            [User::class, $userRepo],
            [\App\Entity\Likes::class, $likesRepo],
        ]);

        $commentService = $this->createMock(CommentService::class);

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);
        $form->method('createView')->willReturn(new FormView());

        // Use onlyMethods for existing methods
        $controller = $this->getMockBuilder(PostController::class)
            ->onlyMethods(['getUser', 'createForm', 'redirectToRoute', 'render'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);
        $controller->method('createForm')->willReturn($form);
        $controller->expects($this->once())
            ->method('render')
            ->with(
                'post/postDetail.html.twig',
                $this->callback(function ($params) use ($post) {
                    return $params['post'] === $post
                        && $params['nbLike'] === 0
                        && is_array($params['comments'])
                        && $params['form'] instanceof FormView;
                })
            )
            ->willReturn(new Response());

        $request = new Request();

        $controller->getPostDetail($request, $entityManager, $commentService, 1, 0);
    }

    public function testAllUserPostRendersTemplate()
    {
        $user = $this->createMock(User::class);
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(1);
        $user->method('getId')->willReturn(1);
        // Return a Collection, not an array
        $user->method('getPosts')->willReturn(new ArrayCollection([$post]));

        $likesRepo = $this->getMockBuilder(\App\Repository\LikesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $likesRepo->method('totalLikesPerPost')->willReturn(3);

        $userRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepo->method('find')->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnMap([
            [User::class, $userRepo],
            [\App\Entity\Likes::class, $likesRepo],
        ]);

        $postService = $this->createMock(PostService::class);

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);
        $form->method('createView')->willReturn(new FormView());

        $controller = $this->getMockBuilder(PostController::class)
            ->onlyMethods(['getUser', 'createForm', 'redirectToRoute', 'render'])
            ->getMock();

        $controller->method('getUser')->willReturn($user);
        $controller->method('createForm')->willReturn($form);
        $controller->expects($this->once())
            ->method('render')
            ->with(
                'post/userPosts.html.twig',
                $this->callback(function ($params) {
                    return isset($params['posts'])
                        && isset($params['form'])
                        && $params['form'] instanceof FormView;
                })
            )
            ->willReturn(new Response());

        $request = new Request();

        $controller->allUserPost($request, $entityManager, $postService);
    }
}
