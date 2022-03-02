<?php

declare(strict_types=1);

/**
 * Contao Content Navigation
 */

namespace Hofff\Contao\ContentNavigation\Navigation;

use Contao\Environment;
use Contao\StringUtil;

use function array_merge;
use function count;
use function current;
use function next;
use function prev;
use function substr;

final class ContentNavigationBuilder
{
    /** @var RelatedPages */
    private $relatedPages;

    /** @var Query\ItemsInParentQuery */
    private $itemsInParentQuery;

    /** @var Query\ItemsInColumnQuery */
    private $itemsInColumnQuery;

    public function __construct(
        RelatedPages $relatedPages,
        Query\ItemsInColumnQuery $itemsInColumnQuery,
        Query\ItemsInParentQuery $itemsInParentQuery
    ) {
        $this->relatedPages       = $relatedPages;
        $this->itemsInColumnQuery = $itemsInColumnQuery;
        $this->itemsInParentQuery = $itemsInParentQuery;
    }

    /**
     * Collect the headings from content elements and return an structured array of headings.
     *
     * @param list<object> $result          $result The database result of content elements.
     * @param int          $minLevel        Min navigation level.
     * @param int          $maxLevel        Max navigation level.
     * @param int          $currentLevel    The current heading level.
     * @param bool         $forceRequestUri Force the current request URI instead of connected page.
     *
     * @return list<array<string,mixed>>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function collect(
        array &$result,
        int $minLevel = 1,
        int $maxLevel = 6,
        int $currentLevel = 1,
        bool $forceRequestUri = false
    ): array {
        $items = [];

        do {
            $item = current($result);
            $page = $this->relatedPages->ofItem($item);

            if ($page === null) {
                continue;
            }

            // load headline and cssID
            $headline = StringUtil::deserialize($item->headline, true);
            $cssId    = StringUtil::deserialize($item->cssID, true);

            // only add if headline AND cssID is given
            if (empty($headline['value']) || empty($cssId[0])) {
                continue;
            }

            // the current heading level
            $level = (int) substr($headline['unit'], 1);

            if ($level > $currentLevel) {
                // go one level down, by calling the collect function

                // go down if the level should be collected
                if ($level <= $maxLevel) {
                    $subItems = $this->collect($result, $minLevel, $maxLevel, $currentLevel + 1, $forceRequestUri);

                    if (count($items)) {
                        $items[count($items) - 1]['subitems'] = $subItems;
                    } else {
                        $items = $subItems;
                    }
                } else {
                    // skip all items, below the max level
                    while ($item = next($result)) {
                        $headline = StringUtil::deserialize($item->headline, true);
                        $level    = (int) substr($headline['unit'], 1);

                        if ($level <= $maxLevel) {
                            prev($result);
                            break;
                        }
                    }
                }
            } elseif ($level < $currentLevel) {
                // this element is from an upper level
                // just break and return to the upper level

                prev($result);
                break;
            } elseif ($level < $minLevel) {
                // skip all upper level elements and merge all lower levels into one array
                $merge = [$items];

                while ($item = next($result)) {
                    $headline = StringUtil::deserialize($item->headline, true);
                    $level    = (int) substr($headline['unit'], 1);

                    if ($level < $minLevel) {
                        continue;
                    }

                    $subItems = $this->collect($result, $minLevel, $maxLevel, $currentLevel + 1, $forceRequestUri);

                    if (! count($subItems)) {
                        continue;
                    }

                    $merge[] = $subItems;
                }

                $items = array_merge(...$merge);
            } else {
                // add a new item of the same level
                $pageUrl = $forceRequestUri || $page->id === $GLOBALS['objPage']->id
                    ? Environment::get('indexFreeRequest')
                    : $page->getFrontendUrl();

                $arrItem = array_merge(
                    (array) $item,
                    [
                        'title' => $headline['value'],
                        'href'  => $pageUrl . '#' . $cssId[0],
                    ]
                );
                $items[] = $arrItem;
            }
        } while (next($result));

        return $items;
    }

    /**
     * Collect the headings from the parent and return a structured array of the headings.
     *
     * @param string $parentTable     The parent table.
     * @param int    $parentId        The parent name.
     * @param int    $minLevel        Min navigation level.
     * @param int    $maxLevel        Max navigation level.
     * @param bool   $forceRequestUri Force the current request URI instead of connected page.
     *
     * @return list<array<string,mixed>>
     */
    public function fromParent(
        string $parentTable,
        int $parentId,
        int $minLevel = 1,
        int $maxLevel = 6,
        bool $forceRequestUri = false
    ): array {
        $result = ($this->itemsInParentQuery)($parentTable, $parentId);

        return $this->collect($result, $minLevel, $maxLevel, 1, $forceRequestUri);
    }

    /**
     * Collect the headings from the parent and return a structured array of the headings.
     *
     * @param int  $articleId       The article id.
     * @param int  $minLevel        Min navigation level.
     * @param int  $maxLevel        Max navigation level.
     * @param bool $forceRequestUri Force the current request URI instead of connected page.
     *
     * @return list<array<string,mixed>>
     */
    public function fromArticle(
        int $articleId,
        int $minLevel = 1,
        int $maxLevel = 6,
        bool $forceRequestUri = false
    ): array {
        return $this->fromParent('tl_article', $articleId, $minLevel, $maxLevel, $forceRequestUri);
    }

    /**
     * Collect the headings from a column and return a structured array of the headings.
     *
     * @param int    $pageId          The id of the page.
     * @param string $column          The name of the column.
     * @param int    $minLevel        Min navigation level.
     * @param int    $maxLevel        Max navigation level.
     * @param bool   $forceRequestUri Force the current request URI instead of connected page.
     *
     * @return list<array<string,mixed>>
     */
    public function fromColumn(
        int $pageId,
        string $column = 'main',
        int $minLevel = 1,
        int $maxLevel = 6,
        bool $forceRequestUri = false
    ): array {
        $result = ($this->itemsInColumnQuery)($pageId, $column);

        return $this->collect($result, $minLevel, $maxLevel, 1, $forceRequestUri);
    }
}
