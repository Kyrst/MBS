	<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title><?= $page_title ?></title>

		<?php foreach ( $assets[\App\Http\Controllers\CoreController::ASSET_CSS] as $css_file ): ?>
			<link href="<?= ($css_file['external'] === FALSE ? $base_url : '') . $css_file['path'] ?>" rel="stylesheet">
		<?php endforeach ?>
	</head>

	<body id="<?= $page_id ?>">
		<?php if ( $current_page !== 'dashboard/auth/sign-in' ): ?>
			<div class="ui page grid">
				<header id="header" class="row">
					<div class="three wide column">
						<a href="<?= URL::route('home') ?>" id="logo" class="ui header"><?= Config::get('custom.PROJECT_NAME') ?></a>
					</div>

					<div class="thirteen wide column" style="text-align:right">
						<div id="header_player_container">
							<a href="javascript:" id="header_player_play_button"><i id="header_player_play_button_icon" class="pause icon"></i></a>
							<a href="javascript:" id="header_player_stop_button"><i id="header_player_stop_button_icon" class="stop icon"></i></a>
							<span id="header_player_title"></span>
							<progress id="header_player_progress" max="100" value="0"></progress>
							<label><input type="checkbox" name="loop" id="header_player_loop_toggle" checked> Loop</label>
							<span id="header_player_position"></span> / <span id="header_player_duration"></span>
						</div>

						<div id="header_dropdown" class="ui selection dropdown">
							<i class="dropdown icon"></i>
							<div class="text"><?= $user->getAlias() ?></div>
							<div class="menu">
								<a href="<?= $user->getURL(\App\Models\User::PROFILE_PAGE) ?>" class="item">Profile</a>
								<a href="<?= URL::route('dashboard/sign-out') ?>" class="item">Sign Out</a>
							</div>
						</div>

						<form action="" id="player_form">
							<input type="range" id="header_volume" min="0" max="100" value="<?= $volume ?>">
						</form>
					</div>
				</header>

				<div class="row">
					<div class="sixteen wide column">
						<?php if ( isset($breadcrumb) ): ?>
							<?= $breadcrumb ?>
						<?php endif ?>

						<?= $content ?>
					</div>
				</div>
			</div>
		<?php else: ?>
			<?= $content ?>
		<?php endif ?>

		<?= $jquery . $inline_js ?>

		<?php foreach ( $assets[\App\Http\Controllers\CoreController::ASSET_JS] as $js_file ): ?>
			<script src="<?= ($js_file['external'] === FALSE ? $base_url : '') . $js_file['path'] ?>"></script>
		<?php endforeach ?>
	</body>
</html>