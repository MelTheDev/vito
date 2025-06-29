<?php

namespace App\Actions\Server;

use App\Models\Server;
use App\ValidationRules\RestrictedIPAddressesRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EditServer
{
    /**
     * @param  array<string, mixed>  $input
     * @return Server $server
     *
     * @throws ValidationException
     */
    public function edit(Server $server, array $input): Server
    {
        Validator::make($input, self::rules($server))->validate();

        $checkConnection = false;
        if (isset($input['name'])) {
            $server->name = $input['name'];
        }
        if (isset($input['ip'])) {
            if ($server->ip !== $input['ip']) {
                $checkConnection = true;
            }
            $server->ip = $input['ip'];
        }
        if (isset($input['local_ip'])) {
            $server->local_ip = $input['local_ip'];
        }
        if (isset($input['port'])) {
            if ($server->port !== $input['port']) {
                $checkConnection = true;
            }
            $server->port = $input['port'];
        }
        $server->save();

        if ($checkConnection) {
            return $server->checkConnection();
        }

        return $server;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(Server $server): array
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('servers')->where('project_id', $server->project_id)->ignore($server->id),
            ],
            'ip' => [
                'string',
                new RestrictedIPAddressesRule,
                Rule::unique('servers')->where('project_id', $server->project_id)->ignore($server->id),
            ],
            'local_ip' => [
                'nullable',
                'string',
                Rule::unique('servers')->where('project_id', $server->project_id)->ignore($server->id),
            ],
            'port' => [
                'integer',
                'min:1',
                'max:65535',
            ],
        ];
    }
}
