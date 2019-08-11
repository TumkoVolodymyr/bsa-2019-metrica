<?php
declare(strict_types=1);

namespace App\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Session extends Model
{
    protected $fillable = [
        'start_session',
        'end_session',
        'visitor_id',
        'entrance_page_id',
        'demographic_id',
        'device_id',
        'system_id',
    ];

    protected $with = ['visitor', 'page', 'demographic', 'device', 'system'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function demographic(): BelongsTo
    {
        return $this->belongsTo(Demographic::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereTime('updated_at', '<', (Carbon::now())->subMinutes(30)->toDateString());
    }
}
