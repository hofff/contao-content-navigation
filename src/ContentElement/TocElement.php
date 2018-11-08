<?php

declare(strict_types=1);

/**
 * Contao Table of Contents
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname . Büro für visuelle Kommunikation Nicky Hoff
 */

namespace Hofff\Contao\TableOfContens\ContentElement;

use Contao\ContentElement;

final class TocElement extends ContentElement
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $strTemplate = 'mod_ce_navigation';

    protected function flatten(&$arrItems, $intLevel = 1): string
    {
        if (!count($arrItems)) {
            return '';
        }
        foreach ($arrItems as &$arrItem) {
            if (isset($arrItem['subitems'])) {
                $arrItem['subitems'] = $this->flatten($arrItem['subitems'], $intLevel + 1);
            }
            $arrItem['class'] = '';
        }

        $arrItems[0]['class']                    = 'first';
        $arrItems[count($arrItems) - 1]['class'] = 'last';

        $tpl        = new \FrontendTemplate('ce_navigation');
        $tpl->items = $arrItems;
        $tpl->level = $intLevel;

        return $tpl->parse();
    }

    protected function compile(): void
    {
        global $objPage;
        $this->import('CeNavigation\\ArticleNavigation', 'ArticleNavigation');
        $this->ArticleNavigation = new \CeNavigation\ArticleNavigation();

        if (is_numeric($this->navigation_article)) {
            $arrItems = $this->ArticleNavigation->fromArticle(
                $this->navigation_article,
                $this->navigation_min_level,
                $this->navigation_max_level
            );
        } else {
            $arrItems = $this->ArticleNavigation->fromColumn(
                $objPage->id,
                $this->navigation_article,
                $this->navigation_min_level,
                $this->navigation_max_level
            );
        }

        $this->Template->items = $this->flatten($arrItems);
    }

}
