<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use Contao\Date;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Hofff\Contao\ContentNavigation\Request\PreviewModeDetector;

abstract class AbstractItemQuery
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Preview mode detector.
     *
     * @var PreviewModeDetector
     */
    protected $previewMode;

    public function __construct(Connection $connection, PreviewModeDetector $previewMode)
    {
        $this->connection  = $connection;
        $this->previewMode = $previewMode;
    }

    protected function addPublishedCondition(
        QueryBuilder $builder,
        string $alias = 'c',
        string $column = 'invisible',
        $inverted = true
    ): void {
        if ($this->previewMode->isEnabled()) {
            return;
        }

        $time = Date::floorToMinute();

        $builder->andWhere(sprintf('(%1$s.start=:%1$s_empty OR %1$s.start<=:%1$s_start)', $alias));
        $builder->andWhere(sprintf('(%1$s.stop=:%1$s_empty OR %1$s.stop>:%1$s_stop)', $alias));
        $builder->andWhere(sprintf('%1$s.%2$s=:%1$s_visible', $alias, $column));
        $builder->setParameter($alias . '_start', $time);
        $builder->setParameter($alias . '_stop', ($time + 60));
        $builder->setParameter($alias . '_empty', '');
        $builder->setParameter($alias . '_visible', $inverted ? '' : '1');
    }
}
