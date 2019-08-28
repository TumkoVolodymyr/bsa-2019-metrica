<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface TablePageViewsRepository
{
    public function getCountPageViewsByPage(string $from, string $to, int $websiteId): array;

    public function getCountBounceRateByPage(string $from, string $to, int $websiteId): array;

    public function getCountExitRateByPage(string $from, string $to, int $websiteId): array;

    public function getPageNamesAndTitles(string $from, string $to, int $websiteId): array;
}