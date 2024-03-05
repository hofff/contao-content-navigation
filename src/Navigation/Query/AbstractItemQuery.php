<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Date;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

use function sprintf;

abstract class AbstractItemQuery
{
    public function __construct(
        protected Connection $connection,
        protected TokenChecker $tokenChecker,
    ) {
    }

    protected function addPublishedCondition(
        QueryBuilder $builder,
        string $alias = 'c',
        string $column = 'invisible',
        bool $inverted = true,
    ): void {
        if ($this->tokenChecker->isPreviewMode()) {
            return;
        }

        $time = Date::floorToMinute();

        $builder->andWhere(sprintf('(%1$s.start=:%1$s_empty OR %1$s.start<=:%1$s_start)', $alias));
        $builder->andWhere(sprintf('(%1$s.stop=:%1$s_empty OR %1$s.stop>:%1$s_stop)', $alias));
        $builder->andWhere(sprintf('%1$s.%2$s=:%1$s_visible', $alias, $column));
        $builder->setParameter($alias . '_start', $time);
        $builder->setParameter($alias . '_stop', $time + 60);
        $builder->setParameter($alias . '_empty', '');
        $builder->setParameter($alias . '_visible', $inverted ? '' : '1');
    }
}
