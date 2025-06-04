<?php

namespace App\Actions\Site;

use App\Exceptions\SSHError;
use App\Models\Site;
use Illuminate\Support\Facades\Validator;

class UpdateEnv
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws SSHError
     */
    public function update(Site $site, array $input): void
    {
        Validator::make($input, [
            'env' => ['required', 'string'],
        ])->validate();

        $site->server->os()->write(
            $site->path.'/.env',
            trim((string) $input['env']),
            $site->user,
        );
    }
}
