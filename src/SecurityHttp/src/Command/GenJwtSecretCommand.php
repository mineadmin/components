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

namespace Mine\Security\Http\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as Base;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GenJwtSecretCommand extends Base
{
    protected ?string $name = 'mine:gen-jwt-secret';

    public function __invoke()
    {
        $secretName = $this->getSecretName();
        $value = $this->generator();
        $envPath = $this->getEnvPath();

        if (! file_exists($envPath)) {
            $this->error('.env file not is exists!');
            return;
        }
        if (\Mine\Helper\Str::contains(file_get_contents($envPath), $secretName) === false) {
            file_put_contents($envPath, "\n{$secretName}={$value}\n", FILE_APPEND);
        } else {
            file_put_contents($envPath, preg_replace(
                "~{$secretName}\\s*=\\s*[^\n]*~",
                "{$secretName}=\"{$value}\"",
                file_get_contents($envPath)
            ));
        }

        $this->info('jwt secret generator successfully:' . $value);
    }

    public function getSecretName(): string
    {
        return Str::upper($this->input->getOption('secret-name'));
    }

    public function getEnvPath(): string
    {
        return BASE_PATH . '/.env';
    }

    public function generator(): string
    {
        return base64_encode(random_bytes(64));
    }

    protected function configure()
    {
        $this->setHelp('run "php bin/hyperf.php mine:gen-jwt" create the new jwt secret');
        $this->setDescription('MineAdmin system gen jwt command');
    }

    protected function getOptions(): array
    {
        return [
            'secret-name', 'sn', InputOption::VALUE_OPTIONAL, 'The jwt secret name.default:jwt_secret', 'jwt_secret',
        ];
    }
}
