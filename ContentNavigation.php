<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

class ContentNavigation extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_navigation';
	
	protected function compile()
	{
		global $objPage;
		
		$an = new ArticleNavigation();
		if (is_numeric($this->navigationArticle)) {
			$items = $an->fromArticle($this->navigationArticle);
		} else {
			$items = $an->fromColumn($objPage->id, $this->navigationArticle);
		}
		
		$this->Template->items = $items;
		$this->Template->level = 1;
	}
	
}

?>