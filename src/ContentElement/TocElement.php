<?php

declare(strict_types=1);

/**
 * Contao Table of Contents
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname . Büro für visuelle Kommunikation Nicky Hoff
 */

namespace Hofff\Contao\TableOfContents\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\StringUtil;
use Hofff\Contao\TableOfContents\Navigation\TableOfContentsBuilder;
use Patchwork\Utf8;
use function count;
use function sprintf;

final class TocElement extends ContentElement
{
    /** @var string */
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

    public function generate(): string
    {
        if (TL_MODE === 'BE') {
            $template           = new BackendTemplate('be_wildcard');
            $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['CTE'][$this->type][0]) . ' ###';
            $template->title    = $this->headline;
            $template->id       = $this->id;
            $template->link     = $GLOBALS['TL_LANG']['CTE'][$this->type][0];
            $template->href     = sprintf(
                Environment::get('indexFreeRequest') . 'contao?do=%s&amp;table=tl_content&amp;act=edit&amp;id=%s',
                Input::get('do'),
                $this->id
            );

            return $template->parse();
        }

        return parent::generate();
    }

    private function parseItems(array $items, int $level = 1): string
    {
        if (!count($items)) {
            return '';
        }

        foreach ($items as &$item) {
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
        if ($this->hofff_toc_source === '') {
            $arrItems = $this->tableOfContentsBuilder->fromParent(
                (string) $this->ptable,
                (int) $this->pid,
                (int) $this->hofff_toc_min_level,
                (int) $this->hofff_toc_max_level
            );
        } elseif (is_numeric($this->hofff_toc_source)) {
            $arrItems = $this->tableOfContentsBuilder->fromArticle(
                (int) $this->hofff_toc_source,
                (int) $this->hofff_toc_min_level,
                (int) $this->hofff_toc_max_level
            );
        } else {
            $arrItems = $this->tableOfContentsBuilder->fromColumn(
                (int) $GLOBALS['objPage']->id,
                $this->hofff_toc_source,
                (int) $this->hofff_toc_min_level,
                (int) $this->hofff_toc_max_level
            );
        }

        $this->Template->items          = $this->parseItems($arrItems);
        $this->Template->request        = Environment::get('indexFreeRequest');
        $this->Template->skipId         = 'skipNavigation' . $this->id;
        $this->Template->skipNavigation = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
    }

}
