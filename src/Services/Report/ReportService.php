<?php

namespace App\Services\Report;

use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Report\DTO\ReportDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReportService
{
    public function __construct(private EntityManagerInterface $em, private ValidatorInterface $validator) {}

    public function create(ReportDTO $data): Report
    {
        $errors = $this->validator->validate($data);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \Exception(implode("\n", $messages));
        }

        $report = new Report();

        $report->setReporter($data->reporter);
        $report->setPostReported($data->post_reported);
        $report->setReason($data->reason);
        $report->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }

    // private function check(ReportDTO $data): bool
    // {
    //     return $data->reporter && $data->post_reported && $data->reason;
    // }
}
