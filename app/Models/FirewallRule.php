<?php

namespace App\Models;

use App\Enums\FirewallRuleStatus;
use Database\Factories\FirewallRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $server_id
 * @property string $name
 * @property string $type
 * @property string $protocol
 * @property int $port
 * @property string $source
 * @property ?string $mask
 * @property string $note
 * @property string $status
 * @property Server $server
 */
class FirewallRule extends AbstractModel
{
    /** @use HasFactory<FirewallRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'server_id',
        'type',
        'protocol',
        'port',
        'source',
        'mask',
        'note',
        'status',
    ];

    protected $casts = [
        'server_id' => 'integer',
        'port' => 'integer',
    ];

    /**
     * @var array<string, string>
     */
    public static array $statusColors = [
        FirewallRuleStatus::CREATING => 'info',
        FirewallRuleStatus::UPDATING => 'warning',
        FirewallRuleStatus::DELETING => 'danger',
        FirewallRuleStatus::READY => 'success',
        FirewallRuleStatus::FAILED => 'danger',
    ];

    /**
     * @return BelongsTo<Server, covariant $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
