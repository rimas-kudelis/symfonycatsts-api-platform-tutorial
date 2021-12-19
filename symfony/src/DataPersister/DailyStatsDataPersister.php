<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\ApiResource\DailyStats;
use Psr\Log\LoggerInterface;

class DailyStatsDataPersister implements DataPersisterInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function supports($data): bool
    {
        return $data instanceof DailyStats;
    }

    /**
     * @param DailyStats $data
     */
    public function persist($data)
    {
        $this->logger->info(sprintf('Update the visitors to %d.', $data->totalVisitors));
    }

    public function remove($data)
    {
        throw new \Exception('Not supported.');
    }
}
