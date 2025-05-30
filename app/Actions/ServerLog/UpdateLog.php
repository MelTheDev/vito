<?php

namespace App\Actions\ServerLog;

use App\Models\ServerLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateLog
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(ServerLog $serverLog, array $input): void
    {
        Validator::make($input, self::rules())->validate();

        $serverLog->update([
            'name' => $input['path'],
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
