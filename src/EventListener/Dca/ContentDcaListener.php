<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\EventListener\Dca;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Backend;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Contao\LayoutModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContentNavigation\Navigation\Query\ArticlePageQuery;
use Symfony\Component\String\UnicodeString;

use function html_entity_decode;
use function is_array;
use function is_numeric;
use function sprintf;
use function trim;

use const ENT_QUOTES;

final class ContentDcaListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ArticlePageQuery $articlePageQuery,
        private readonly SlugGenerator $cssIdGenerator,
    ) {
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    #[AsCallback('tl_content', 'config.onload')]
    public function adjustPalettes(DataContainer|null $dataContainer = null): void
    {
        if (
            ! isset($GLOBALS['TL_DCA']['tl_content']['palettes'])
            || ! is_array($GLOBALS['TL_DCA']['tl_content']['palettes'])
        ) {
            return;
        }

        $manipulator = PaletteManipulator::create()
            ->addField('hofff_toc_include', 'cssID', PaletteManipulator::POSITION_BEFORE);

        // The short title is added to the palette instead of being registered as a subpalette, because
        // Contao renders subpalettes in a container of their own, which prevents the field from being
        // displayed next to the checkbox.
        if ($this->isIncludedInNavigation($dataContainer)) {
            $manipulator->addField('hofff_toc_title', 'hofff_toc_include', PaletteManipulator::POSITION_AFTER);
        }

        foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $name => $config) {
            if (is_array($config)) {
                continue;
            }

            $manipulator->applyToPalette($name, 'tl_content');
        }
    }

    /** Determine if the content element being edited is included in the content navigation. */
    private function isIncludedInNavigation(DataContainer|null $dataContainer): bool
    {
        if ($dataContainer === null) {
            return false;
        }

        $act = Input::get('act');

        // The palette is generated once for all records when editing multiple elements, so the field
        // has to be offered independently of the value of a single record.
        if ($act === 'editAll' || $act === 'overrideAll') {
            return true;
        }

        if ($act !== 'edit') {
            return false;
        }

        $currentRecord = $dataContainer->getCurrentRecord();

        return (bool) ($currentRecord['hofff_toc_include'] ?? false);
    }

    /**
     * Return all content elements as array.
     *
     * @return array<string, array<string|int,string>>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[AsCallback('tl_content', 'fields.hofff_toc_source.options')]
    public function sourceOptions(DataContainer $dataContainer): array
    {
        $currentRecord = $dataContainer->getCurrentRecord();

        if (
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] !== 'tl_article'
            || $currentRecord === null
        ) {
            return [];
        }

        return [
            (string) $GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_column'] => $this->activeSections(
                (int) $currentRecord['pid'],
            ),
            (string) $GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_page']   => $this->pageArticles(
                (int) $dataContainer->id,
            ),
        ];
    }

    /**
     * @return array{0:string|null, 1:string|null}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[AsCallback('tl_content', 'fields.cssID.save')]
    public function generateCssId(mixed $value, DataContainer|null $dataContainer): array
    {
        $value = StringUtil::deserialize($value, true);

        // DC_Table::getActiveRecord() is used instead of DataContainer::getCurrentRecord(), because it
        // also contains the values submitted in the running save cycle. The headline and the checkbox
        // are saved before the CSS ID, while the record itself is not updated until all save callbacks
        // have been run.
        $activeRecord = $dataContainer instanceof DC_Table ? $dataContainer->getActiveRecord() : null;

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (
            ! ($activeRecord['hofff_toc_include'] ?? null)
            || $value[0]
        ) {
            return $value;
        }

        // The CSS ID is generated from what is being displayed in the navigation, hence the short
        // title takes precedence over the headline here as well.
        $cssId = trim((string) ($activeRecord['hofff_toc_title'] ?? ''));

        if ($cssId === '') {
            $headline = StringUtil::deserialize($activeRecord['headline'] ?? null, true);
            $cssId    = trim((string) ($headline['value'] ?? ''));
        }

        if ($cssId === '') {
            return $value;
        }

        $cssId = html_entity_decode($cssId, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
        $cssId = StringUtil::stripInsertTags($cssId);
        $cssId = $this->cssIdGenerator->generate($cssId);

        if (is_numeric($cssId[0])) {
            $cssId = 'id-' . $cssId;
        }

        $value[0] = (new UnicodeString(trim($cssId, '-')))->lower()->toString();

        return $value;
    }

    /**
     * @return list<string>
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
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
                    if (! $page->$key) {
                        continue;
                    }

                    $layout = LayoutModel::findByPk($page->$key);

                    if ($layout === null) {
                        continue;
                    }

                    $this->registerSectionLabels($layout);
                    $modules = StringUtil::deserialize($layout->modules);
                    if (empty($modules) || ! is_array($modules)) {
                        continue;
                    }

                    // Find all sections with an article module (see #6094)
                    foreach ($modules as $module) {
                        if ($module['mod'] !== '0' || ! $module['enable']) {
                            continue;
                        }

                        $sections[] = $module['col'];
                    }
                }
            }
        } else {
            // Show all sections (e.g. "override all" mode)

            $sections  = ['header', 'left', 'right', 'main', 'footer'];
            $statement = $this->connection->executeQuery('SELECT sections FROM tl_layout WHERE sections!=\'\'');

            while ($layout = $statement->fetchAssociative()) {
                $layout = (object) $layout;
                $this->registerSectionLabels($layout);
                $arrCustom = StringUtil::deserialize($layout->sections);

                // Add the custom layout sections
                if (empty($arrCustom) || ! is_array($arrCustom)) {
                    continue;
                }

                foreach ($arrCustom as $v) {
                    if (empty($v['id'])) {
                        continue;
                    }

                    $sections[] = $v['id'];
                }
            }
        }

        return Backend::convertLayoutSectionIdsToAssociativeArray($sections);
    }

    /**
     * @return array<string|int,string>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
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
				a.sorting',
        );

        $statement->bindValue('id', $contentId);
        $result = $statement->executeQuery();

        while ($row = $result->fetchAssociative()) {
            $row                = (object) $row;
            $articles[$row->id] = sprintf(
                '%s [%s]',
                $row->title,
                $GLOBALS['TL_LANG']['COLS'][$row->inColumn] ?? $row->inColumn,
            );
        }

        return $articles;
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    private function registerSectionLabels(object $layout): void
    {
        foreach (StringUtil::deserialize($layout->sections, true) as $section) {
            if (isset($GLOBALS['TL_LANG']['COLS'][$section['id']])) {
                continue;
            }

            $GLOBALS['TL_LANG']['COLS'][$section['id']] = $section['title'] ?: $section['id'];
        }
    }
}
