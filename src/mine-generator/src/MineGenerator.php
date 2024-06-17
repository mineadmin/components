<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Mine\Generator;

use Composer\InstalledVersions;
use Psr\Container\ContainerInterface;

abstract class MineGenerator
{
    public const NO = 1;

    public const YES = 2;

    protected string $stubDir;

    protected string $namespace;

    /**
     * MineGenerator constructor.
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->setStubDir(
            realpath(
                InstalledVersions::getInstallPath(
                    'xmo/mine-generator'
                )
            ) . DIRECTORY_SEPARATOR . 'Stubs' . DIRECTORY_SEPARATOR
        );
    }

    public function getStubDir(): string
    {
        return $this->stubDir;
    }

    public function setStubDir(string $stubDir)
    {
        $this->stubDir = $stubDir;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function replace(): self
    {
        return $this;
    }
}
