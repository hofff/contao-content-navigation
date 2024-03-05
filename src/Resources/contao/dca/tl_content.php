<?php

declare(strict_types=1);

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_content_navigation'] = '{type_legend},type,headline'
    . ';{toc_legend},hofff_toc_source,hofff_toc_min_level,hofff_toc_max_level,hofff_toc_force_request_uri'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID,space'
    . ';{invisible_legend:hide},invisible,start,stop';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_source'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_source'],
    'default'          => 'main',
    'exclude'          => true,
    'inputType'        => 'select',
    'eval'             => [
        'mandatory' => false,
        'chosen' => true,
        'includeBlankOption' => true,
        'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_source_parent'],
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
    'sql'       => 'int(1) UNSIGNED NOT NULL default 6',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_include'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_include'],
    'inputType' => 'checkbox',
    'exclude'   => true,
    'filter'    => true,
    'eval'      => ['tl_class' => 'clr w50'],
    'sql'       => 'char(1) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_toc_force_request_uri'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_toc_force_request_uri'],
    'inputType' => 'checkbox',
    'exclude'   => true,
    'filter'    => true,
    'eval'      => ['tl_class' => 'clr w50'],
    'sql'       => 'char(1) NOT NULL default \'\'',
];
