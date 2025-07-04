<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\MetricFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $server_id
 * @property ?float $load
 * @property ?float $memory_total
 * @property ?float $memory_used
 * @property ?float $memory_free
 * @property ?float $disk_total
 * @property ?float $disk_used
 * @property ?float $disk_free
 * @property-read float|int $memory_total_in_bytes
 * @property-read float|int $memory_used_in_bytes
 * @property-read float|int $memory_free_in_bytes
 * @property-read float|int $disk_total_in_bytes
 * @property-read float|int $disk_used_in_bytes
 * @property-read float|int $disk_free_in_bytes
 * @property Server $server
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Metric extends Model
{
    /** @use HasFactory<MetricFactory> */
    use HasFactory;

    protected $fillable = [
        'server_id',
        'load',
        'memory_total',
        'memory_used',
        'memory_free',
        'disk_total',
        'disk_used',
        'disk_free',
    ];

    protected $casts = [
        'server_id' => 'integer',
        'load' => 'float',
        'memory_total' => 'float',
        'memory_used' => 'float',
        'memory_free' => 'float',
        'disk_total' => 'float',
        'disk_used' => 'float',
        'disk_free' => 'float',
    ];

    /**
     * @return BelongsTo<Server, covariant $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function getMemoryTotalInBytesAttribute(): float|int
    {
        return ($this->memory_total ?? 0) * 1024;
    }

    public function getMemoryUsedInBytesAttribute(): float|int
    {
        return ($this->memory_used ?? 0) * 1024;
    }

    public function getMemoryFreeInBytesAttribute(): float|int
    {
        return ($this->memory_free ?? 0) * 1024;
    }

    public function getDiskTotalInBytesAttribute(): float|int
    {
        return ($this->disk_total ?? 0) * (1024 * 1024);
    }

    public function getDiskUsedInBytesAttribute(): float|int
    {
        return ($this->disk_used ?? 0) * (1024 * 1024);
    }

    public function getDiskFreeInBytesAttribute(): float|int
    {
        return ($this->disk_free ?? 0) * (1024 * 1024);
    }
}
