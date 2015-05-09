<?php namespace App\Http\Controllers\Dashboard;

use App\Models\Image_Generation_Queue;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImageGenerationQueueController extends \App\Http\Controllers\DashboardController
{
	public function get()
	{
		$image_generation_queue_items = \App\Models\Image_Generation_Queue::newestFirst()->get();
		$num_image_generation_queue_items = count($image_generation_queue_items);

		$view = view('layouts/partials/dashboard/image_generation_queue');
		$view->image_generation_queue_items = $image_generation_queue_items;
		$view->num_image_generation_queue_items = $num_image_generation_queue_items;

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function cancel()
	{
		$id = \Input::get('id');

		try
		{
			$image_generation_queue_item = \App\Models\Image_Generation_Queue::where('id', $id)->firstOrFail();

			if ( $image_generation_queue_item->status === Image_Generation_Queue::STATUS_PROCESSING )
			{
				return $this->ajax->outputWithError('Processing of image has already started.');
			}
			else if ( $image_generation_queue_item->status === Image_Generation_Queue::STATUS_DONE )
			{
				return $this->ajax->outputWithError('Image has already been generated.');
			}

			$image_generation_queue_item->delete();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find image in queue.');
		}

		return $this->ajax->output();
	}

	public function abort()
	{
		$id = \Input::get('id');

		try
		{
			$image_generation_queue_item = \App\Models\Image_Generation_Queue::where('id', $id)->firstOrFail();

			if ( $image_generation_queue_item->status === Image_Generation_Queue::STATUS_DONE )
			{
				return $this->ajax->outputWithError('Image has already been generated.');
			}

			$image_generation_queue_item->delete();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find image in queue.');
		}

		return $this->ajax->output();
	}
}