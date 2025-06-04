<?php

namespace App\Actions\Script;

use App\Models\Script;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CreateScript
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(User $user, array $input): Script
    {
        Validator::make($input, self::rules())->validate();

        $script = new Script([
            'user_id' => $user->id,
            'name' => $input['name'],
            'content' => $input['content'],
            'project_id' => isset($input['global']) && $input['global'] ? null : $user->current_project_id,
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
            'content' => ['required', 'string'],
        ];
    }
}
