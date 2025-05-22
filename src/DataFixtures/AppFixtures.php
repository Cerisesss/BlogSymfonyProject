<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Likes;
use App\Entity\Report;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__ . '/data.json');
        $jsonData = json_decode($json);

        $userId = [];
        $postId = [];
        $commentId = [];

        foreach ($jsonData->users as $user) {
            $newUser = new User();

            $newUser->setEmail($user->email);
            $password = $this->hasher->hashPassword($newUser, $user->password);
            $newUser->setPassword($password);
            $newUser->setUsername($user->username);
            $newUser->setRoles($user->roles);
            $newUser->setCreatedAt(new \DateTimeImmutable());
            $newUser->setIsActive(true);

            $userId[$user->id] = $newUser;

            $manager->persist($newUser);
        }

        $manager->flush();

        foreach ($jsonData->posts as $post) {
            $newPost = new Post();
;
            $newPost->setAuthor($userId[$post->author_id]);
            $newPost->setTitle($post->title);
            $newPost->setContent($post->content);
            $newPost->setCreatedAt(new \DateTimeImmutable());
            $newPost->setUpdatedAt(new \DateTimeImmutable());

            $postId[$post->id] = $newPost;

            $manager->persist($newPost);
        }

        foreach ($jsonData->comments as $comment) {
            $newComment = new Comment();

            $newComment->setUser($userId[$comment->user_id]);
            $newComment->setPost($postId[$comment->post_id]);
            $newComment->setContent($comment->content);
            $newComment->setCreatedAt(new \DateTimeImmutable());

            $commentId[$comment->id] = $newComment;

            $manager->persist($newComment);
        }

        $manager->flush();

        foreach ($jsonData->likes as $like) {
            $newLike = new Likes();

            $newLike->setUserLike($userId[$like->user_like_id]);

            if ($like->post_like_id == null) {
                $newLike->setCommentLike($commentId[$like->comment_like_id]);
            } else {
                $newLike->setPostLike($postId[$like->post_like_id]);
            }

            $manager->persist($newLike);
        }

        foreach ($jsonData->reports as $report) {
            $newReport = new Report();

            $newReport->setReporter($userId[$report->reporter_id]);
            $newReport->setPostReported($postId[$report->post_reported_id]);
            $newReport->setReason($report->reason);
            $newReport->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($newReport);
        }

        $manager->flush();
    }
}
