<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2009-2010 Leo Feyer
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
 * @copyright  InfinityLabs - Olck & Lins GbR - 2009-2010
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
 * @package    ContentNavigation
 * @license    LGPL 
 * @filesource
 */


/**
 * Class ContentNavigation
 *
 * 
 * @copyright  InfinityLabs - Olck & Lins GbR - 2009-2010
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
 * @package    ContentNavigation
 */
class ContentNavigation extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_ce_navigation';
	
	protected function flatten(&$items, $level = 1) {
		if (!count($items))
			return '';
		foreach ($items as &$item) {
			if (isset($item['subitems'])) {
				
				$item['subitems'] = $this->flatten($item['subitems'], $level + 1);
			}
			$item['class'] = '';
		}
		
		$items[0]['class'] = 'first';
		$items[count($items)-1]['class'] = 'last';
		
		$tpl = new FrontendTemplate('ce_navigation');
		$tpl->items = $items;
		$tpl->level = $level;
		return $tpl->parse();
	}
	
	protected function compile()
	{
		global $objPage;
		
		$an = new ArticleNavigation();
		if (is_numeric($this->navigationArticle)) {
			$items = $an->fromArticle($this->navigationArticle);
		} else {
			$items = $an->fromColumn($objPage->id, $this->navigationArticle);
		}
		
		$this->Template->items = $this->flatten($items);
	}
	
}

?>