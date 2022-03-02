<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use Contao\Model\Registry;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\Result as ForwardCompatibilityResult;
use Doctrine\DBAL\Result;
use InvalidArgumentException;

abstract class AbstractPageQuery
{
    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Result|ForwardCompatibilityResult $statement
     */
    protected function createPageModel($statement): PageModel
    {
        $row = $statement->fetchAssociative();
        if ($row === false) {
            throw new InvalidArgumentException('No page given');
        }

        $registry = Registry::getInstance();
        $model    = $registry->fetch(PageModel::getTable(), $row['id']);

        if ($model instanceof PageModel) {
            return $model;
        }

        $model = new PageModel();
        $model->setRow($row);

        $registry->register($model);

        return $model;
    }
}
