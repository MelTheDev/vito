<?php

namespace App\Actions\Site;

use App\Exceptions\SSHError;
use App\Models\Site;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdatePHPVersion
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws SSHError
     */
    public function update(Site $site, array $input): void
    {
        Validator::make($input, self::rules($site))->validate();

        $site->changePHPVersion($input['version']);
    }

    /**
     * @return array<string, array<string>>
     */
    public static function rules(Site $site): array
    {
        return [
            'version' => [
                'required',
                Rule::exists('services', 'version')
                    ->where('server_id', $site->server_id)
                    ->where('type', 'php'),
            ],
        ];
    }
}
