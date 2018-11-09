<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Request;

use function defined;

final class PreviewModeDetector
{
    public function isEnabled(): bool
    {
        return defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true;
    }
}
