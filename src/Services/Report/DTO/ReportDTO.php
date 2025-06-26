<?php

namespace App\Services\Report\DTO;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class ReportDTO
{
    #[Assert\NotNull]
    public User $reporter;

    #[Assert\NotNull]
    public Post $post_reported;

    #[Assert\NotBlank]
    public string $reason;
    
    public \DateTimeImmutable $created_at;
}