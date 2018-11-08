<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\Navigation\Query;

use Contao\Model\Registry;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use PDO;

final class ArticlePageQuery
{
    private const QUERY = <<<'SQL'
SELECT
    p.*
FROM
    tl_page p
INNER JOIN
    tl_article a
    ON a.pid = p.id
WHERE a.id = :articleId
LIMIT 0,1
SQL;

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(int $articleId): ?PageModel
    {
        $statement = $this->connection->prepare(self::QUERY);
        $statement->bindValue('articleId', $articleId);

        if (!$statement->execute() || $statement->rowCount() === 0) {
            return null;
        }

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
