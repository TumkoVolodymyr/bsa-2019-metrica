<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Visitors\NewVisitorsCountFilterData;
use App\DataTransformer\Visitors\ActiveVisitorItem;
use App\Entities\Visitor;
use App\Repositories\Contracts\VisitorRepository;
use App\Utils\DatePeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentVisitorRepository implements VisitorRepository
{
    public function all(): Collection
    {
        return Visitor::all();
    }

    public function getById(int $id): Visitor
    {
        return Visitor::findOrFail($id);
    }

    public function save(Visitor $visitor): Visitor
    {
        $visitor->save();
        return $visitor;
    }

    public function updateLastActivity(Visitor $visitor): void
    {
        $visitor->last_activity = Carbon::now()->toDateTimeString();
        $visitor->save();
    }

    public function countVisitorsBetweenDate(DatePeriod $period): int
    {
        return Visitor::whereHas('sessions', function (Builder $query) use ($period) {
            $query->whereDateBetween($period);
        })
            ->forUserWebsite()
            ->count();
    }

    public function newest(): Collection
    {
        return new Collection();
    }

    public function newestCount(NewVisitorsCountFilterData $filterData, int $websiteId): int
    {
        return Visitor::whereCreatedAtBetween($filterData->getStartDate(), $filterData->getEndDate())
            ->where('website_id', $websiteId)
            ->count();
    }

    public function countSinglePageInactiveSessionBetweenDate(DatePeriod $period): int
    {
        return Visitor::has('sessions', '=', '1')
            ->whereHas('sessions', function (Builder $query) use ($period) {
                $query->whereDateBetween($period)
                    ->has('visits', '=', '1')
                    ->inactive($period->getEndDate());
            })
            ->forUserWebsite()
            ->count();
    }

    public function getVisitorsOfWebsite(int $websiteId): Collection
    {
        return Visitor::where('website_id', $websiteId)->get();
    }

    public function countAllVisitorsGroupByCountry(string $startDate, string $endDate): Collection
    {
        return Visitor::forUserWebsite()
                ->join('visits', 'visitors.id', '=', 'visits.visitor_id')
                ->join('geo_positions', 'geo_positions.id', '=', 'visits.geo_position_id')
                ->select(DB::raw('COUNT(DISTINCT visitors.id) as all_visitors_count, geo_positions.country as country'))
                ->whereBetween('visits.visit_time', [$startDate, $endDate])
                ->groupBy('geo_positions.country')
                ->get();
    }

    public function countNewVisitorsGroupByCountry(string $startDate, string $endDate): Collection
    {
        return Visitor::forUserWebsite()
            ->where('visitors.created_at', '>', $startDate)
            ->join('visits', 'visitors.id', '=', 'visits.visitor_id')
            ->join('geo_positions', 'geo_positions.id', '=', 'visits.geo_position_id')
            ->select(DB::raw('COUNT(DISTINCT visitors.id) as new_visitors_count, geo_positions.country as country'))
            ->whereBetween('visits.visit_time', [$startDate, $endDate])
            ->groupBy('geo_positions.country')
            ->get();
    }

    public function countInactiveSingleVisitSessionGroupByCountry(string $startDate, string $endDate): Collection
    {
        return Visitor::has('sessions', '=', '1')
            ->whereHas('sessions', function (Builder $query) use ($startDate, $endDate) {
                $query->has('visits', '=', '1')
                    ->where('updated_at', '<', (new Carbon($endDate))->subMinutes(30)->toDateTimeString())
                    ->whereBetween('start_session', [$startDate, $endDate]);
            })
            ->forUserWebsite()
            ->join('visits', 'visitors.id', '=', 'visits.visitor_id')
            ->join('geo_positions', 'visits.geo_position_id', '=', 'geo_positions.id')
            ->select(DB::raw('count(visitors.id) as bounced_visitors_count, geo_positions.country as country'))
            ->groupBy('geo_positions.country')
            ->get();
    }
    public function getAllActivityVisitors(int $websiteId): SupportCollection
    {
        $subQueryFirst = "SELECT DISTINCT ON (v.id) visitor_id, v.last_activity as max_date, v.website_id, p.url 
                          from visitors v
                          LEFT JOIN  visits vs on vs.visitor_id = v.id left join pages p on vs.page_id = p.id
                          where v.last_activity > NOW() - interval '5 minutes' and v.website_id = ".$websiteId."
                          order by v.id, vs.id desc";
        $query = DB::raw($subQueryFirst);
        $result = DB::select((string)$query);

        return collect($result)->map(function ($item) {
            return new ActiveVisitorItem($item->url, $item->visitor_id, $item->max_date);
        });
    }
}
