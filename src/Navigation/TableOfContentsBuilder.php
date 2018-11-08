<?php

declare(strict_types=1);

/**
 * Contao Table of Contents
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname . Büro für visuelle Kommunikation Nicky Hoff
 */

namespace Hofff\Contao\TableOfContents\Navigation;

use Contao\StringUtil;
use Hofff\Contao\TableOfContents\Navigation\Query;
use function array_merge;
use function count;
use function next;
use function prev;

final class TableOfContentsBuilder
{
    /**
     * @var RelatedPages
     */
    private $relatedPages;

    /**
     * @var Query\ItemsInParentQuery
     */
    private $itemsInParentQuery;

    /**
     * @var Query\ItemsInColumnQuery
     */
    private $itemsInColumnQuery;

    /**
     * ArticleNavigationBuilder constructor.
     *
     * @param RelatedPages       $relatedPages
     * @param Query\ItemsInColumnQuery $itemsInColumnQuery
     * @param Query\ItemsInParentQuery $itemsInParentQuery
     */
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
     * @param array|object[] $result       $result The database result of content elements.
     * @param int            $minLevel     Min navigation level.
     * @param int            $maxLevel     Max navigation level.
     * @param integer        $currentLevel The current heading level.
     *
     * @return array
     */
    private function collect(array &$result, int $minLevel = 1, int $maxLevel = 6, int $currentLevel = 1): array
    {
        $items = [];
        $page  = null;

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
                    $subItems = $this->collect($result, $minLevel, $maxLevel, $currentLevel + 1);

                    if (count($items)) {
                        $items[count($items) - 1]['subitems'] = $subItems;
                    } else {
                        $items = $subItems;
                    }
                } // skip all items, below the max level
                else {
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

                    if ($level >= $minLevel) {
                        $subItems = $this->collect($result, $minLevel, $maxLevel, $currentLevel + 1);

                        if (count($subItems)) {
                            $merge[] = $subItems;
                        }
                    }
                }

                $items = array_merge(...$merge);
            } else {
                // add a new item of the same level
                $arrItem = array_merge(
                    (array) $item,
                    [
                        'title' => $headline['value'],
                        'href'  => $page->getFrontendUrl() . '#' . $cssId[0],
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
     * @param string $parentTable The parent table.
     * @param int    $parentId    The parent name.
     * @param int    $minLevel    Min navigation level.
     * @param int    $maxLevel    Max navigation level.
     *
     * @return array
     */
    public function fromParent(string $parentTable, int $parentId, int $minLevel = 1, int $maxLevel = 6): array
    {
        return $this->collect(
            ($this->itemsInParentQuery)($parentTable, $parentId),
            $minLevel,
            $maxLevel
        );
    }

    /**
     * Collect the headings from the parent and return a structured array of the headings.
     *
     * @param int $articleId The article id.
     * @param int $minLevel  Min navigation level.
     * @param int $maxLevel  Max navigation level.
     *
     * @return array
     */
    public function fromArticle(int $articleId, int $minLevel = 1, int $maxLevel = 6): array
    {
        return $this->fromParent('tl_article', $articleId, $minLevel, $maxLevel);
    }

    /**
     * Collect the headings from a column and return a structured array of the headings.
     *
     * @param int    $pageId   The id of the page.
     * @param string $column   The name of the column.
     * @param int    $minLevel Min navigation level.
     * @param int    $maxLevel Max navigation level.
     *
     * @return array
     */
    public function fromColumn(int $pageId, string $column = 'main', int $minLevel = 1, int $maxLevel = 6): array
    {
        return $this->collect(
            ($this->itemsInColumnQuery)($pageId, $column),
            $minLevel,
            $maxLevel
        );
    }
}
