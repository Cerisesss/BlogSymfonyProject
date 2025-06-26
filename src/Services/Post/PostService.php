<?php

namespace App\Services\Post;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Post\DTO\PostDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostService
{
    public function __construct(private EntityManagerInterface $em, private ValidatorInterface $validator) {}

    public function create(PostDTO $data): Post
    {
        $errors = $this->validator->validate($data);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \Exception(implode("\n", $messages));
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

    // private function check(PostDTO $data): bool
    // {
    //     return $data->user && $data->title && $data->content;
    // }
}
