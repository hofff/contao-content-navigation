<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\Navigation\Query;

use Contao\PageModel;

final class ArticlePageQuery extends AbstractPageQuery
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


    public function __invoke(int $articleId): ?PageModel
    {
        $statement = $this->connection->prepare(self::QUERY);
        $statement->bindValue('articleId', $articleId);

        if (!$statement->execute() || $statement->rowCount() === 0) {
            return null;
        }

        return $this->createPageModel($statement);
    }
}
