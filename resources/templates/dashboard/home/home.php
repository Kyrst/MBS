<div id="home_container" class="ui raised segment">
	<div id="songs_filter_container" class="ui grid">
		<div class="ui floated left eight wide column">
			<div class="ui purple header">
				<img src="http://semantic-ui.com/images/avatar/small/elliot.jpg" class="ui avatar image" alt="">

				<div id="home_header" class="content">
					<?= $user->alias ?>

					<div  id="sub_header" class="sub header">
						<?= $user->getName() ?>
					</div>
				</div>
			</div>
		</div>

		<div class="ui floated right aligned right eight wide column">
			<form action="">
				<label style="margin-right:6px">Key</label>

				<select name="key">
					<option value="">Any</option>
					<?php foreach ( \App\Models\User_Song_Version::getKeys() as $key => $name ): ?>
						<option value="<?= $key ?>"><?= $name ?></option>
					<?php endforeach ?>
				</select>
			</form>
		</div>
	</div>

	<?= \App\Helpers\Core\Markup::segmentLoadingContainer('songs_container', NULL, 'purple') ?>
</div>