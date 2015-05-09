<div class="field<?php if ( isset($column_data['form']['validation']['required']) && ($column_data['form']['validation']['required'] === ALWAYS_REQUIRED || $item_to_edit === NULL && $column_data['form']['validation']['required'] === REQUIRED_ON_ADD) ): ?> required<?php endif ?>">
	<label><?= $column_data['title'] ?></label>

	<?php foreach ( $options as $option_index => $option ): ?>
		<div class="ui checkbox">
			<input type="checkbox" name="<?= $column_data['form']['name'] ?>[]" id="<?= $column_id ?>_<?= $option->$column_data['id_column'] ?>" value="<?= $option->$column_data['id_column'] ?>"<?php if ( $item_to_edit !== NULL && in_array($option->$column_data['id_column'], $selected_options) ): ?> checked<?php endif ?>>
			<label><?= e($option->$column_data['title_column']) ?></label>
		</div>

		<?php if ( $option_index < ($num_options - 1) ): ?>
			<div class="checkbox-line-break"></div>
		<?php endif ?>
	<?php endforeach ?>
</div>