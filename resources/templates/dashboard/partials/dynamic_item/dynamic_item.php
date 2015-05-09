<?php
$save_button = '<button type="submit" id="dynamic_item_save_button" data-loading_text="' . ($item_to_edit !== NULL ? 'Saving...' : 'Adding...') . '"' . ($item_to_edit === NULL ? ' data-saved_text="Added! Redirecting..."' : '') . ' class="ui submit button">' . ($item_to_edit !== NULL ? 'Save' : 'Add') . '</button>';
?>

<h1><?php if ( $item_to_edit !== NULL ): ?><?= e($item_to_edit->$title_column) ?><?php else: ?>Add <?= ucfirst($identifier['singular']) ?><?php endif ?></h1>

<form action="<?= URL::current() ?>" method="post" id="dynamic_item_form" enctype="multipart/form-data" class="ui form segment">
	<input type="hidden" name="_token" value="<?= $csrf_token ?>">

	<div id="dynamic_item_form_loader" class="ui active inverted dimmer">
    	<div class="ui text loader"><?= ($item_to_edit !== NULL ? 'Saving ' . $identifier['singular'] . '...' : 'Adding ' . $identifier['singular'] . '...') ?></div>
  	</div>

	<?php if ( isset($tabs) ): ?>
		<div id="dynamic_tabs_container">
			<div id="dynamic_item_tabs" class="ui top attached tabular menu">
				<?php foreach ( $tabs as $tab_id => $tab_data ): ?>
					<?php
					$tab_locked = ($item_to_edit === NULL && $tab_data['only_edit'] === TRUE);
					?>

					<a href="javascript:" data-tab="<?= $tab_id ?>" class="item<?php if ( $tab_id === 'general' ): ?> active<?php endif ?><?php if ( $tab_locked === TRUE ): ?> popup locked<?php endif ?>"<?php if ( $tab_locked === TRUE ): ?> data-content="Save <?= $identifier['singular'] ?> before using this tab" data-variation="inverted"<?php endif ?>><?= $tab_data['text'] ?></a>
				<?php endforeach ?>
			</div>

			<?php foreach ( $tabs as $tab_id => $tab_data ): ?>
				<?php
				$tab_locked = ($item_to_edit === NULL && $tab_data['only_edit'] === TRUE);
				?>

				<div data-tab="<?= $tab_id ?>" class="ui bottom attached tab segment<?php if ( $tab_id === 'general' ): ?> active<?php endif ?><?php if ( $tab_locked === TRUE ): ?> locked<?php endif ?>">
					<?php if ( $tab_locked ): ?>
						<div class="locked-tab-info">
							<span>Save <?= $identifier['singular'] ?> before using this section.</span>
						</div>

						<div class="locked-tab-content">
							<?= $tab_data['html'] ?>
						</div>
					<?php else: ?>
						<?= $tab_data['html'] ?>
					<?php endif ?>

					<?php if ( isset($tab_data['active_toggle']) && $tab_data['active_toggle'] === TRUE ): ?>
						<?= $active_toggle ?>
					<?php endif ?>

					<?php if ( isset($tab_data['save_button']) && $tab_data['save_button'] === TRUE ): ?>
						<?= $save_button ?>
					<?php endif ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php else: ?>
		<?= $form_html ?>
	<?php endif ?>

	<?php if ( !isset($tabs) ): ?>
		<?php if ( isset($active_toggle) ): ?>
			<?= $active_toggle ?>
		<?php endif ?>

		<?= $save_button ?>
	<?php endif ?>
</form>