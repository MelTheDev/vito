<?php

namespace App\Actions\ServerLog;

use App\Models\Server;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateLog
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function create(Server $server, array $input): void
    {
        Validator::make($input, self::rules())->validate();

        $server->logs()->create([
            'is_remote' => true,
            'name' => $input['path'],
            'type' => 'remote',
            'disk' => 'ssh',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function rules(): array
    {
        return [
            'path' => 'required',
        ];
    }
}
