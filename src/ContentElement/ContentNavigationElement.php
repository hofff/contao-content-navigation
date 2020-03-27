<?php

declare(strict_types=1);

/**
 * Contao Content Navigation
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname. Büro für visuelle Kommunikation Nicky Hoff
 */

namespace Hofff\Contao\ContentNavigation\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\StringUtil;
use Hofff\Contao\ContentNavigation\Navigation\ContentNavigationBuilder;
use Patchwork\Utf8;
use function count;
use function sprintf;

final class ContentNavigationElement extends ContentElement
{
    /** @var string */
    protected $strTemplate = 'ce_hofff_content_navigation';

    /** @var ContentNavigationBuilder */
    private $contentNavigationBuilder;

    public function __construct(ContentModel $objElement, string $strColumn = 'main')
    {
        parent::__construct($objElement, $strColumn);

        $this->contentNavigationBuilder = self::getContainer()->get(ContentNavigationBuilder::class);
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

        $tpl = new FrontendTemplate('hofff_content_nav_default');
        $tpl->setData(['items' => $items, 'level' => $level]);

        return $tpl->parse();
    }

    protected function compile(): void
    {
        if ($this->hofff_toc_source === '') {
            $arrItems = $this->contentNavigationBuilder->fromParent(
                (string) $this->ptable,
                (int) $this->pid,
                (int) $this->hofff_toc_min_level,
                (int) $this->hofff_toc_max_level,
                (bool) $this->hofff_toc_force_request_uri
            );
        } elseif (is_numeric($this->hofff_toc_source)) {
            $arrItems = $this->contentNavigationBuilder->fromArticle(
                (int) $this->hofff_toc_source,
                (int) $this->hofff_toc_min_level,
                (int) $this->hofff_toc_max_level,
                (bool) $this->hofff_toc_force_request_uri
            );
        } else {
            $arrItems = $this->contentNavigationBuilder->fromColumn(
                (int) $GLOBALS['objPage']->id,
                $this->hofff_toc_source,
                (int) $this->hofff_toc_min_level,
                (int) $this->hofff_toc_max_level,
                (bool) $this->hofff_toc_force_request_uri
            );
        }

        $this->Template->items          = $this->parseItems($arrItems);
        $this->Template->request        = Environment::get('indexFreeRequest');
        $this->Template->skipId         = 'skipNavigation' . $this->id;
        $this->Template->skipNavigation = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
    }
}
