<?php

namespace App\SSH\OS;

use App\Exceptions\SSHError;
use App\Models\Server;

class Systemd
{
    public function __construct(protected Server $server) {}

    /**
     * @throws SSHError
     */
    public function status(string $unit): string
    {
        $command = <<<EOD
            sudo systemctl status $unit | cat
        EOD;

        return $this->server->ssh()->exec($command, sprintf('status-%s', $unit));
    }

    /**
     * @throws SSHError
     */
    public function start(string $unit): string
    {
        $command = <<<EOD
            sudo systemctl start $unit
            sudo systemctl status $unit | cat
        EOD;

        return $this->server->ssh()->exec($command, sprintf('start-%s', $unit));
    }

    /**
     * @throws SSHError
     */
    public function stop(string $unit): string
    {
        $command = <<<EOD
            sudo systemctl stop $unit
            sudo systemctl status $unit | cat
        EOD;

        return $this->server->ssh()->exec($command, sprintf('stop-%s', $unit));
    }

    /**
     * @throws SSHError
     */
    public function restart(string $unit): string
    {
        $command = <<<EOD
            sudo systemctl restart $unit
            sudo systemctl status $unit | cat
        EOD;

        return $this->server->ssh()->exec($command, sprintf('restart-%s', $unit));
    }

    /**
     * @throws SSHError
     */
    public function enable(string $unit): string
    {
        $command = <<<EOD
            sudo systemctl start $unit
            sudo systemctl enable $unit
            sudo systemctl status $unit | cat
        EOD;

        return $this->server->ssh()->exec($command, sprintf('enable-%s', $unit));
    }

    /**
     * @throws SSHError
     */
    public function disable(string $unit): string
    {
        $command = <<<EOD
            sudo systemctl stop $unit
            sudo systemctl disable $unit
            sudo systemctl status $unit | cat
        EOD;

        return $this->server->ssh()->exec($command, sprintf('disable-%s', $unit));
    }

    /**
     * @throws SSHError
     */
    public function reload(): string
    {
        $command = <<<'EOD'
            sudo systemctl daemon-reload
        EOD;

        return $this->server->ssh()->exec($command, 'reload-systemctl');
    }
}
