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

namespace Mine\Module\Command\Creater;

use Hyperf\Command\Annotation\Command;
use Hyperf\Support\Filesystem\FileNotFoundException;
use Hyperf\Support\Filesystem\Filesystem;
use Mine\MineCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CreateFormRequest.
 */
#[Command]
class CreateFormRequest extends MineCommand
{
    protected ?string $name = 'mine:request-gen';

    protected string $module;

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php mine:module <module_name> <name>"');
        $this->setDescription('Generate validate form request class file');
        $this->addArgument(
            'module_name',
            InputArgument::REQUIRED,
            'input module name'
        );

        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'input FormRequest class file name'
        );
    }

    public function handle()
    {
        $this->module = ucfirst(trim($this->input->getArgument('module_name')));
        $this->name = ucfirst(trim($this->input->getArgument('name')));

        $fs = new Filesystem();

        try {
            $content = str_replace(
                ['{MODULE_NAME}', '{CLASS_NAME}'],
                [$this->module, $this->name],
                $fs->get($this->getStub('form_request'))
            );
        } catch (FileNotFoundException $e) {
            $this->error($e->getMessage());
            exit;
        }

        $fs->put($this->getModulePath() . $this->name . 'FormRequest.php', $content);

        $this->info('<info>[INFO] Created request:</info> ' . $this->name . 'FormRequest.php');
    }
}
