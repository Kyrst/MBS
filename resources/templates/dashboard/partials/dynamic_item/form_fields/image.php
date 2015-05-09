<div data-id="<?= $column_id ?>" class="field<?php if ( isset($column_data['form']['validation']['required']) && ($column_data['form']['validation']['required'] === ALWAYS_REQUIRED || $item_to_edit === NULL && $column_data['form']['validation']['required'] === REQUIRED_ON_ADD) ): ?> required<?php endif ?>">
	<label for="<?= $column_id ?>"><?= $column_data['title'] ?></label>

	<?php if ( $item_to_edit !== NULL &&  $item_to_edit->haveDynamicImage($column_id) ): ?>
		<?php $image = $item_to_edit->getDynamicImage($column_id) ?>

		<div class="ui card image-card small">
			<input type="file" name="<?= $column_data['form']['name'] ?>" id="<?= $column_id ?>" class="image-card-file-input">

			<div class="image with-padding">
				<img src="<?= $item_to_edit->getDynamicImageURL($column_id, $params['size']) ?>" alt="">
			</div>

			<?php if ( $image['processing'] === TRUE ): ?>
				<div class="content description-content">
					<div class="meta">
						<span class="date">Processing...</span>
					</div>
				</div>
			<?php endif ?>

			<div class="extra content">
				<span class="image-card-status-text"></span>

				<div class="ui buttons mini">
					<a href="javascript:" class="ui button mini attached left image-card-replace-button">Replace</a>
					<a href="javascript:" class="ui button mini attached image-card-delete-button">Delete</a>
					<a href="<?= $image['original_url'] ?>" class="ui button mini attached right colorbox">Original</a>
				</div>
			</div>
		</div>
	<?php else: ?>
		<input type="file" name="<?= $column_data['form']['name'] ?>" id="<?= $column_id ?>">
	<?php endif ?>
</div>