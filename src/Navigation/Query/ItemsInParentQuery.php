<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\Navigation\Query;

use PDO;

final class ItemsInParentQuery extends AbstractItemQuery
{
    public function __invoke(string $parentTable, int $parentId): array
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('c.*')
            ->from('tl_content', 'c')
            ->where('c.pid=:pid')
            ->andWhere('c.hofff_toc_exclude=:exclude')
            ->orderBy('c.sorting')
            ->setParameter('pid', $parentId)
            ->setParameter('empty', '')
            ->setParameter('exclude', '');

        if ($parentTable === 'tl_article' || $parentTable === '') {
            $builder->andWhere('(c.ptable=:empty OR c.ptable=:ptable)');

            $builder->setParameter('ptable', 'tl_article');
        } else {
            $builder->andWhere('c.ptable=:ptable');
            $builder->setParameter('ptable', $parentTable);
        }

        $this->addPublishedCondition($builder);

        return $builder->execute()->fetchAll(PDO::FETCH_OBJ);
    }
}
