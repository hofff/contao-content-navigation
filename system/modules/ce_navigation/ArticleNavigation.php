<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Tristan Lins 2010
 * @author     Tristan Lins <info@infinitysoft.de>
 * @package    ContentNavigation
 * @license    LGPL
 * @filesource
 */


/**
 * Class ArticleNavigation
 *
 * 
 * @copyright  Tristan Lins 2010
 * @author     Tristan Lins <info@infinitysoft.de>
 * @package    ContentNavigation
 */
class ArticleNavigation extends Frontend {
	
	/**
	 * Collect the headings from content elements.
	 * 
	 * @param Database_Result $objCte
	 * The database result of content elements.
	 * @param integer $intCurrentLevel
	 * The current heading level.
	 * @param boolean $boolSkip
	 * If skip is true, the current row of $objCte will be reused, instead of going to the next row.
	 * @return mixed
	 * An structured array of the headings.
	 */
	protected function collect(Database_Result $objCte, $intCurrentLevel = 1, $boolSkip = false) {
		$arrItems = array();
		$objPage = false;
		$intArticleId = 0;
		while ($boolSkip || $objCte->next())
		{
			if ($intArticleId != $objCte->pid) {
				$objPage = $this->Database->prepare("SELECT p.* FROM tl_page p INNER JOIN tl_article a ON a.pid = p.id WHERE a.id = ?")
										  ->execute($intArticleId = $objCte->pid);
				if (!$objPage->next()) {
					$objPage = false;
					continue;
				}
			} else if ($intArticleId == $objCte->pid && $objPage == false) {
				continue;
			}
			$strHeadline = unserialize($objCte->headline);
			$strCssId = unserialize($objCte->cssID);
			if (!empty($strHeadline['value']) && !empty($strCssId[0])) {
				$intLevel = intval(substr($strHeadline['unit'], 1));
				if ($intLevel > $intCurrentLevel) {
					$arrSubitems = $this->collect($objCte, $intCurrentLevel + 1, true);
					$boolSkip = count($arrSubitems) > 0;
					$arrItems[count($arrItems)-1]['subitems'] = $arrSubitems;
				} else if ($intLevel < $intCurrentLevel) {
					break;
				} else {
					$arrItem = array_merge($objCte->row(),
						array(
							'title' => $strHeadline['value'],
							'href' => $this->generateFrontendUrl($objPage->row()) .'#'. $strCssId[0]
						));
					$arrItems[] = $arrItem;
					$boolSkip = false;
				}
			}
		}
		return $arrItems;
	}
	
	/**
	 * Collect the headings from one article.
	 * 
	 * @param int $intArticleId
	 * The article id.
	 * @return mixed
	 * An structured array of the headings.
	 */
	public function fromArticle($intArticleId) {
		return $this->collect(
			$this->Database->prepare("SELECT * FROM tl_content WHERE pid=?" . (!BE_USER_LOGGED_IN ? " AND invisible=''" : "") . " ORDER BY sorting")
						   ->execute($intArticleId));
	}
	
	/**
	 * Collect the headings from some articles.
	 * 
	 * @param mixed $intArticleId
	 * Array of article id's.
	 * @return mixed
	 * An structured array of the headings.
	 */
	public function fromArticles($intArticleIds) {
		if (!count($intArticleIds))
			return array();
		$arrWhere = array();
		$arrArgs = array();
		foreach ($intArticleIds as $intId) {
			$arrWhere[] = 'a.id = ?';
			$arrArgs[] = $intId;
		}
		if (!count($arrWhere))
			return array();
		return $this->collect(
			$this->Database->prepare("SELECT c.* FROM tl_content c INNER JOIN tl_article a ON a.id = c.pid WHERE (" . implode(" OR ", $arrWhere) . ")" . (!BE_USER_LOGGED_IN ? " AND c.invisible=''" : "") . " ORDER BY a.sorting,c.sorting")
						   ->execute($arrArgs));
	}
	
	/**
	 * Collect the headings from a column.
	 * 
	 * @param integer $intPageId
	 * The id of the page.
	 * @param string $strColumn
	 * The name of the column.
	 * @return mixed
	 * An structured array of the headings.
	 */
	public function fromColumn($intPageId, $strColumn = 'main') {
		return $this->collect(
			$this->Database->prepare("SELECT c.* FROM tl_content c INNER JOIN tl_article a ON a.id = c.pid WHERE a.pid = ? AND a.inColumn = ? ORDER BY a.sorting,c.sorting")
						   ->execute($intPageId, $strColumn));
	}
	
}
?>