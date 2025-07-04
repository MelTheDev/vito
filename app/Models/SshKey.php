<?php

namespace App\Models;

use Database\Factories\SshKeyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $user_id
 * @property string $name
 * @property string $public_key
 * @property User $user
 * @property Server[] $servers
 */
class SshKey extends AbstractModel
{
    /** @use HasFactory<SshKeyFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'public_key',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'public_key' => 'encrypted',
    ];

    /**
     * @return BelongsTo<User, covariant $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Server, covariant $this>
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'server_ssh_keys')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function existsOnServer(Server $server): bool
    {
        return (bool) $this->servers()->where('id', $server->id)->first();
    }
}
