<?php

declare(strict_types=1);

/**
 * Contao Table of Contents
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname . Büro für visuelle Kommunikation Nicky Hoff
 */

use Hofff\Contao\TableOfContents\EventListener\Dca\ContentDcaListener;

/*
 * Config
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = [ContentDcaListener::class, 'adjustPalettes'];

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_toc'] = '{type_legend},type,headline'
    . ';{toc_legend},hofff_toc_source,hofff_toc_min_level,hofff_toc_max_level'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID,space';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_source'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_source'],
    'default'          => 'main',
    'inputType'        => 'select',
    'options_callback' => [ContentDcaListener::class, 'sourceOptions'],
    'eval'             => [
        'mandatory' => false,
        'chosen' => true,
        'includeBlankOption' => true,
        'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_parent']
    ],
    'sql'              => 'varchar(32) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_min_level'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_min_level'],
    'inputType' => 'select',
    'default'   => '1',
    'exclude'   => true,
    'options'   => ['1', '2', '3', '4', '5', '6'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => 'int(1) UNSIGNED NOT NULL default 1',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_max_level'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_max_level'],
    'inputType' => 'select',
    'default'   => '6',
    'exclude'   => true,
    'options'   => ['1', '2', '3', '4', '5', '6'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => 'int(1) UNSIGNED NOT NULL default 2',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_exclude'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_exclude'],
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'clr w50'],
    'sql'       => 'char(1) NOT NULL default \'\'',
];
