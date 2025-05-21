<?php

namespace App\Services\Comment;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Comment\DTO\CommentDTO;

class CommentService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(CommentDTO $data): Comment
    {
        if (!$this->check($data)) {
            throw new \Exception('unable to create comment');
        }

        $comment = new Comment();

        $comment->setContent($data->content);
        $comment->setPost($data->post); 
        $comment->setUser($data->user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    private function check(CommentDTO $data): bool
    {
        return $data->content && $data->user && $data->post;
    }
}
