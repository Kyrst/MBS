<div class="field<?php if ( isset($column_data['form']['validation']['required']) && ($column_data['form']['validation']['required'] === ALWAYS_REQUIRED || $item_to_edit === NULL && $column_data['form']['validation']['required'] === REQUIRED_ON_ADD) ): ?> required<?php endif ?>">
	<label for="<?= $column_id ?>"><?= $column_data['title'] ?></label>
	<input type="text" name="<?= $column_data['form']['name'] ?>" id="<?= $column_id ?>"<?php if ( $item_to_edit !== NULL ): ?> value="<?= e($item_to_edit->$column_data['column']) ?>"<?php endif ?>>
</div>