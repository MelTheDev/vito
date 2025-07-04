<?php

namespace App\SourceControlProviders;

use App\Exceptions\FailedToDeployGitHook;
use App\Exceptions\FailedToDeployGitKey;
use App\Exceptions\FailedToDestroyGitHook;
use Exception;
use Illuminate\Support\Facades\Http;

class Github extends AbstractSourceControlProvider
{
    protected string $apiUrl = 'https://api.github.com';

    public static function id(): string
    {
        return 'github';
    }

    public function connect(): bool
    {
        try {
            $res = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer '.$this->data()['token'],
            ])->get($this->apiUrl.'/user/repos');
        } catch (Exception) {
            return false;
        }

        return $res->successful();
    }

    /**
     * @throws Exception
     */
    public function getRepo(string $repo): mixed
    {
        $url = $repo !== '' && $repo !== '0' ? $this->apiUrl.'/repos/'.$repo : $this->apiUrl.'/user/repos';
        $res = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => 'Bearer '.$this->data()['token'],
        ])->get($url);

        $this->handleResponseErrors($res, $repo);

        return $res->json();
    }

    public function fullRepoUrl(string $repo, string $key): string
    {
        return sprintf('git@github.com-%s:%s.git', $key, $repo);
    }

    /**
     * @throws FailedToDeployGitHook
     */
    public function deployHook(string $repo, array $events, string $secret): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer '.$this->data()['token'],
            ])->post($this->apiUrl."/repos/$repo/hooks", [
                'name' => 'web',
                'events' => $events,
                'config' => [
                    'url' => url('/api/git-hooks?secret='.$secret),
                    'content_type' => 'json',
                ],
                'active' => true,
            ]);
        } catch (Exception $e) {
            throw new FailedToDeployGitHook($e->getMessage());
        }

        if ($response->status() != 201) {
            throw new FailedToDeployGitHook($response->body());
        }

        return [
            'hook_id' => json_decode($response->body())->id,
            'hook_response' => json_decode($response->body()),
        ];
    }

    /**
     * @throws FailedToDestroyGitHook
     */
    public function destroyHook(string $repo, string $hookId): void
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'Bearer '.$this->data()['token'],
            ])->delete($this->apiUrl."/repos/$repo/hooks/$hookId");
        } catch (Exception $e) {
            throw new FailedToDestroyGitHook($e->getMessage());
        }

        if ($response->status() != 204) {
            throw new FailedToDestroyGitHook($response->body());
        }
    }

    /**
     * @throws Exception
     */
    public function getLastCommit(string $repo, string $branch): ?array
    {
        $url = $this->apiUrl.'/repos/'.$repo.'/commits/'.$branch;
        $res = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => 'Bearer '.$this->data()['token'],
        ])->get($url);

        $this->handleResponseErrors($res, $repo);

        $commit = $res->json();
        if (isset($commit['sha']) && isset($commit['commit'])) {
            return [
                'commit_id' => $commit['sha'],
                'commit_data' => [
                    'name' => $commit['commit']['committer']['name'] ?? null,
                    'email' => $commit['commit']['committer']['email'] ?? null,
                    'message' => $commit['commit']['message'] ?? null,
                    'url' => $commit['html_url'] ?? null,
                ],
            ];
        }

        return null;
    }

    /**
     * @throws FailedToDeployGitKey
     */
    public function deployKey(string $title, string $repo, string $key): void
    {
        try {
            $response = Http::withToken($this->data()['token'])->post(
                $this->apiUrl.'/repos/'.$repo.'/keys',
                [
                    'title' => $title,
                    'key' => $key,
                    'read_only' => false,
                ]
            );
        } catch (Exception $e) {
            throw new FailedToDeployGitKey($e->getMessage());
        }

        if ($response->status() != 201) {
            throw new FailedToDeployGitKey($response->body());
        }
    }
}
