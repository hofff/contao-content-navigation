<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

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
    ON p.id = a.pid
WHERE a.id = :articleId
LIMIT 0,1
SQL;

    public function __invoke(int $articleId): PageModel|null
    {
        $statement = $this->connection->prepare(self::QUERY);
        $statement->bindValue('articleId', $articleId);
        $result = $statement->executeQuery();

        if ($result->rowCount() === 0) {
            return null;
        }

        return $this->createPageModel($result);
    }
}
