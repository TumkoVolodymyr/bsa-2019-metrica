<?php

namespace App\Events;

use App\Entities\Visit;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class VisitCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $visit;

    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('active-users.' . $this->visit->page->website_id);
    }

    public function broadcastWith()
    {
        return [
            'page' => $this->visit->page->url,
            'visitor' => $this->visit->visitor_id,
            'time_notification' => $this->visit->created_at,
        ];
    }
}
