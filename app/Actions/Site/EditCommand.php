<?php

namespace App\Actions\Site;

use App\Models\Command;

class EditCommand
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function edit(Command $command, array $input): Command
    {
        $command->name = $input['name'];
        $command->command = $input['command'];
        $command->save();

        return $command;
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
