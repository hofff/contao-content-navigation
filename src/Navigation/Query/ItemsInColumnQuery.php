<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use PDO;

final class ItemsInColumnQuery extends AbstractItemQuery
{
    public function __invoke(int $pageId, string $column): array
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('c.*')
            ->from('tl_content', 'c')
            ->innerJoin('c', 'tl_article', 'a', 'a.id = c.pid')
            ->where('a.pid=:pageId')
            ->andWhere('c.hofff_toc_exclude=:exclude')
            ->andWhere('a.inColumn=:column')
            ->orderBy('a.sorting,c.sorting')
            ->setParameter('pageId', $pageId)
            ->setParameter('column', $column)
            ->setParameter('exclude', '');

        $this->addPublishedCondition($builder, 'a', 'published', false);
        $this->addPublishedCondition($builder, 'c');

        return $builder->execute()->fetchAll(PDO::FETCH_OBJ);
    }
}
