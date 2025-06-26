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

    public function __construct(User $reporter, Post $post_reported, string $reason)
    {
        $this->reporter = $reporter;
        $this->post_reported = $post_reported;
        $this->reason = $reason;
        $this->created_at = new \DateTimeImmutable();
    }
}