<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package Ce_navigation
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'InfinitySoft',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'InfinitySoft\CeNavigation\ArticleNavigation' => 'system/modules/CeNavigation/ArticleNavigation.php',
	'InfinitySoft\CeNavigation\ContentNavigation' => 'system/modules/CeNavigation/ContentNavigation.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_navigation'     => 'system/modules/CeNavigation/templates',
	'mod_ce_navigation' => 'system/modules/CeNavigation/templates',
));
