<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use function array_map;
use function is_int;

final class ItemsInParentQuery extends AbstractItemQuery
{
    /** @return list<object> */
    public function __invoke(string $parentTable, int $parentId): array
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('c.*')
            ->from('tl_content', 'c')
            ->where('c.pid=:pid')
            ->andWhere('c.hofff_toc_include=:include')
            ->orderBy('c.sorting')
            ->setParameter('pid', $parentId)
            ->setParameter('empty', '')
            ->setParameter('include', '1');

        if ($parentTable === 'tl_article' || $parentTable === '') {
            $builder->andWhere('(c.ptable=:empty OR c.ptable=:ptable)');

            $builder->setParameter('ptable', 'tl_article');
        } else {
            $builder->andWhere('c.ptable=:ptable');
            $builder->setParameter('ptable', $parentTable);
        }

        $this->addPublishedCondition($builder);

        $result = $builder->execute();
        if (is_int($result)) {
            return [];
        }

        return array_map(
            static function (array $row): object {
                return (object) $row;
            },
            $result->fetchAllAssociative()
        );
    }
}
