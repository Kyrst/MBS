<?php namespace App\Http\Controllers\Dashboard\HomePage;

use App\Models\Home_Page_Slider_Item;
use App\Models\Image_Generation_Queue;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImageSliderController extends \App\Http\Controllers\Dashboard\HomePageController
{
	public function imageSlider()
	{
		$this->addBreadcrumbItem('Home Page', route('dashboard/home-page'));
		$this->addBreadcrumbItem('Image Slider');

		return $this->display(['Image Slider', 'Home Page', 'Dashboard']);
	}

	public function getSliderItems()
	{
		$view = view('dashboard/home_page/image_slider/image_slider/slider_items');

		$slider_items = \App\Models\Home_Page_Slider_Item::orderBy('position')->get();
		$num_slider_items = count($slider_items);

		$view->slider_items = $slider_items;
		$view->num_slider_items = $num_slider_items;

		$this->ajax->addData('num_home_page_slider_items', $num_slider_items);

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function getItemDialog()
	{
		$id = \Input::get('slider_item_id');

		$item_to_edit = null;

		if ( $id !== null )
		{
			try
			{
				$item_to_edit = Home_Page_Slider_Item::where('id', $id)->firstOrFail();
			}
			catch ( ModelNotFoundException $e )
			{
				return $this->ajax->outputWithError('Could not find item with ID "' . $id . '".');
			}
		}

		$view = view('dashboard/home_page/image_slider/image_slider/item_dialog');
		$view->item_to_edit = $item_to_edit;

		$this->ajax->addData('html', $view->render());

		return $this->ajax->output();
	}

	public function saveItemDialog()
	{
		$input = \Input::all();

		$item_slider_id = $input['id'];
		$link = $input['link'];

		if ( $item_slider_id !== '' )
		{
			try
			{
				$home_page_slider_item = Home_Page_Slider_Item::where('id', $item_slider_id)->firstOrFail();
			}
			catch ( ModelNotFoundException $e )
			{
				return $this->ajax->outputWithError('Could not find item with ID "' . $item_slider_id . '".');
			}
		}
		else
		{
			$home_page_slider_item = new Home_Page_Slider_Item();
			$home_page_slider_item->position = Home_Page_Slider_Item::getNextPosition();

			$this->ajax->addData('added_home_page_slider_item_id', $home_page_slider_item->id);
		}

		$home_page_slider_item->link = \App\Helpers\Core\URI::formatURL(trim($link));
		$home_page_slider_item->save();

		return $this->ajax->output();
	}

	public function uploadDesktopImage()
	{
		$home_page_slider_item_id = $_SERVER['HTTP_HOME_PAGE_SLIDER_ITEM_ID'];
		$mime = $_SERVER['HTTP_MIME'];

		try
		{
			$home_page_slider_item = \App\Models\Home_Page_Slider_Item::where('id', $home_page_slider_item_id)->firstOrFail();

			$upload_dir = \App\Models\Home_Page_Slider_Item::getDesktopImageDirectory($home_page_slider_item_id);

			if ( !file_exists($upload_dir) )
			{
				mkdir($upload_dir, 0775, TRUE);
			}

			$file_extension = \App\Helpers\Core\File::getExtensionFromMime($mime);
			$upload_filename = 'original.' . $file_extension;
			$upload_filepath = $upload_dir . $upload_filename;

			$input_fp = fopen('php://input', 'rb');
			$output_fp = fopen($upload_filepath, 'wb');

			while ( $chunk = fread($input_fp, 8192) )
			{
				fwrite($output_fp, $chunk);
			}

			fclose($input_fp);
			fclose($output_fp);

			Image_Generation_Queue::add(Image_Generation_Queue::TYPE_HOME_PAGE_SLIDER_DESKTOP_IMAGE, $home_page_slider_item_id);

			$home_page_slider_item->setDesktopImage
			(
				[
					'file_extension' => $file_extension,
					'mime' => $mime,
					'index' => 0,
					'processing' => 'yes'
				]
			);
		}
		catch ( ModelNotFoundException $e )
		{
		}

		return \Response::json();
	}

	public function uploadMobileImage()
	{
		$home_page_slider_item_id = $_SERVER['HTTP_HOME_PAGE_SLIDER_ITEM_ID'];
		$mime = $_SERVER['HTTP_MIME'];

		try
		{
			$home_page_slider_item = \App\Models\Home_Page_Slider_Item::where('id', $home_page_slider_item_id)->firstOrFail();

			$upload_dir = \App\Models\Home_Page_Slider_Item::getMobileImageDirectory($home_page_slider_item_id);

			if ( !file_exists($upload_dir) )
			{
				mkdir($upload_dir, 0775, TRUE);
			}

			$file_extension = \App\Helpers\Core\File::getExtensionFromMime($mime);
			$upload_filename = 'original.' . $file_extension;
			$upload_filepath = $upload_dir . $upload_filename;

			$input_fp = fopen('php://input', 'rb');
			$output_fp = fopen($upload_filepath, 'wb');

			while ( $chunk = fread($input_fp, 8192) )
			{
				fwrite($output_fp, $chunk);
			}

			fclose($input_fp);
			fclose($output_fp);

			Image_Generation_Queue::add(Image_Generation_Queue::TYPE_HOME_PAGE_SLIDER_MOBILE_IMAGE, $home_page_slider_item_id);

			$home_page_slider_item->setMobileImage
			(
				[
					'file_extension' => $file_extension,
					'mime' => $mime,
					'index' => 0,
					'processing' => 'yes'
				]
			);
		}
		catch ( ModelNotFoundException $e )
		{
		}

		return \Response::json();
	}

	public function saveItems()
	{
		$positions = \Input::get('positions');

		try
		{
			foreach ( $positions as $position => $home_page_slider_item_id )
			{
				\DB::update('UPDATE home_page_slider_items SET position = ? WHERE id = ?', [ $position, $home_page_slider_item_id ]);
			}
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find item with ID "' . $home_page_slider_item_id . '".');
		}

		return $this->ajax->output();
	}

	public function deleteItem()
	{
		$id = \Input::get('id');

		try
		{
			$home_page_slider_item = \App\Models\Home_Page_Slider_Item::where('id', $id)->firstOrFail();
			$home_page_slider_item->delete();
		}
		catch ( ModelNotFoundException $e )
		{
			return $this->ajax->outputWithError('Could not find item with ID "' . $id . '".');
		}

		return $this->ajax->output();
	}
}