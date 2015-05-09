<?php if ( $num_total_items > 0 ): ?>
	<div id="dynamic_table_info_row" class="ui grid">
		<div id="dynamic_table_page_info_container" class="<?= $search_enabled ? 'eight' : 'sixteen' ?> wide column">
			<?php if ( $num_total_filtered_items > 0 ): ?>
				<p>
					<?php if ( $paging_enabled ): ?>
						Showing <?= $num_page_items ?> of <?= $num_total_items ?> <?= $identifier[($num_total_items === 1 ? 'singular' : 'plural')] ?>.
					<?php else: ?>
						Showing <?= $num_page_items ?> of <?= $num_total_items ?> <?= $identifier[($num_total_items === 1 ? 'singular' : 'plural')] ?>.
					<?php endif ?>
				</p>
			<?php else: ?>
				No <?= $identifier['plural'] ?> found.
			<?php endif ?>
		</div>

		<?php if ( $search_enabled ): ?>
			<div id="dynamic_table_search_container" class="eight wide column">
				<div class="ui category search">
					<div class="ui left icon input">
						<i class="search icon"></i>
						<input type="text" name="search" id="dynamic_table_search" placeholder="Search" class="prompt" value="<?= $filters['search_query'] ?>">
					</div>
					<div class="results"></div>
				</div>
			</div>
		<?php endif ?>
	</div>

	<?php if ( $num_total_filtered_items > 0 ): ?>
		<table id="dynamic_table" class="ui striped table">
			<thead>
				<tr>
					<?php foreach ( $table_columns as $table_column ): ?>
						<th class="<?= \App\Helpers\Core\DynamicTable::convertTableColumnSize($table_column['size']) ?> wide"><?php if ( isset($table_column['sortable']) && $table_column['sortable'] === TRUE ): ?><a href="javascript:"><?= $table_column['text'] ?></a><?php else: ?><?= $table_column['text'] ?><?php endif ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $item_index => $item ): ?>
					<tr id="dynamic_table_item_<?= $item->id ?>" data-id="<?= $item->id ?>" data-title="<?= $item->table_title ?>">
						<?php foreach ( $table_columns as $table_column_id => $table_column ): ?>
							<td>
								<?= $table_column_data[$item_index][$table_column_id]['html'] ?>
							</td>
						<?php endforeach ?>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>

		<?php if ( $paging_enabled ): ?>
			<div class="ui pagination menu">
				<a class="icon item prev<?php if ( $items->currentPage() === 1 ): ?> disabled<?php endif ?>">
					<i class="left arrow icon"></i>
				</a>

				<div class="active item">
					Page <?= $items->currentPage() ?> of <?= $items->lastPage() ?>
				</div>

				<a class="icon item next<?php if ( $items->currentPage() === $items->lastPage() ): ?> disabled<?php endif ?>">
					<i class="right arrow icon"></i>
				</a>
			</div>
		<?php endif ?>
	<?php endif ?>
<?php else: ?>
	<p>No <?= $identifier['plural'] ?>.</p>
<?php endif ?>