<?php

namespace App\Models;

use Database\Factories\LoadBalancerServerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $load_balancer_id
 * @property string $ip
 * @property int $port
 * @property int $weight
 * @property bool $backup
 * @property Site $loadBalancer
 */
class LoadBalancerServer extends AbstractModel
{
    /** @use HasFactory<LoadBalancerServerFactory> */
    use HasFactory;

    protected $fillable = [
        'load_balancer_id',
        'ip',
        'port',
        'weight',
        'backup',
    ];

    protected $casts = [
        'load_balancer_id' => 'integer',
        'port' => 'integer',
        'weight' => 'integer',
        'backup' => 'boolean',
    ];

    /**
     * @return BelongsTo<Site, covariant $this>
     */
    public function loadBalancer(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'load_balancer_id');
    }

    public function server(): ?Server
    {
        /** @var ?Server $server */
        $server = $this->loadBalancer->project->servers()->where('local_ip', $this->ip)->first();

        return $server;
    }
}
