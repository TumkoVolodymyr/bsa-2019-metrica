<?php
declare(strict_types=1);

namespace App\Repositories\Elasticsearch\VisitorsFlow\Contracts;

interface Criteria
{
    public static function getCriteria(int $websiteId, string $targetUrl, int $level, ?string $prevPageUrl, ...$params);
}
