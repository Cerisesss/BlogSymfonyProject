<?php

namespace App\Services\Post;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Post\DTO\PostDTO;

class PostService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(PostDTO $data): Post
    {
        if (!$this->check($data)) {
            throw new \Exception('unable to create post');
        }

        $post = new Post();

        $post->setAuthor($data->user);
        $post->setTitle($data->title); 
        $post->setContent($data->content);
        $post->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    private function check(PostDTO $data): bool
    {
        return $data->user && $data->title && $data->content;
    }
}
