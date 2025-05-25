<?php

namespace App\SiteTypes;

use App\DTOs\DynamicFieldDTO;
use App\DTOs\DynamicFieldsCollectionDTO;
use App\Enums\SiteFeature;
use App\Exceptions\FailedToDeployGitKey;
use App\Exceptions\SSHError;
use App\Models\Site;
use App\SSH\Composer\Composer;
use App\SSH\Git\Git;
use Illuminate\Validation\Rule;

class PHPSite extends AbstractSiteType
{
    public static function make(): self
    {
        return new self(new Site(['type' => \App\Enums\SiteType::PHP]));
    }

    public function language(): string
    {
        return 'php';
    }

    public function supportedFeatures(): array
    {
        return [
            SiteFeature::DEPLOYMENT,
            SiteFeature::COMMANDS,
            SiteFeature::ENV,
            SiteFeature::SSL,
            SiteFeature::WORKERS,
        ];
    }

    public function fields(): DynamicFieldsCollectionDTO
    {
        return new DynamicFieldsCollectionDTO([
            DynamicFieldDTO::make('php_version')
                ->component()
                ->label('PHP Version'),
            DynamicFieldDTO::make('source_control')
                ->component()
                ->label('Source Control'),
            DynamicFieldDTO::make('web_directory')
                ->text()
                ->label('Web Directory')
                ->placeholder('For / leave empty')
                ->description('The relative path of your website from /home/vito/your-domain/'),
            DynamicFieldDTO::make('repository')
                ->text()
                ->label('Repository')
                ->placeholder('organization/repository'),
            DynamicFieldDTO::make('branch')
                ->text()
                ->label('Branch')
                ->default('main'),
            DynamicFieldDTO::make('composer')
                ->checkbox()
                ->label('Run `composer install --no-dev`')
                ->default(false),
        ]);
    }

    public function createRules(array $input): array
    {
        return [
            'php_version' => [
                'required',
                Rule::in($this->site->server->installedPHPVersions()),
            ],
            'source_control' => [
                'required',
                Rule::exists('source_controls', 'id'),
            ],
            'web_directory' => [
                'nullable',
            ],
            'repository' => [
                'required',
            ],
            'branch' => [
                'required',
            ],
            'composer' => [
                'nullable',
            ],
        ];
    }

    public function createFields(array $input): array
    {
        return [
            'web_directory' => $input['web_directory'] ?? '',
            'source_control_id' => $input['source_control'] ?? '',
            'repository' => $input['repository'] ?? '',
            'branch' => $input['branch'] ?? '',
            'php_version' => $input['php_version'] ?? '',
            'composer' => $input['php_version'] ?? '',
        ];
    }

    public function data(array $input): array
    {
        return [
            'composer' => isset($input['composer']) && $input['composer'],
        ];
    }

    /**
     * @throws FailedToDeployGitKey
     * @throws SSHError
     */
    public function install(): void
    {
        $this->isolate();
        $this->site->webserver()->createVHost($this->site);
        $this->progress(15);
        $this->deployKey();
        $this->progress(30);
        app(Git::class)->clone($this->site);
        $this->progress(65);
        $this->site->php()?->restart();
        if ($this->site->type_data['composer']) {
            app(Composer::class)->installDependencies($this->site);
        }
    }

    public function baseCommands(): array
    {
        return [
            [
                'name' => 'Install Composer Dependencies',
                'command' => 'composer install --no-dev --no-interaction --no-progress',
            ],
        ];
    }
}
