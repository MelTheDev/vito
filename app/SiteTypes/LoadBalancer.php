<?php

namespace App\SiteTypes;

use App\DTOs\DynamicFieldDTO;
use App\DTOs\DynamicFieldsCollectionDTO;
use App\Enums\LoadBalancerMethod;
use App\Enums\SiteFeature;
use App\Exceptions\SSHError;
use App\Models\Site;
use Illuminate\Validation\Rule;

class LoadBalancer extends AbstractSiteType
{
    public static function make(): self
    {
        return new self(new Site(['type' => \App\Enums\SiteType::LOAD_BALANCER]));
    }

    public function language(): string
    {
        return 'yaml';
    }

    public function supportedFeatures(): array
    {
        return [
            SiteFeature::SSL,
        ];
    }

    public function fields(): DynamicFieldsCollectionDTO
    {
        return new DynamicFieldsCollectionDTO([
            DynamicFieldDTO::make('method')
                ->select()
                ->label('Load Balancing Method')
                ->options([
                    LoadBalancerMethod::IP_HASH,
                    LoadBalancerMethod::ROUND_ROBIN,
                    LoadBalancerMethod::LEAST_CONNECTIONS,
                ]),
        ]);
    }

    public function createRules(array $input): array
    {
        return [
            'method' => [
                'required',
                Rule::in([
                    LoadBalancerMethod::IP_HASH,
                    LoadBalancerMethod::ROUND_ROBIN,
                    LoadBalancerMethod::LEAST_CONNECTIONS,
                ]),
            ],
        ];
    }

    public function data(array $input): array
    {
        return [
            'method' => $input['method'] ?? LoadBalancerMethod::ROUND_ROBIN,
        ];
    }

    /**
     * @throws SSHError
     */
    public function install(): void
    {
        $this->isolate();

        $this->site->webserver()->createVHost($this->site);
    }
}
