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
		<?= $content ?>

		<?= $jquery . $inline_js ?>

		<?php foreach ( $assets[\App\Http\Controllers\CoreController::ASSET_JS] as $js_file ): ?>
			<script src="<?= ($js_file['external'] === FALSE ? $base_url : '') . $js_file['path'] ?>"></script>
		<?php endforeach ?>
	</body>
</html>