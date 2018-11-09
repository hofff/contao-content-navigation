<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation\Query;

use Contao\PageModel;
use function var_dump;

final class JumpToPageQuery extends AbstractPageQuery
{
    public function __invoke(int $parentId, string $parentTable, string $categoryTable): ?PageModel
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from($categoryTable, 'c')
            ->innerJoin('c', $parentTable, 't', 'c.id=t.pid')
            ->innerJoin('t', 'tl_page', 'p', 'p.id=c.jumpTo')
            ->where('t.id=:parentId')
            ->setMaxResults(1)
            ->setParameter('parentId', $parentId)
            ->execute();

        if ($statement->rowCount() === 0) {
            return null;
        }

        return $this->createPageModel($statement);
    }
}
