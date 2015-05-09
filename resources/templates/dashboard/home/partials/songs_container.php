<?php if ( $num_songs > 0 ): ?>
	<div id="songs">
		<?php foreach ( $songs as $song ): ?>
			<?php $latest_version = $song->getLatestVersion() ?>

			<div id="song_<?= $song->id ?>" data-id="<?= $song->id ?>"<?php if ( $latest_version !== null ): ?> data-version="<?= $latest_version->version ?>" data-url="<?= $latest_version->getFileURL() ?>"<?php endif ?> data-title="<?= e($song->title) ?>" class="ui vertical segment song open">
				<div class="main">
					<div class="info">
						<a href="<?= $song->getURL(\App\Models\User_Song::URL_VIEW) ?>" class="song-title"><?= e($song->title) ?></a>
						<span class="song-added">Added <time data-livestamp="<?= ($latest_version !== null ? strtotime($latest_version->created_at) : $song->created_at->getTimestamp()) ?>"></time></span>
					</div>

					<div class="toolbar">
						<div>
							<?php if ( $latest_version !== null ): ?>
								<?php if ( $latest_version->bpm !== null ): ?><?= $latest_version->getBPM() ?> BPM<?php endif ?><?php if ( $latest_version->bpm !== null && $latest_version->key !== null ): ?> / <?php endif ?><?= $latest_version->getKey() ?>
							<?php endif ?>

							<a href="javascript:" class="delete-song-button"><i class="trash icon"></i></a>
						</div>

						<?php
						$songs_versions = $song->versions()->orderBy('version', 'DESC')->get();
						$num_versions = count($songs_versions);
						?>

						<?php if ( $num_versions > 1 ): ?>
							<div class="ui inline dropdown version-dropdown">
								<div class="text">Version <?= $songs_versions[0]->version ?></div>

								<i class="dropdown icon"></i>

								<div class="menu">
									<?php foreach ( $songs_versions as $song_version ): ?>
										<div class="item" data-text="Version <?= $song_version->version ?>">Version <?= $song_version->version ?></div>
									<?php endforeach ?>
								</div>
							</div>
						<?php endif ?>
					</div>
				</div>

				<?php if ( $latest_version !== null ): ?>
					<div id="song_waveform_container_<?= $song->id ?>" class="song-waveform-container">
						<div id="song_waveform_progress_<?= $song->id ?>" class="song-waveform-progress"></div>

						<div id="song_waveform_<?= $song->id ?>" class="song-waveform"></div>
					</div>
				<?php endif ?>
			</div>
		<?php endforeach ?>
	</div>
<?php else: ?>
	<span class="no-segment-data">No songs.</span>
<?php endif ?>