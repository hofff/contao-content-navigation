<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation;

use Contao\PageModel;
use Hofff\Contao\ContentNavigation\Navigation\Query\ArticlePageQuery;
use Hofff\Contao\ContentNavigation\Navigation\Query\JumpToPageQuery;

use function array_key_exists;

final class RelatedPages
{
    /** @var array<string,array<int,PageModel|null>> */
    private array $cache = [];

    /** @param array<string,string> $jumpToMapping */
    public function __construct(
        private readonly ArticlePageQuery $articlePageQuery,
        private readonly JumpToPageQuery $jumpToPageQuery,
        private readonly array $jumpToMapping,
    ) {
    }

    /**
     * Get related page for a given item.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function ofItem(object $item): PageModel|null
    {
        $parentId = (int) $item->pid;

        if (isset($this->jumpToMapping[$item->ptable])) {
            return $this->getJumpToPage($parentId, $item->ptable, $this->jumpToMapping[$item->ptable]);
        }

        return match ($item->ptable) {
            '', 'tl_article' => $this->getArticlePage($parentId),
            default => $GLOBALS['objPage'] ?? null,
        };
    }

    /**
     * Get article of a page.
     */
    private function getArticlePage(int $articleId): PageModel|null
    {
        if (! isset($this->cache['tl_article']) || ! array_key_exists($articleId, $this->cache['tl_article'])) {
            $this->cache['tl_article'][$articleId] = ($this->articlePageQuery)($articleId);
        }

        return $this->cache['tl_article'][$articleId];
    }

    private function getJumpToPage(int $parentId, string $parentTable, string $categoryTable): PageModel|null
    {
        if (! isset($this->cache[$parentTable]) || ! array_key_exists($parentId, $this->cache[$parentTable])) {
            $this->cache[$parentTable][$parentId] = ($this->jumpToPageQuery)($parentId, $parentTable, $categoryTable);
        }

        return $this->cache[$parentTable][$parentId];
    }
}
