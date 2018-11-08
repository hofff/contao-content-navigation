<?php

declare(strict_types=1);

/**
 * Contao Table of Contents
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname . Büro für visuelle Kommunikation Nicky Hoff
 */

use Hofff\Contao\TableOfContents\EventListener\Dca\ContentDcaListener;

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_toc'] = '{type_legend},type,headline'
    . ';{navigation_legend},navigation_article,navigation_min_level,navigation_max_level'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID,space';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['navigation_article'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['navigation_article'],
    'default'          => 'main',
    'inputType'        => 'select',
    'options_callback' => [ContentDcaListener::class, 'articleOptions'],
    'eval'             => ['mandatory' => false, 'includeBlankOption' => true],
    'sql'              => 'varchar(32) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['navigation_min_level'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['navigation_min_level'],
    'default'   => '1',
    'inputType' => 'select',
    'options'   => ['1', '2', '3', '4', '5', '6'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => 'int(1) UNSIGNED NOT NULL default 1',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['navigation_max_level'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['navigation_max_level'],
    'default'   => '6',
    'inputType' => 'select',
    'options'   => ['1', '2', '3', '4', '5', '6'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => 'int(1) UNSIGNED NOT NULL default 2',
];
