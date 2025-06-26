<?php

namespace App\Services\Comment;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Comment\DTO\CommentDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentService
{
    public function __construct(private EntityManagerInterface $em, private ValidatorInterface $validator) {}

    public function create(CommentDTO $data): Comment
    {
        $errors = $this->validator->validate($data);

        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
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
