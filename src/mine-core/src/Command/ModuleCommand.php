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

namespace Mine\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Concerns\Confirmable;
use Hyperf\Database\Migrations\Migrator;
use Mine\Helper\ConsoleTable;
use Mine\Interfaces\ServiceInterface\ModuleServiceInterface;
use Mine\Mine;
use Mine\MineCommand;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Support\make;

/**
 * Class ModuleCommand.
 */
#[Command]
class ModuleCommand extends MineCommand
{
    use Confirmable;

    /**
     * 安装命令.
     */
    protected ?string $name = 'mine:module';

    protected Mine $mine;

    protected Migrator $migrator;

    public function __construct(Migrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
    }

    public function configure()
    {
        parent::configure();
        $this->mine = make(Mine::class);
        $this->setHelp('run "php bin/hyperf.php mine:module --name cms --option install"');
        $this->setDescription('install command of module MineAdmin');
        $this->addOption(
            'option',
            null,
            InputOption::VALUE_OPTIONAL,
            'input "--option list" show module list, "-option install" install module or "-option uninstall" uninstall module',
            'list'
        );
        $this->addOption(
            'name',
            null,
            InputOption::VALUE_OPTIONAL,
            'input module name or "list" command show module list',
        );
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $name = $this->input->getOption('name');
        $option = $this->input->getOption('option');
        $modules = $this->mine->getModuleInfo();

        // 模块名不能叫list，list是展示模块列表
        if ($option === 'list') {
            $table = new ConsoleTable();
            $table->setHeader(['Name', 'Description', 'Version', 'Install', 'Enable']);
            foreach ($modules as $mod) {
                $row = [
                    $mod['name'] ?? 'Null',
                    $mod['description'] ?? 'Null',
                    $mod['version'] ?? 'Null',
                    isset($mod['installed']) && $mod['installed'] === true ? 'yes' : 'no',
                    isset($mod['enabled']) && $mod['enabled'] === true ? 'yes' : 'no',
                ];
                $table->addRow($row);
            }
            echo $table->render();
            exit;
        }

        $service = make(ModuleServiceInterface::class);
        $name = ucfirst($name);

        // other module
        if (! empty($name) && isset($modules[$name])) {
            if (empty($option)) {
                $this->line($this->getRedText('Please input the operation command for the module: -o install or -o uninstall'));
                exit;
            }

            if ($option === 'install') {
                $this->call('mine:migrate-run', ['name' => $name, '--force' => 'true']);
                $this->call('mine:seeder-run', ['name' => $name, '--force' => 'true']);
                $this->line(
                    sprintf(
                        ' "%s" module install complete, Please run it again "%s" command! ',
                        $this->getGreenText($name),
                        $this->getGreenText('php bin/hyperf.php start')
                    )
                );
            }

            if ($option === 'uninstall') {
                $input = ucfirst($name) . ' uninstall';
                $answer = $this->ask(sprintf('You are now ready to unload the module for safety. Please input: %s', $this->getRedText($input)));
                if ($input !== $answer) {
                    $this->line('Input error');
                    exit;
                }

                if (! $this->confirmToProceed()) {
                    $this->line('A delete is already being performed');
                    exit;
                }

                // 是否删除数据
                if ($this->confirm('Whether to delete the data?', false)) {
                    $this->migrator->setOutput($this->output);
                    $path = $this->getUninstallPath($name);
                    $this->migrator->rollback([$path]);
                    is_dir($path . '/Update') && $this->migrator->rollback([$path . '/Update']);
                }

                $service->deleteModule($name);

                $this->line(sprintf('Uninstall complete, Please run it again "%s" command! ', $this->getGreenText('php bin/hyperf.php start')));
            }
        } else {
            $this->line($this->getRedText(sprintf('The "%s" module does not exist....', $name)));
        }
    }

    protected function getUninstallPath(string $moduleName): string
    {
        return BASE_PATH . '/app/' . $moduleName . '/Database/Migrations';
    }
}
