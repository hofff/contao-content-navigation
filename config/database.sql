-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_page`
-- 

-- CREATE TABLE `tl_page` (
--   `navigation_image` blob NULL,
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `navigationArticle` varchar(10) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
