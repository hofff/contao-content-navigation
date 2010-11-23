<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
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
 * @copyright  InfinitySoft 2010
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    ContentNavigation
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Class ContentNavigationRunonceJob
 * 
 * runonce update job
 * @copyright  InfinitySoft 2010
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    ContentNavigation
 */
class ContentNavigationRunonceJob extends Backend
{
	private static $instance = null;
	
	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new ContentNavigationRunonceJob();
		}
		return self::$instance;
	}
	
	protected function __construct()
	{
		$this->import('Database');
	}
	
	public function run($strTargetVersion)
	{
		switch ($strTargetVersion)
		{
		// update from version prior 1.0.6 stable
		case "1.0.6 stable":
			/*
			 * rename the database columns
			 *   navigationArticle  -> navigation_article
			 *   navigationMinLevel -> navigation_min_level
			 *   navigationMaxLevel -> navigation_max_level
			 */
			$objColumns = $this->Database->execute("SHOW COLUMNS FROM `tl_content` LIKE 'navigation%'");
			while ($objColumns->next()) {
				switch ($objColumns->Field) {
				case 'navigationArticle':
					$this->Database->execute("ALTER TABLE `tl_content` CHANGE `navigationArticle` `navigation_article` varchar(10) NOT NULL default ''");
					break;
	
				case 'navigationMinLevel':
					$this->Database->execute("ALTER TABLE `tl_content` CHANGE `navigationMinLevel` `navigation_min_level` varchar(10) NOT NULL default '0'");
					break;
	
				case 'navigationMaxLevel':
					$this->Database->execute("ALTER TABLE `tl_content` CHANGE `navigationMaxLevel` `navigation_max_level` varchar(10) NOT NULL default '0'");
					break;
				}
			}
			break;
		}
	}
}

?>