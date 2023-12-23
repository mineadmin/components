<?php

namespace Mine\Command;

use Hyperf\Command\Command;

class MineGenServiceCommand extends Command
{
    protected ?string $signature = 'mine:gen-crud-service
                                    {path: the base path of project}
                                    {--model=: The class name or absolute path of the model}
                                    {--mode: Based on which strategy, the default is to only generate query contracts}
                                    {--search-params: Field name to generate query conditions}
                                    {--sort-field: sort fields,like id desc,created_at,desc}';
    protected function configure()
    {
        $this->setDescription('基于 Ast语法树解析 快速生成Service');
    }

    public function handle()
    {
        $path = $this->input->getArgument('path');
        $model = $this->input->getOption('model');
        $mode = $this->input->getOption('mode');
        $searchParams = $this->input->getOption('search-params');
        $sortField = $this->input->getOption('sort-field');

    }
}