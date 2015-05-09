<?php namespace App\Http\Controllers\Dashboard;

class HomeController extends \App\Http\Controllers\DashboardController
{
	public function home()
	{
		return $this->display();
	}

	public function getSongs()
	{
		$songs = $this->user->songs()->newestFirst()->get();
		$num_songs = count($songs);

		$songs_container_view = view('dashboard/home/partials/songs_container');
		$songs_container_view->num_songs = $num_songs;
		$songs_container_view->songs = $songs;

		$this->ajax->addData('html', $songs_container_view->render());

		$_songs = [];

		foreach ( $songs as $song )
		{
			$latest_version = $song->getLatestVersion();

			$_songs[] =
			[
				'id' => $song->id,
				'waveform_data' => ($latest_version !== null ? json_decode($latest_version->waveform_data, TRUE) : null)
			];
		}

		$this->ajax->addData('num_songs', $num_songs);
		$this->ajax->addData('songs', $_songs);

		return $this->ajax->output();
	}
}