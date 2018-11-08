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
use Contao\ContentModel;
use Contao\FrontendTemplate;
use Hofff\Contao\TableOfContens\Navigation\TableOfContentsBuilder;
use function count;

final class TocElement extends ContentElement
{
    /**
     * Template name.
     *
     * @var string
     */
    protected $strTemplate = 'ce_hofff_toc';

    /**
     * @var TableOfContentsBuilder
     */
    private $tableOfContentsBuilder;

    public function __construct(ContentModel $objElement, string $strColumn = 'main')
    {
        parent::__construct($objElement, $strColumn);

        $this->tableOfContentsBuilder = self::getContainer()->get(TableOfContentsBuilder::class);
    }

    private function parseItems(array $items, int $level = 1): string
    {
        if (!count($items)) {
            return '';
        }

        foreach ($items as $item) {
            if (isset($item['subitems'])) {
                $item['subitems'] = $this->parseItems($item['subitems'], $level + 1);
            }
            $item['class'] = '';
        }

        $items[0]['class']                 = 'first';
        $items[count($items) - 1]['class'] = 'last';

        $tpl = new FrontendTemplate('toc_default');
        $tpl->setData(['items' => $items, 'level' => $level]);

        return $tpl->parse();
    }

    protected function compile(): void
    {
        if (is_numeric($this->navigation_article)) {
            $arrItems = $this->tableOfContentsBuilder->fromArticle(
                (int) $this->navigation_article,
                (int) $this->navigation_min_level,
                (int) $this->navigation_max_level
            );
        } else {
            $arrItems = $this->tableOfContentsBuilder->fromColumn(
                (int) $GLOBALS['objPage']->id,
                $this->navigation_article,
                (int) $this->navigation_min_level,
                (int) $this->navigation_max_level
            );
        }

        $this->Template->items = $this->parseItems($arrItems);
    }

}
