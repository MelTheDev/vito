<?php

namespace App\SiteTypes;

use App\DTOs\DynamicFieldsCollectionDTO;

interface SiteType
{
    public function language(): string;

    /**
     * @return array<string>
     */
    public function supportedFeatures(): array;

    public function fields(): DynamicFieldsCollectionDTO;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function createRules(array $input): array;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function createFields(array $input): array;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function data(array $input): array;

    public function install(): void;

    /**
     * @return array<array<string, string>>
     */
    public function baseCommands(): array;
}
