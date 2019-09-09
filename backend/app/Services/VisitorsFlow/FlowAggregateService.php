<?php
declare(strict_types=1);

namespace App\Services\VisitorsFlow;

use App\Aggregates\VisitorsFlow\BrowserAggregate;
use App\Aggregates\VisitorsFlow\CountryAggregate;
use App\Aggregates\VisitorsFlow\DeviceAggregate;
use App\Aggregates\VisitorsFlow\ScreenAggregate;
use App\Aggregates\VisitorsFlow\Values\PageValue;
use App\Entities\Visit;
use App\Repositories\Contracts\GeoPositionRepository;
use App\Repositories\Contracts\PageRepository;
use App\Repositories\Contracts\VisitRepository;
use App\Repositories\Contracts\WebsiteRepository;
use App\Repositories\Elasticsearch\VisitorsFlow\BrowserCriteria;
use App\Repositories\Elasticsearch\VisitorsFlow\Contracts\VisitorFlowBrowserRepository;
use App\Repositories\Elasticsearch\VisitorsFlow\Contracts\VisitorFlowCountryRepository;
use App\Repositories\Elasticsearch\VisitorsFlow\Contracts\VisitorFlowDeviceRepository;
use App\Repositories\Elasticsearch\VisitorsFlow\Contracts\VisitorFlowScreenRepository;
use App\Repositories\Elasticsearch\VisitorsFlow\CountryCriteria;
use App\Repositories\Elasticsearch\VisitorsFlow\DeviceCriteria;
use App\Repositories\Elasticsearch\VisitorsFlow\ScreenCriteria;
use Carbon\Carbon;

final class FlowAggregateService
{
    private $pageRepository;
    private $visitRepository;
    private $visitorFlowCountryRepository;
    private $websiteRepository;
    private $geoPositionRepository;
    private $visitorFlowBrowserRepository;
    private $visitorFlowDeviceRepository;
    private $visitorFlowScreenRepository;

    public function __construct(
        PageRepository $pageRepository,
        VisitRepository $visitRepository,
        VisitorFlowCountryRepository $visitorFlowCountryRepository,
        WebsiteRepository $websiteRepository,
        GeoPositionRepository $geoPositionRepository,
        VisitorFlowBrowserRepository $visitorFlowBrowserRepository,
        VisitorFlowDeviceRepository $visitorFlowDeviceRepository,
        VisitorFlowScreenRepository $visitorFlowScreenRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->visitRepository = $visitRepository;
        $this->visitorFlowCountryRepository = $visitorFlowCountryRepository;
        $this->websiteRepository = $websiteRepository;
        $this->geoPositionRepository = $geoPositionRepository;
        $this->visitorFlowBrowserRepository = $visitorFlowBrowserRepository;
        $this->visitorFlowDeviceRepository = $visitorFlowDeviceRepository;
        $this->visitorFlowScreenRepository = $visitorFlowScreenRepository;
    }

    public function aggregate(Visit $visit): void
    {
        $previousVisit = $this->getLastVisit($visit);
        $isFirstInSession = $previousVisit === null;
        $level = $this->getLevel($visit, $isFirstInSession);

        $countryAggregate = $this->getCountryAggregate($visit, $level, $isFirstInSession, $previousVisit);
        $browserAggregate = $this->getBrowserAggregate($visit, $level, $isFirstInSession, $previousVisit);
        $deviceAggregate = $this->getDeviceAggregate($visit, $level, $isFirstInSession, $previousVisit);
        $screenAggregate = $this->getScreenAggregate($visit, $level, $isFirstInSession, $previousVisit);

        $this->updateCountryAggregate($visit, $level, $previousVisit, $countryAggregate);
        $this->updateBrowserAggregate($visit, $level, $previousVisit, $browserAggregate);
        $this->updateDeviceAggregate($visit, $level, $previousVisit, $deviceAggregate);
        $this->updateScreenAggregate($visit, $level, $previousVisit, $screenAggregate);
    }

    private function getLevel(Visit $visit, bool $isFirstInSession): int
    {
        if (!$isFirstInSession) {
            return $this->getVisitsCount($visit);
        }
        return 1;
    }

    private function updateCountryAggregate(
        Visit $visit,
        int $level,
        Visit $previousVisit,
        ?CountryAggregate $countryAggregate): void
    {
        if (!$countryAggregate) {
            $countryAggregate = $this->createCountryAggregate($visit, $level, $previousVisit);
            $this->visitorFlowCountryRepository->save($countryAggregate);
        } else {
            if ($level > 1) {
                $previousAggregate = CountryAggregate::getPreviousAggregate(
                    $this->visitorFlowCountryRepository,
                    $previousVisit,
                    $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                    $level
                );
                $previousAggregate->isLastPage = false;
                $previousAggregate->exitCount--;
                $this->visitorFlowCountryRepository->update($previousAggregate);
            }
            $countryAggregate->views++;
            $countryAggregate->exitCount++;
            $this->visitorFlowCountryRepository->update($countryAggregate);
        }
    }

