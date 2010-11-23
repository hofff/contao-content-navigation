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
 * @copyright  InfinityLabs - Olck & Lins GbR - 2009-2010
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
 * @package    ContentNavigation
 * @license    LGPL 
 * @filesource
 */


/**
 * Class ArticleNavigation
 *
 * 
 * @copyright  InfinityLabs - Olck & Lins GbR - 2009-2010
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
 * @package    ContentNavigation
 */
class ArticleNavigation extends Frontend {
	
	protected function collect($objCte, $currentLevel = 1, $skip = false) {
		$items = array();
		$objPage = false;
		$articleID = 0;
		while ($skip || $objCte->next())
		{
			if ($articleID != $objCte->pid) {
				$objPage = $this->Database->prepare("SELECT p.* FROM tl_page p INNER JOIN tl_article a ON a.pid = p.id WHERE a.id = ?")
										  ->execute($articleID = $objCte->pid);
				if (!$objPage->next()) {
					$objPage = false;
					continue;
				}
			} else if ($articleID == $objCte->pid && $objPage == false) {
				continue;
			}
			$headline = unserialize($objCte->headline);
			$cssID = unserialize($objCte->cssID);
			if (!empty($headline['value']) && !empty($cssID[0])) {
				$level = intval(substr($headline['unit'], 1));
				if ($level > $currentLevel) {
					$tmp = $this->collect(&$objCte, $currentLevel + 1, true);
					$skip = count($tmp) > 0;
					$items[count($items)-1]['subitems'] = $tmp;
				} else if ($level < $currentLevel) {
					break;
				} else {
					$item = array_merge($objCte->row(),
						array(
							'title' => $headline['value'],
							'href' => $this->generateFrontendUrl($objPage->row()) .'#'. $cssID[0]
						));
					$items[] = $item;
					$skip = false;
				}
			}
		}
		return $items;
	}
	
	public function fromArticle($articleID) {
		return $this->collect(
			$this->Database->prepare("SELECT * FROM tl_content WHERE pid=?" . (!BE_USER_LOGGED_IN ? " AND invisible=''" : "") . " ORDER BY sorting")
						   ->execute($articleID));
	}
	
	public function fromArticles($articleIDs) {
		if (!count($articleIDs))
			return array();
		$ids = '';
		foreach ($articleIDs as $id) {
			if ($ids) $ids .= ',';
			$ids .= intval($id);
		}
		return $this->collect(
			$this->Database->execute("SELECT c.* FROM tl_content c INNER JOIN tl_article a ON a.id = c.pid WHERE c.pid IN ($ids)" . (!BE_USER_LOGGED_IN ? " AND c.invisible=''" : "") . " ORDER BY a.sorting,c.sorting"));
	}
	
	public function fromColumn($pageID, $column = 'main') {
		return $this->collect(
			$this->Database->prepare("SELECT c.* FROM tl_content c INNER JOIN tl_article a ON a.id = c.pid WHERE a.pid = ? AND a.inColumn = ? ORDER BY a.sorting,c.sorting")
						   ->execute($pageID, $column));
	}
	
}
?>