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
 * Content elements
 */
$GLOBALS['TL_CTE']['links']['navigation'] = 'ContentNavigation';

/**
 * runonce job
 */
try {
	$strExecutionLockFile = 'system/modules/ce_navigation/config/runonce-1.0.6_stable.lock';
	if (!file_exists(TL_ROOT . '/' . $strExecutionLockFile))
	{
		# load the runonce class
		require_once(TL_ROOT . '/system/modules/ce_navigation/ContentNavigationRunonceJob.php');
		# execute the runonce update job
		ContentNavigationRunonceJob::getInstance()->run("1.0.6 stable");
		# lock the update
		$objLock = new File($strExecutionLockFile);
		$objLock->write('1');
	}
} catch(Exception $e) {}

?>