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
 * Class ContentNavigationRunonce
 * 
 * @copyright  InfinitySoft 2010,2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    ContentNavigation
 */
class ContentNavigationRunonce extends Controller
{
	public function __construct()
	{
		$this->import('Database');
	}

	/**
	 * Run runonce.
	 * @return void
	 */
	public function run()
	{
		$this->update_1_0_6();
	}

	/**
	 * Update from version prior 1.0.6 stable
	 * @return void
	 */
	protected function update_1_0_6()
	{
		/*
		 * rename the database columns
		 *   navigationArticle  -> navigation_article
		 *   navigationMinLevel -> navigation_min_level
		 *   navigationMaxLevel -> navigation_max_level
		 */
		if ($this->Database->tableExists('tl_content'))
		{
			if ($this->Database->fieldExists('navigationArticle', 'tl_content'))
			{
				$this->Database->execute("ALTER TABLE tl_content CHANGE navigationArticle navigation_article varchar(10) NOT NULL default ''");
			}
			if ($this->Database->fieldExists('navigationMinLevel', 'tl_content'))
			{
				$this->Database->execute("ALTER TABLE tl_content CHANGE navigationMinLevel navigation_min_level varchar(10) NOT NULL default '0'");
			}
			if ($this->Database->fieldExists('navigationMaxLevel', 'tl_content'))
			{
				$this->Database->execute("ALTER TABLE tl_content CHANGE navigationMaxLevel navigation_max_level varchar(10) NOT NULL default '0'");
			}
		}
	}
}

$objRunonce = new ContentNavigationRunonce();
$objRunonce->run();
