<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  2009-2010, InfinityLabs 
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
 * @package    ContentNavigation 
 * @license    LGPL 
 * @filesource
 */


$GLOBALS['TL_DCA']['tl_content']['fields']['navigationArticle'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['navigationArticle'],
	'default'                 => 'main',
	'inputType'               => 'select',
	'options_callback'        => array('tl_content_navigation', 'getArticles'),
	'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_content']['palettes']['navigation'] = '{type_legend},type,headline,navigationArticle;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Class tl_content_navigation
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  2009-2010, InfinityLabs 
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
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
		
		$columns = array();
		foreach (array('header', 'left', 'main', 'right', 'footer') as $s) {
			$columns[$s] = $GLOBALS['TL_LANG']['tl_article'][$s];
		}
		
		$articles = array();
		$objArticles = $this->Database->prepare('SELECT a.id,a.title,a.inColumn FROM tl_article a '.
												'INNER JOIN tl_article b ON a.pid = b.pid '.
												'INNER JOIN tl_content c ON c.pid = b.id '.
												'WHERE c.id = ? ORDER BY a.inColumn,a.sorting')
									  ->execute($dc->id);
		while ($objArticles->next()) {
			if (isset($GLOBALS['TL_LANG']['tl_article'][$objArticles->inColumn]))
				$column = $GLOBALS['TL_LANG']['tl_article'][$objArticles->inColumn];
			else
				$column = $objArticles->inColumn;
			$articles[$objArticles->id] = sprintf('%s (%s)', $objArticles->title, $column);
		}
		
		return array(
			$GLOBALS['TL_LANG']['tl_content']['navigationArticleColumn'] => $columns,
			$GLOBALS['TL_LANG']['tl_content']['navigationArticlePage'] => $articles
		);
	}
	
}
?>