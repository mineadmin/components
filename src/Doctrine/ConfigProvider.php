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

namespace Mine\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__ . '/',
                    ],
                ],
            ],
            'dependencies' => [
                ManagerRegistry::class => static function (ContainerInterface $container) {
                    return $container->get(Factory::class)->makeRegistry();
                },
                EntityManagerInterface::class => static function (ContainerInterface $container) {
                    return $container->get(ManagerRegistry::class)->getManager();
                },
            ],
            'publish' => [
                [
                    'id' => 'doctrine',
                    'description' => 'The config for doctrine.',
                    'source' => __DIR__ . '/publish/doctrine.php',
                    'destination' => BASE_PATH . '/config/autoload/doctrine.php',
                ],
            ],
        ];
    }
}
