<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\StringUtil;
use Contao\Template;
use Hofff\Contao\ContentNavigation\Navigation\ContentNavigationBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;
use function is_numeric;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[AsContentElement('hofff_content_navigation', 'links', 'ce_hofff_content_navigation')]
final class ContentNavigationElement extends AbstractContentElementController
{
    public function __construct(
        private readonly ContentNavigationBuilder $navigationBuilder,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if ($this->container->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $this->getBackendView($model, $template);
        }

        if ($model->hofff_toc_source === '') {
            $items = $this->navigationBuilder->fromParent(
                $model->ptable,
                (int) $model->pid,
                (int) $model->hofff_toc_min_level,
                (int) $model->hofff_toc_max_level,
                (bool) $model->hofff_toc_force_request_uri,
            );
        } elseif (is_numeric($model->hofff_toc_source)) {
            $items = $model->navigationBuilder->fromArticle(
                (int) $model->hofff_toc_source,
                (int) $model->hofff_toc_min_level,
                (int) $model->hofff_toc_max_level,
                (bool) $model->hofff_toc_force_request_uri,
            );
        } else {
            $items = $this->navigationBuilder->fromColumn(
                (int) $GLOBALS['objPage']->id,
                $model->hofff_toc_source,
                (int) $model->hofff_toc_min_level,
                (int) $model->hofff_toc_max_level,
                (bool) $model->hofff_toc_force_request_uri,
            );
        }

        $template->items          = $this->parseItems($items);
        $template->request        = Environment::get('indexFreeRequest');
        $template->skipId         = 'skipNavigation' . $model->id;
        $template->skipNavigation = StringUtil::specialchars(
            $this->translator->trans('MSC.skipNavigation', [], 'contao_default'),
        );

        return $template->getResponse();
    }

    /** @param list<array<string,mixed>> $items */
    private function parseItems(array $items, int $level = 1): string
    {
        if (! count($items)) {
            return '';
        }

        foreach ($items as &$item) {
            if (isset($item['subitems'])) {
                $item['subitems'] = $this->parseItems($item['subitems'], $level + 1);
            }

            $item['class'] = '';
        }

        $items[0]['class']                 = 'first';
        $items[count($items) - 1]['class'] = 'last';

        $tpl = new FrontendTemplate('hofff_content_nav_default');
        $tpl->setData(['items' => $items, 'level' => $level]);

        return $tpl->parse();
    }

    private function getBackendView(ContentModel $model, Template $frontendTemplate): Response
    {
        $template           = new BackendTemplate('be_wildcard');
        $template->wildcard = '### '
            . $this->translator->trans('CTE.' . $model->type . '.1', [], 'contao_tl_content')
            . ' ###';
        $template->title    = $frontendTemplate->headline;
        $template->id       = $model->id;
        $template->link     = $this->translator->trans('CTE.' . $model->type . '.0', [], 'contao_tl_content');
        $template->href     = $this->router->generate(
            'contao_backend',
            [
                'do'    => Input::get('do'),
                'table' => 'tl_content',
                'act'   => 'edit',
                'id'    => $model->id,
                'rt'    => $this->csrfTokenManager->getDefaultTokenValue(),
            ],
        );

        return $template->getResponse();
    }
}
