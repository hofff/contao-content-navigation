<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use function array_map;

final class ItemsInColumnQuery extends AbstractItemQuery
{
    /** @return list<object> */
    public function __invoke(int $pageId, string $column): array
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('c.*')
            ->from('tl_content', 'c')
            ->innerJoin('c', 'tl_article', 'a', 'a.id = c.pid')
            ->where('a.pid=:pageId')
            ->andWhere('c.hofff_toc_include=:include')
            ->andWhere('a.inColumn=:column')
            ->orderBy('a.sorting,c.sorting')
            ->setParameter('pageId', $pageId)
            ->setParameter('column', $column)
            ->setParameter('include', '1');

        $this->addPublishedCondition($builder, 'a', 'published', false);
        $this->addPublishedCondition($builder, 'c');

        $result = $builder->executeQuery();

        return array_map(
            static function (array $row): object {
                return (object) $row;
            },
            $result->fetchAllAssociative(),
        );
    }
}
