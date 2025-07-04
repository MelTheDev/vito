<?php

namespace App\Actions\PHP;

use App\Enums\PHPIniType;
use App\Models\Server;
use App\Models\Service;
use App\Services\PHP\PHP;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class GetPHPIni
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function getIni(Server $server, array $input): string
    {
        $this->validate($server, $input);

        /** @var Service $php */
        $php = $server->php($input['version']);

        try {
            /** @var PHP $handler */
            $handler = $php->handler();

            return $handler->getPHPIni($input['type']);
        } catch (Throwable $e) {
            throw ValidationException::withMessages(
                ['ini' => $e->getMessage()]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function validate(Server $server, array $input): void
    {
        Validator::make($input, [
            'type' => [
                'required',
                Rule::in([PHPIniType::CLI, PHPIniType::FPM]),
            ],
        ])->validate();

        if (! isset($input['version']) || ! in_array($input['version'], $server->installedPHPVersions())) {
            throw ValidationException::withMessages(
                ['version' => __('This version is not installed')]
            );
        }
    }
}
