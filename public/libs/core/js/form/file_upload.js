function Core_Form_File_Upload() {}

Core_Form_File_Upload.prototype =
{
	PROGRESS_BAR_TYPE_DIALOG: 1,
	PROGRESS_BAR_TYPE_CUSTOM: 2,
	PROGRESS_BAR_TYPE_DYNAMIC_ITEM: 3,

	default_options:
	{
		saveURL: null,
		progressBarType: 2,
		progressBarDialog: null,
		progressBarContainerSelector: null,
		progressBarKeyword: null,
		maxFileSize: '2',
		allowedFileTypes: null,
		data: {},
		onStart: null,
		onProgress: null,
		onComplete: null,
		onAlways: function() {},
		onError: null
	},

	file_uploads: {},

	init: function(selector, options)
	{
		var inst = this;

		for ( var option_key in inst.default_options )
		{
			if ( inst.default_options.hasOwnProperty(option_key) && !options.hasOwnProperty(option_key) )
			{
				options[option_key] = inst.default_options[option_key];
			}
		}

		inst.file_uploads[selector] =
		{
			$element: document.querySelector(selector),
			options: options,
			$progressBarContainer: null
		};

		if ( inst.file_uploads[selector].$element === null )
		{
			$core.log('Could not find file upload element "' + this.selector + '".');

			return;
		}
		else if ( inst.file_uploads[selector].options.saveURL === null )
		{
			$core.log('Missing required option "saveURL".');

			return;
		}

		if ( inst.file_uploads[selector].options.progressBarType === inst.PROGRESS_BAR_TYPE_DIALOG )
		{
			if ( inst.file_uploads[selector].options.progressBarDialog === null )
			{
				$core.log('No progress bar dialog defined.');

				return;
			}
		}
		else if ( inst.file_uploads[selector].options.progressBarType === inst.PROGRESS_BAR_TYPE_CUSTOM )
		{
			if ( inst.file_uploads[selector].options.progressBarContainerSelector === null )
			{
				$core.log('No progress bar selector defined.');

				return;
			}

			var $progressBarContainer = document.querySelector(inst.file_uploads[selector].options.progressBarContainerSelector);

			if ( $progressBarContainer === null )
			{
				$core.log('Could not find file upload progress bar container "' + inst.file_uploads[selector].options.progressBarContainerSelector + '".');

				return;
			}

			$progressBarContainer.innerHTML = '<div class="ui small progress"><div class="bar"><div class="progress"></div></div><div class="label"></div></div>';

			$(inst.file_uploads[selector].options.progressBarContainerSelector).find('.progress').progress(
			{
				percent: 0
			});

			inst.file_uploads[selector].$progressBarContainer = document.querySelector(inst.file_uploads[selector].options.progressBarContainerSelector);

			inst.file_uploads[selector].$element.addEventListener('change', function()
			{
				inst.file_uploads[selector].$progressBarContainer.style.display = 'block';

				if ( inst.checkFileSize(selector) === true && inst.checkFileType(selector) === true )
				{
					inst.clearError(selector);
				}
			}, false);
		}

		return {
			start: function()
			{
				var _inst = this,
					files = $(selector)[0].files,
					file_upload_inst = inst.file_uploads[selector];

				if ( files.length === 0 )
				{
					$core.log('No file to upload.');

					file_upload_inst.options.onAlways();

					return;
				}
				else if ( files.length > 1 )
				{
					$core.log('More than one file selected.');

					file_upload_inst.options.onAlways();

					return;
				}

				var file = files[0];

				file_upload_inst.options.data.mime = file.type;

				if ( $core.isFunction(file_upload_inst.options.onStart) )
				{
					inst.file_uploads[selector].options.onStart();
				}

				if ( file_upload_inst.options.progressBarType === inst.PROGRESS_BAR_TYPE_DIALOG )
				{
					file_upload_inst.options.progressBarDialog.showLoader('Start uploading of ' + file_upload_inst.options.progressBarKeyword + '...');
				}

				$core.ajax.post
				(
					file_upload_inst.options.saveURL,
					file,
					{
						progress: function(percent)
						{
							if ( file_upload_inst.options.progressBarType === inst.PROGRESS_BAR_TYPE_DIALOG )
							{
								file_upload_inst.options.progressBarDialog.setLoaderText('Uploading ' + file_upload_inst.options.progressBarKeyword + ' (' + percent + '%)...');
							}
							else if ( file_upload_inst.options.progressBarType === inst.PROGRESS_BAR_TYPE_CUSTOM )
							{
								$(file_upload_inst.options.progressBarContainerSelector).find('.progress').progress({ percent: percent });

								if ( $core.isFunction(file_upload_inst.options.onProgress) )
								{
									file_upload_inst.options.onProgress(percent);
								}
							}
							else if ( file_upload_inst.options.progressBarType === inst.PROGRESS_BAR_TYPE_DYNAMIC_ITEM )
							{
							}
						},
						success: function(result)
						{
							if ( $core.isFunction(file_upload_inst.options.onComplete) )
							{
								file_upload_inst.options.onComplete();
							}

							file_upload_inst.options.onAlways();
						},
						error: function()
						{
							inst.throwError(selector, 'Could not save image (<a href="javascript:" class="core-file-upload-retry-button">retry</a>).');

							$(file_upload_inst.options.progressBarContainerSelector).find('.core-file-upload-retry-button').on('click', function()
							{
								$(file_upload_inst.options.progressBarContainerSelector).find('.progress').progress({ percent: 0 });

								_inst.start();
							});

							if ( $core.isFunction(file_upload_inst.options.onError) )
							{
								file_upload_inst.options.onError();
							}

							file_upload_inst.options.onAlways();
						}
					},
					{
						file_upload: true,
						file_upload_data: file_upload_inst.options.data
					}
				);
			},
			addData: function(key, value)
			{
				inst.file_uploads[selector].options.data[key] = value;
			}
		};
	},

	checkFileSize: function(selector)
	{
		var file = $(selector)[0].files[0];

		if ( file.size > post_max_size )
		{
			this.throwError(selector, 'File (' + $core.formatSize(file.size) + ') is larger than server post max size (' + $core.formatSize(post_max_size) + ').');

			return false;
		}
		else if ( file.size > upload_max_filesize )
		{
			this.throwError(selector, 'File (' + $core.formatSize(file.size) + ') is larger than server max upload file size (' + $core.formatSize(upload_max_filesize) + ').');

			return false;
		}

		return true;
	},

	checkFileType: function(selector)
	{
		var inst = this,
			allowed_file_types = inst.file_uploads[selector].options.allowedFileTypes;

		if ( allowed_file_types === null )
		{
			return true;
		}

		var file = $(selector)[0].files[0];

		if ( !$core.inArray(file.type, allowed_file_types) )
		{
			this.throwError(selector, 'Selected file type (' + file.type + ') is not valid (' + $core.implode(', ', allowed_file_types) + ').');

			return false;
		}

		return true;
	},

	throwError: function(selector, error)
	{
		var inst = this,
			$progress_bar_container = $(inst.file_uploads[selector].options.progressBarContainerSelector),
			$label = $progress_bar_container.find('.label');

		$progress_bar_container.find('.progress').addClass('error');
		$label.html(error);
	},

	clearError: function(selector)
	{
		var inst = this,
			$progress_bar_container = $(inst.file_uploads[selector].options.progressBarContainerSelector),
			$label = $progress_bar_container.find('.label');

		$progress_bar_container.find('.progress').removeClass('error');
		$label.html('');
	}
};