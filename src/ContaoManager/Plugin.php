<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Hofff\Contao\TableOfContents\HofffContaoTableOfContentsBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HofffContaoTableOfContentsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['ce_navigation'])
        ];
    }
}
