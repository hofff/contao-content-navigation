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
 * Class ArticleNavigation
 *
 * 
 * @copyright  InfinityLabs - Olck & Lins GbR - 2009-2010
 * @author     Tristan Lins <tristan.lins@infinitylabs.de>
 * @package    ContentNavigation
 */
class ArticleNavigation extends Frontend {
	
	/**
	 * Collect the headings from content elements.
	 * 
	 * @param Database_Result $objCte
	 * The database result of content elements.
	 * @param integer $currentLevel
	 * The current heading level.
	 * @param boolean $skip
	 * If skip is true, the current row of $objCte will be reused, instead of going to the next row.
	 * @return mixed
	 * An structured array of the headings.
	 */
	protected function collect(Database_Result $objCte, $currentLevel = 1, $skip = false) {
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
	
	/**
	 * Collect the headings from one article.
	 * 
	 * @param mixed $article
	 * The article id or alias.
	 * @return mixed
	 * An structured array of the headings.
	 */
	public function fromArticle($article) {
		return $this->collect(
			$this->Database->prepare("SELECT * FROM tl_content WHERE (pid=? OR alias=?)" . (!BE_USER_LOGGED_IN ? " AND invisible=''" : "") . " ORDER BY sorting")
						   ->execute($articleID, $article));
	}
	
	/**
	 * Collect the headings from some articles.
	 * 
	 * @param mixed $articles
	 * Array of ids or aliases of articles.
	 * @return mixed
	 * An structured array of the headings.
	 */
	public function fromArticles($articles) {
		if (!count($articles))
			return array();
		$where = array();
		$args = array();
		foreach ($articles as $id) {
			$where[] = 'a.id = ? OR a.alias = ?';
			$args[] = $id; # for id
			$args[] = $id; # for alias
		}
		if (!count($where))
			return array();
		return $this->collect(
			$this->Database->prepare("SELECT c.* FROM tl_content c INNER JOIN tl_article a ON a.id = c.pid WHERE (" . implode(" OR ", $where) . ")" . (!BE_USER_LOGGED_IN ? " AND c.invisible=''" : "") . " ORDER BY a.sorting,c.sorting")
						   ->execute($args));
	}
	
	/**
	 * Collect the headings from a column.
	 * 
	 * @param integer $pageID
	 * The id of the page.
	 * @param string $column
	 * The name of the column.
	 * @return mixed
	 * An structured array of the headings.
	 */
	public function fromColumn($pageID, $column = 'main') {
		return $this->collect(
			$this->Database->prepare("SELECT c.* FROM tl_content c INNER JOIN tl_article a ON a.id = c.pid WHERE a.pid = ? AND a.inColumn = ? ORDER BY a.sorting,c.sorting")
						   ->execute($pageID, $column));
	}
	
}
?>