<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\Navigation\Query;

use Contao\Date;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Hofff\Contao\TableOfContents\Request\PreviewModeDetector;

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

    protected function addPublishedCondition(QueryBuilder $builder, string $alias = 'c'): void
    {
        if ($this->previewMode->isEnabled()) {
            return;
        }

        $time = Date::floorToMinute();

        $builder->andWhere(sprintf('(%1$s.start=:empty OR %1$s.start<=:start)', $alias));
        $builder->andWhere(sprintf('(%1$s.stop=:empty OR %1$s.stop>:stop)', $alias));
        $builder->andWhere(sprintf('%1$s.invisible=:empty', $alias));
        $builder->setParameter('start', $time);
        $builder->setParameter('stop', ($time + 60));
    }
}
