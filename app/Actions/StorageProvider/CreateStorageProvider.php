<?php

namespace App\Actions\StorageProvider;

use App\Models\Project;
use App\Models\StorageProvider;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateStorageProvider
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function create(User $user, Project $project, array $input): StorageProvider
    {
        Validator::make($input, self::rules($input))->validate();

        $storageProvider = new StorageProvider([
            'user_id' => $user->id,
            'provider' => $input['provider'],
            'profile' => $input['name'],
            'project_id' => isset($input['global']) && $input['global'] ? null : $project->id,
        ]);

        $storageProvider->credentials = $storageProvider->provider()->credentialData($input);

        try {
            if (! $storageProvider->provider()->connect()) {
                throw ValidationException::withMessages([
                    'provider' => __("Couldn't connect to the provider"),
                ]);
            }
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'provider' => $e->getMessage(),
            ]);
        }

        $storageProvider->save();

        return $storageProvider;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function rules(array $input): array
    {
        $rules = [
            'provider' => [
                'required',
                Rule::in(array_keys(config('storage-provider.providers'))),
            ],
            'name' => [
                'required',
            ],
        ];

        if (isset($input['provider'])) {
            $provider = (new StorageProvider(['provider' => $input['provider']]))->provider();
            $rules = array_merge($rules, $provider->validationRules());
        }

        return $rules;
    }
}
