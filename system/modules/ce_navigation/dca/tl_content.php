<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * [ce_navigation] Content Navigation Module
 * Copyright (C) 2010,2011 Tristan Lins
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2010,2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    ContentNavigation
 * @license    LGPL
 * @filesource
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
