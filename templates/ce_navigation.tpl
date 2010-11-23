<?php if ($this->level == 1): ?><div class="ce_navigation block<?php if ($this->class): ?> <?php echo $this->class; ?><?php endif; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>><?php endif; ?>
<?php if (count($this->items)): ?>

<?php if ($this->level == 1 && $this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<ul class="level_<?php echo $this->level ?>">
	<?php $this->n = 0; foreach ($this->items as $item): ?>
	<?php if (isset($item['title']) && $item['href']): ?>
	<?php if ($this->n > 0): ?></li><?php endif; ?>
	<li class="level_<?php echo $this->level ?><?php if ($this->n == 0): ?> first<?php endif; ?>"><a href="<?php echo $item['href'] ?>"><?php echo $item['title'] ?></a>
	<?php else:
		if ($this->n == 0): ?><li><?php endif;
		$tpl = new FrontendTemplate('ce_navigation');
		$tpl->items = $item;
		$tpl->level = $this->level + 1;
		echo $tpl->parse();
		if ($this->n == 0): ?></li><?php endif;
	endif; $this->n ++; endforeach; ?>
	</li>
</ul>
<?php endif; ?>
<?php if ($this->level == 1): ?></div><?php endif; ?>