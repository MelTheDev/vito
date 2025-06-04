<?php

namespace App\Actions\Site;

use App\Models\Command;
use App\Models\Site;
use Illuminate\Support\Facades\Validator;

class CreateCommand
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(Site $site, array $input): Command
    {
        Validator::make($input, self::rules())->validate();

        $script = new Command([
            'site_id' => $site->id,
            'name' => $input['name'],
            'command' => $input['command'],
        ]);
        $script->save();

        return $script;
    }

    /**
     * @return array<string, array<string>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'command' => ['required', 'string'],
        ];
    }
}
