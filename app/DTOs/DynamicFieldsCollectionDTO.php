<?php

namespace App\DTOs;

readonly class DynamicFieldsCollectionDTO
{
    /**
     * @param  array<int, DynamicFieldDTO>  $fields
     */
    public function __construct(
        private array $fields = [],
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = $field->toArray();
        }

        return $fields;
    }
}
