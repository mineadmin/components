<?php

namespace Xmo\AppStore\Service;

interface PluginService
{
    /**
     * 插件安装后文件识别
     */
    public const INSTALL_LOCK_FILE = 'install.lock';

    /**
     * Reads the Mine plugin information through the given directory.
     * And check the legitimacy of the plugin
     * @param string $path
     * @return array
     */
    public function read(string $path): array;

    /**
     * @param string $path
     * @return void
     */
    public function register(string $path): void;


    /**
     * Installation of local plug-ins.
     */
    public function installExtension(string $path): void;

    /**
     * Uninstall locally installed plug-ins.
     */
    public function uninstallExtension(string $path): void;

    /**
     * Get all locally installed extensions.
     * @throws \JsonException
     */
    public function getLocalExtensions(): array;
}