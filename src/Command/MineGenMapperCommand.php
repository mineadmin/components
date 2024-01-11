<?php

namespace Mine\Command;

use Hyperf\Command\Command;

class MineGenMapperCommand extends Command
{
    protected ?string $signature = 'mine:gen-mapper
                                    {path: The generated mapper root directory}
                                    {--model=: The class name or absolute path of the model}
                                    {--mode: Based on which strategy, the default is to only generate query contracts}
                                    {--search-params: Field name to generate query conditions}
                                    {--sort-field: sort fields,like id desc,created_at,desc}';
}