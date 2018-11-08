<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\Navigation\Query;

use Contao\PageModel;
use Contao\Model\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PDO;

abstract class AbstractPageQuery
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    protected function createPageModel(Statement $statement): PageModel
    {
        $row = $statement->fetch(PDO::FETCH_ASSOC);

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
