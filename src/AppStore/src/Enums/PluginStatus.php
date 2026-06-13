<?php

declare(strict_types=1);

namespace Mine\AppStore\Enums;

enum PluginStatus: string
{
    case Discovered = 'Discovered';
    case Enabled = 'Enabled';
    case Disabled = 'Disabled';
    case Failed = 'Failed';
    case Invalid = 'Invalid';
}
