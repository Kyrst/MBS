<div class="field<?php if ( isset($column_data['form']['validation']['required']) && ($column_data['form']['validation']['required'] === ALWAYS_REQUIRED || $item_to_edit === NULL && $column_data['form']['validation']['required'] === REQUIRED_ON_ADD) ): ?> required<?php endif ?>">
	<label for="<?= $column_id ?>"><?= $column_data['title'] ?></label>

	<select name="<?= $column_data['form']['name'] ?>" id="<?= $column_id ?>" class="ui dropdown">
		<?php if ( $item_to_edit === NULL ): ?>
			<option value="">Choose</option>
		<?php endif ?>

		<?php foreach ( $options as $option ): ?>
			<option value="<?= $option->$column_data['id_column'] ?>"<?php if ( $item_to_edit !== NULL && $selected_option !== NULL && $item_to_edit->$column_data['column'] === $option->$column_data['id_column'] ): ?> selected<?php endif ?>><?= $option->$column_data['title_column'] ?></option>
		<?php endforeach ?>
	</select>
</div>