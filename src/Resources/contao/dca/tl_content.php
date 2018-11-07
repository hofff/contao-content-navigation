<?php

declare(strict_types=1);

/**
 * Contao Table of Contents
 *
 * @copyright 2010-2011 InfinitySoft
 * @copyright 2018 nickname . Büro für visuelle Kommunikation Nicky Hoff
 * Copyright (C) 2010,2011 Tristan Lins
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['navigation'] = '{type_legend},type,headline,navigation_article,navigation_min_level,navigation_max_level;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['navigation_article'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['navigation_article'],
	'default'                 => 'main',
	'inputType'               => 'select',
	'options_callback'        => array('tl_content_navigation', 'getArticles'),
	'eval'                    => array('mandatory'=>true)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['navigation_min_level'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['navigation_min_level'],
	'default'                 => '1',
	'inputType'               => 'select',
	'options'                 => array('1', '2', '3', '4', '5', '6'),
	'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['navigation_max_level'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['navigation_max_level'],
	'default'                 => '6',
	'inputType'               => 'select',
	'options'                 => array('1', '2', '3', '4', '5', '6'),
	'eval'                    => array('tl_class'=>'w50')
);

/**
 * Class tl_content_navigation
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Tristan Lins 2010,2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    ContentNavigation
 */
class tl_content_navigation extends Backend
{
	
	/**
	 * Return all content elements as array
	 * @return array
	 */
	public function getArticles(DataContainer $dc)
	{
		$this->loadLanguageFile('tl_article');
		
		$arrColumns = array();
		foreach (array('header', 'left', 'main', 'right', 'footer') as $strColumn) {
			$arrColumns[$strColumn] = $GLOBALS['TL_LANG']['tl_article'][$strColumn];
		}
		
		$arrArticles = array();
		$objArticles = $this->Database->prepare('
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
				c.id = ?
			ORDER BY
				a.inColumn,
				a.sorting')
			->execute($dc->id);
		while ($objArticles->next()) {
			if (isset($GLOBALS['TL_LANG']['tl_article'][$objArticles->inColumn]))
				$strColumn = $GLOBALS['TL_LANG']['tl_article'][$objArticles->inColumn];
			else
				$strColumn = $objArticles->inColumn;
			$arrArticles[$objArticles->id] = sprintf('%s (%s)', $objArticles->title, $strColumn);
		}
		
		return array(
			$GLOBALS['TL_LANG']['tl_content']['navigation_article_column'] => $arrColumns,
			$GLOBALS['TL_LANG']['tl_content']['navigation_article_page'] => $arrArticles
		);
	}
	
}
