<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\Navigation;

use Contao\PageModel;
use Hofff\Contao\TableOfContents\Navigation\Query\ArticlePageQuery;
use function array_key_exists;

final class RelatedPages
{
    /** @var ArticlePageQuery  */
    private $articlePageQuery;

    /** @var array<int,PageModel|null> */
    private $cache = [];

    /**
     * RelatedPages constructor.
     *
     * @param $articlePageQuery
     */
    public function __construct(ArticlePageQuery $articlePageQuery)
    {
        $this->articlePageQuery = $articlePageQuery;
    }

    /**
     * Get related page for a given item.
     *
     * @param object $item
     *
     * @return PageModel|null
     */
    public function ofItem($item): ?PageModel
    {
        if ($item->ptable === '' || $item->ptable === 'tl_article') {
            return $this->getArticlePage($item->pid);
        }

        return $GLOBALS['objPage'];
    }

    /**
     * Get article of a page.
     *
     * @param int $articleId
     *
     * @return PageModel|null
     */
    private function getArticlePage(int $articleId): ?PageModel
    {
        if (!array_key_exists($articleId, $this->cache)) {
            $this->cache[$articleId] = ($this->articlePageQuery)($articleId);
        }

        return $this->cache[$articleId];
    }
}
