<?php

namespace App\Actions\PHP;

use App\Enums\PHPIniType;
use App\Models\Server;
use App\Models\Service;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdatePHPIni
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(Server $server, array $input): void
    {
        Validator::make($input, self::rules($server))->validate();

        /** @var Service $service */
        $service = $server->php($input['version']);

        $tmpName = Str::random(10).strtotime('now');
        try {
            /** @var FilesystemAdapter $storageDisk */
            $storageDisk = Storage::disk('local');

            $storageDisk->put($tmpName, $input['ini']);
            $service->server->ssh('root')->upload(
                $storageDisk->path($tmpName),
                sprintf('/etc/php/%s/%s/php.ini', $service->version, $input['type'])
            );
            $this->deleteTempFile($tmpName);
        } catch (Throwable) {
            $this->deleteTempFile($tmpName);
            throw ValidationException::withMessages([
                'ini' => __("Couldn't update php.ini (:type) file!", ['type' => $input['type']]),
            ]);
        }

        $service->restart();
    }

    private function deleteTempFile(string $name): void
    {
        if (Storage::disk('local')->exists($name)) {
            Storage::disk('local')->delete($name);
        }
    }

    /**
     * @return array<string, array<string>>
     */
    public static function rules(Server $server): array
    {
        return [
            'ini' => [
                'required',
                'string',
            ],
            'version' => [
                'required',
                Rule::exists('services', 'version')
                    ->where('server_id', $server->id)
                    ->where('type', 'php'),
            ],
            'type' => [
                'required',
                Rule::in([PHPIniType::CLI, PHPIniType::FPM]),
            ],
        ];
    }
}
