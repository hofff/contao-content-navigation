<?php

declare(strict_types=1);

namespace Hofff\Contao\TableOfContents\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use Doctrine\DBAL\Connection;
use function is_array;
use PDO;

final class ContentDcaListener
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Contao framework.
     *
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * ContentDcaListener constructor.
     *
     * @param Connection               $connection
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(Connection $connection, ContaoFrameworkInterface $framework)
    {
        $this->connection = $connection;
        $this->framework  = $framework;
    }

    public function adjustPalettes(): void
    {
        if (!isset($GLOBALS['TL_DCA']['tl_content']['palettes'])
            || !is_array($GLOBALS['TL_DCA']['tl_content']['palettes'])
        ) {
            return;
        }

        $manipulator = PaletteManipulator::create()
            ->addField('hofff_toc_exclude', 'expert_legend', PaletteManipulator::POSITION_APPEND);

        foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $name => $config) {
            if (is_array($config)) {
                continue;
            }

            $manipulator->applyToPalette($name, 'tl_content');
        }
    }

    /**
     * Return all content elements as array.
     *
     * @param DataContainer $dataContainer
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function sourceOptions(DataContainer $dataContainer): array
    {
        if ($GLOBALS['TL_DCA']['tl_content']['config']['ptable'] !== 'tl_article') {
            return [];
        }

        $columns = [];
        foreach (['header', 'left', 'main', 'right', 'footer'] as $column) {
            $columns[$column] = $GLOBALS['TL_LANG']['COLS'][$column] ?? $column;
        }

        $articles  = [];
        $statement = $this->connection->prepare(
            '
			SELECT
				a.id,
				a.title,
				a.inColumn
			FROM
				tl_article a
			INNER JOIN
				tl_article b
				ON a.pid = b.pid
			INNER JOIN
				tl_content c
				ON c.pid = b.id
			WHERE
				c.id = :id
			ORDER BY
				a.inColumn,
				a.sorting'
        );

        $statement->bindValue('id', $dataContainer->id);
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $articles[$row->id] = sprintf(
                '%s [%s]',
                $row->title,
                $GLOBALS['TL_LANG']['COLS'][$row->inColumn] ?? $row->inColumn
            );
        }

        return [
            $GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_column'] => $columns,
            $GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_page']   => $articles,
        ];
    }
}
