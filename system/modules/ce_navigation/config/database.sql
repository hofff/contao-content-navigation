-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `navigationArticle` varchar(10) NOT NULL default '',
  `navigationMinLevel` varchar(10) NOT NULL default '0',
  `navigationMaxLevel` varchar(10) NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
