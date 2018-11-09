<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\EventListener\Dca;

use Contao\Backend;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContentNavigation\Navigation\Query\ArticlePageQuery;
use function is_array;
use Patchwork\Utf8;
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
     * @var ArticlePageQuery
     */
    private $articlePageQuery;

    /**
     * ContentDcaListener constructor.
     *
     * @param Connection       $connection
     * @param ArticlePageQuery $articlePageQuery
     */
    public function __construct(Connection $connection, ArticlePageQuery $articlePageQuery)
    {
        $this->connection       = $connection;
        $this->articlePageQuery = $articlePageQuery;
    }

    public function adjustPalettes(): void
    {
        if (!isset($GLOBALS['TL_DCA']['tl_content']['palettes'])
            || !is_array($GLOBALS['TL_DCA']['tl_content']['palettes'])
        ) {
            return;
        }

        $manipulator = PaletteManipulator::create()
            ->addField('hofff_toc_include', 'cssID', PaletteManipulator::POSITION_BEFORE);

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

        return [
            $GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_column'] => $this->activeSections(
                (int) $dataContainer->activeRecord->pid
            ),
            $GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_page']   => $this->pageArticles(
                (int) $dataContainer->id
            )
        ];
    }

    public function generateCssId($value, $dataContainer): array
    {
        $value = StringUtil::deserialize($value, true);

        if (!$dataContainer
            || !$dataContainer->activeRecord
            || !$dataContainer->activeRecord->hofff_toc_include
            || $value[0]
        ) {
            return $value;
        }

        $headline = StringUtil::deserialize($dataContainer->activeRecord->headline, true);
        if (!$headline['value']) {
            return $value;
        }

        $search  = ['/[^0-9A-z \.\&\/_-]+/u', '/[ \.\&\/-]+/'];
        $replace = ['', '-'];

        $cssId = $headline['value'];
        $cssId = html_entity_decode($cssId, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
        $cssId = StringUtil::stripInsertTags($cssId);
        $cssId = preg_replace($search, $replace, $cssId);

        if (is_numeric($cssId[0])) {
            $cssId = 'id-' . $cssId;
        }

        $value[0] = Utf8::strtolower(trim($cssId, '-'));

        return $value;
    }

    private function activeSections(int $articleId): array
    {
        // Show only active sections
        if ($articleId) {
            $sections = [];
            $page     = ($this->articlePageQuery)($articleId);

            if ($page) {
                $page->loadDetails();

                // Get the layout sections
                foreach (['layout', 'mobileLayout'] as $key) {
                    if (!$page->$key) {
                        continue;
                    }

                    $layout = LayoutModel::findByPk($page->$key);

                    if ($layout === null) {
                        continue;
                    }

                    $modules = StringUtil::deserialize($layout->modules);
                    if (empty($modules) || !\is_array($modules)) {
                        continue;
                    }

                    // Find all sections with an article module (see #6094)
                    foreach ($modules as $module) {
                        if ($module['mod'] == 0 && $module['enable']) {
                            $sections[] = $module['col'];
                        }
                    }
                }
            }
        } else {
            // Show all sections (e.g. "override all" mode)

            $sections = ['header', 'left', 'right', 'main', 'footer'];
            $statement   = $this->connection->executeQuery('SELECT sections FROM tl_layout WHERE sections!=\'\'');

            while ($layout = $statement->fetch(PDO::FETCH_OBJ)) {
                $arrCustom = StringUtil::deserialize($layout->sections);

                // Add the custom layout sections
                if (!empty($arrCustom) && \is_array($arrCustom)) {
                    foreach ($arrCustom as $v) {
                        if (!empty($v['id'])) {
                            $sections[] = $v['id'];
                        }
                    }
                }
            }
        }

        return Backend::convertLayoutSectionIdsToAssociativeArray($sections);
    }

    private function pageArticles(int $contentId): array
    {
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

        $statement->bindValue('id', $contentId);
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $articles[$row->id] = sprintf(
                '%s [%s]',
                $row->title,
                $GLOBALS['TL_LANG']['COLS'][$row->inColumn] ?? $row->inColumn
            );
        }

        return $articles;
    }
}
