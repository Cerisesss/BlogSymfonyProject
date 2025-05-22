<?php

namespace App\Services\Post\DTO;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class PostDTO
{
    #[Assert\NotBlank]
    public User $user;
    public string $title;
    public string $content;
    public \DateTimeImmutable $created_at;
}