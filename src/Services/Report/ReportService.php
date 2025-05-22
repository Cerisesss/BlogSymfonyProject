<?php

namespace App\Services\Report;

use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Report\DTO\ReportDTO;

class ReportService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(ReportDTO $data): Report
    {
        if (!$this->check($data)) {
            throw new \Exception('unable to create report');
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

    private function check(ReportDTO $data): bool
    {
        return $data->reporter && $data->post_reported && $data->reason;
    }
}