    private function updateBrowserAggregate(
        Visit $visit,
        int $level,
        Visit $previousVisit,
        ?BrowserAggregate $browserAggregate): void
    {
        if (!$browserAggregate) {
            $browserAggregate = $this->createBrowserAggregate($visit, $level, $previousVisit);
            $this->visitorFlowBrowserRepository->save($browserAggregate);
        } else {
            if ($level > 1) {
                $previousAggregate = BrowserAggregate::getPreviousAggregate(
                    $this->visitorFlowBrowserRepository,
                    $previousVisit,
                    $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                    $level
                );
                $previousAggregate->isLastPage = false;
                $previousAggregate->exitCount--;
                $this->visitorFlowBrowserRepository->update($previousAggregate);
            }
            $browserAggregate->views++;
            $browserAggregate->exitCount++;
            $this->visitorFlowBrowserRepository->update($browserAggregate);
        }
    }

    private function updateDeviceAggregate(
        Visit $visit,
        int $level,
        Visit $previousVisit,
        ?DeviceAggregate $deviceAggregate): void
    {
        if (!$deviceAggregate) {
            $deviceAggregate = $this->createDeviceAggregate($visit, $level, $previousVisit);
            $this->visitorFlowDeviceRepository->save($deviceAggregate);
        } else {
            if ($level > 1) {
                $previousAggregate = DeviceAggregate::getPreviousAggregate(
                    $this->visitorFlowDeviceRepository,
                    $previousVisit,
                    $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                    $level
                );
                $previousAggregate->isLastPage = false;
                $previousAggregate->exitCount--;
                $this->visitorFlowDeviceRepository->update($previousAggregate);
            }
            $deviceAggregate->views++;
            $deviceAggregate->exitCount++;
            $this->visitorFlowDeviceRepository->update($deviceAggregate);
        }
    }

    private function updateScreenAggregate(
        Visit $visit,
        int $level,
        Visit $previousVisit,
        ?ScreenAggregate $screenAggregate): void
    {
        if (!$screenAggregate) {
            $screenAggregate = $this->createScreenAggregate($visit, $level, $previousVisit);
            $this->visitorFlowScreenRepository->save($screenAggregate);
            return;
        }
        if ($level > 1) {
            $previousAggregate = ScreenAggregate::getPreviousAggregate(
                $this->visitorFlowScreenRepository,
                $previousVisit,
                $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                $level
            );
            $previousAggregate->isLastPage = false;
            $previousAggregate->exitCount--;
            $this->visitorFlowScreenRepository->update($previousAggregate);
        }
        $screenAggregate->views++;
        $screenAggregate->exitCount++;
        $this->visitorFlowScreenRepository->update($screenAggregate);
    }

    private function getLastVisit(Visit $currentVisit): ?Visit
    {
        return $this->visitRepository->findBySessionId($currentVisit->session_id)
            ->sortBy(function (Visit $visit) {
                return (new Carbon($visit->visit_time))->getTimestamp();
            })
            ->last(function (Visit $visit) use ($currentVisit) {
                return $currentVisit->id !== $visit->id;
            });
    }

    private function getPreviousVisit(Visit $currentVisit): ?Visit
    {
        return $this->visitRepository->findBySessionId($currentVisit->session_id)
            ->filter(function (Visit $visit) use ($currentVisit) {
                return (new Carbon($currentVisit->visit_time))->greaterThan(new Carbon($visit->visit_time));
            })
            ->sortBy(function (Visit $visit) {
                return (new Carbon($visit->visit_time))->getTimestamp();
            })
            ->last();
    }

    private function getVisitsCount(Visit $currentVisit): int
    {
        return $this->visitRepository->findBySessionId($currentVisit->session_id)->count();
    }

    private function createCountryAggregate(Visit $currentVisit, int $level, ?Visit $previousVisit): CountryAggregate
    {
        $page = $this->pageRepository->getById($currentVisit->page_id);
        $website = $this->websiteRepository->getById($page->website_id);
        $geoPosition = $this->geoPositionRepository->getById($currentVisit->geo_position_id);
        $prevPage = new PageValue();
        if ($level !== 1) {
            $previousAggregate = CountryAggregate::getPreviousAggregate(
                $this->visitorFlowCountryRepository,
                $previousVisit,
                $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                $level
            );
            $previousAggregate->isLastPage = false;
            $previousAggregate->exitCount--;
            $this->visitorFlowCountryRepository->update($previousAggregate);
            $prevPage = new PageValue($previousVisit->id, $previousAggregate->targetUrl);
        }

        $isLatPage = true;
        $exitCount = 1;
        $views = 1;
        return new CountryAggregate(
            $currentVisit->id,
            $website->id,
            $page->url,
            $page->name,
            $views,
            $level,
            $isLatPage,
            $exitCount,
            $geoPosition->country,
            $prevPage
        );
    }

