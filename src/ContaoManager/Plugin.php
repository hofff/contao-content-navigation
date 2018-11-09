<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Hofff\Contao\ContentNavigation\HofffContentNavigationBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HofffContentNavigationBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['ce_navigation'])
        ];
    }
}
