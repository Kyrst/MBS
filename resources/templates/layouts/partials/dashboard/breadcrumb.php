<?php if ( $num_breadcrumb_items > 0 ): ?>
	<div id="breadcrumb" class="ui breadcrumb">
		<?php foreach ( $breadcrumb_items as $index => $breadcrumb_item ): ?>
			<?php if ( !empty($breadcrumb_item['link']) ): ?><a href="<?= $breadcrumb_item['link'] ?>"<?php else: ?><div<?php endif ?> class="section"><?= $breadcrumb_item['text'] ?><?php if ( !empty($breadcrumb_item['link']) ): ?></a><?php else: ?></div><?php endif ?>

			<?php if ( $index < ($num_breadcrumb_items - 1) ): ?>
				<div class="divider"> / </div>
			<?php endif ?>
		<?php endforeach ?>
	</div>
<?php endif ?>