    private function createBrowserAggregate(Visit $currentVisit, int $level, ?Visit $previousVisit): BrowserAggregate
    {
        $page = $this->pageRepository->getById($currentVisit->page_id);
        $website = $this->websiteRepository->getById($page->website_id);
        $prevPage = new PageValue();
        if ($level !== 1) {
            $previousAggregate = BrowserAggregate::getPreviousAggregate(
                $this->visitorFlowBrowserRepository,
                $previousVisit,
                $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                $level
            );
            $previousAggregate->isLastPage = false;
            $previousAggregate->exitCount--;
            $this->visitorFlowBrowserRepository->update($previousAggregate);
            $prevPage = new PageValue($previousVisit->id, $previousAggregate->targetUrl);
        }
        $exitCount = 1;
        $isLatPage = true;
        $views = 1;
        return new BrowserAggregate(
            $currentVisit->id,
            $website->id,
            $page->url,
            $page->name,
            $views,
            $level,
            $isLatPage,
            $exitCount,
            $currentVisit->session->system->browser,
            $prevPage
        );
    }

    private function createDeviceAggregate(Visit $currentVisit, int $level, ?Visit $previousVisit): DeviceAggregate
    {
        $page = $this->pageRepository->getById($currentVisit->page_id);
        $website = $this->websiteRepository->getById($page->website_id);
        $prevPage = new PageValue();
        if ($level !== 1) {
            $previousAggregate = DeviceAggregate::getPreviousAggregate(
                $this->visitorFlowDeviceRepository,
                $previousVisit,
                $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                $level
            );
            $previousAggregate->isLastPage = false;
            $previousAggregate->exitCount--;
            $this->visitorFlowDeviceRepository->update($previousAggregate);
            $prevPage = new PageValue($previousVisit->id, $previousAggregate->targetUrl);
        }
        $exitCount = 1;
        $isLatPage = true;
        $views = 1;
        return new DeviceAggregate(
            $currentVisit->id,
            $website->id,
            $page->url,
            $page->name,
            $views,
            $level,
            $isLatPage,
            $exitCount,
            $currentVisit->session->system->device,
            $prevPage
        );
    }

    private function createScreenAggregate(Visit $currentVisit, int $level, ?Visit $previousVisit): ScreenAggregate
    {
        $page = $this->pageRepository->getById($currentVisit->page_id);
        $website = $this->websiteRepository->getById($page->website_id);
        $prevPage = new PageValue();
        if ($level !== 1) {
            $previousAggregate = ScreenAggregate::getPreviousAggregate(
                $this->visitorFlowScreenRepository,
                $previousVisit,
                $level > 2 ? ($this->getPreviousVisit($previousVisit))->page->url : 'null',
                $level
            );
            $previousAggregate->isLastPage = false;
            $previousAggregate->exitCount--;
            $this->visitorFlowScreenRepository->update($previousAggregate);
            $prevPage = new PageValue($previousVisit->id, $previousAggregate->targetUrl);
        }
        $exitCount = 1;
        $isLatPage = true;
        $views = 1;
        return new ScreenAggregate(
            $currentVisit->id,
            $website->id,
            $page->url,
            $page->name,
            $views,
            $level,
            $isLatPage,
            $exitCount,
            $currentVisit->session->system->resolution_width,
            $currentVisit->session->system->resolution_height,
            $prevPage
        );
    }

    private function getBrowserAggregate(Visit $visit, int $level, bool $isFirstInSession, Visit $previousVisit): BrowserAggregate
    {
        return $this->visitorFlowBrowserRepository->getByCriteria(
            BrowserCriteria::getCriteria(
                $visit->session->website_id,
                $visit->page->url,
                $level,
                $isFirstInSession ? 'null' : $previousVisit->page->url,
                $visit->session->system->browser
            )
        );
    }

    private function getCountryAggregate(Visit $visit, int $level, bool $isFirstInSession, Visit $previousVisit): CountryAggregate
    {
        return $this->visitorFlowCountryRepository->getByCriteria(
            CountryCriteria::getCriteria(
                $visit->session->website_id,
                $visit->page->url,
                $level,
                $isFirstInSession ? 'null' : $previousVisit->page->url,
                $visit->geo_position->country
            )
        );
    }

    private function getDeviceAggregate(Visit $visit, int $level, bool $isFirstInSession, Visit $previousVisit): DeviceAggregate
    {
        return $this->visitorFlowDeviceRepository->getByCriteria(
            DeviceCriteria::getCriteria(
                $visit->session->website_id,
                $visit->page->url,
                $level,
                $isFirstInSession ? 'null' : $previousVisit->page->url,
                $visit->session->system->device
            )
        );
    }

    private function getScreenAggregate(Visit $visit, int $level, bool $isFirstInSession, Visit $previousVisit): ScreenAggregate
    {
        return $this->visitorFlowScreenRepository->getByCriteria(
            ScreenCriteria::getCriteria(
                $visit->session->website_id,
                $visit->page->url,
                $level,
                $isFirstInSession ? 'null' : $previousVisit->page->url,
                $visit->session->system->resolution_width,
                $visit->session->system->resolution_height
            )
        );
    }
}
