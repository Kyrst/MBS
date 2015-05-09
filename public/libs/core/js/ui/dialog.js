function Core_UI_Dialog() {}

Core_UI_Dialog.prototype =
{
	default_options:
	{
		modes: null,
		title: null,
		width: 640,
		height: 480,
		modal: false,
		autoOpen: false,
		closeButton: true,
		loadingText: 'Loading',
		savingText: 'Saving',
		getURL: null,
		getData: null,
		saveURL: null,
		saveData: null,
		closeAfterSave: true,
		verticalPadding: 20,
		horizontalPadding: 18,
		headerHeight: 40,
		buttonsContainerHeight: 40,
		buttons: [],
		afterLoaded: null,
		afterSaved: null
	},

	mask_id: 'core_dialog_mask',
	$mask: null,

	dialogs: {},
	current_dialog_id: null,

	init: function()
	{
		var inst = this;

		$('body').on('keyup', function(e)
		{
			if ( e.which === 27 && inst.current_dialog !== null )
			{
				if ( $core.isDefined(inst.dialogs[inst.current_dialog_id]) )
				{
					inst.dialogs[inst.current_dialog_id].close();
				}
			}
		});
	},

	create: function(options)
	{
		var inst = this,
			dialog_id = $core.getObjectSize(inst.dialogs) + 1;

		if ( typeof options !== 'object' )
		{
			$core.log('Missing options for dialog.');

			return;
		}

		for ( var option_key in inst.default_options )
		{
			if ( inst.default_options.hasOwnProperty(option_key) && !options.hasOwnProperty(option_key) )
			{
				options[option_key] = inst.default_options[option_key];
			}
		}

		var dialog =
		{
			id: dialog_id,
			selector: '#core_dialog_' + dialog_id,
			$dialog: null,
			$loader: null,
			$loader_text: null,
			$header: null,
			$content: null,
			$buttons_container: null,
			$buttons: null,
			default_line_height: null,
			mode: null,
			options: options,
			setMode: function(mode)
			{
				var dialog_inst = this;

				if ( typeof dialog_inst.options.modes[mode] === 'undefined' )
				{
					$core.log('Invalid dialog mode "' + mode + '".');

					return false;
				}

				dialog_inst.mode = mode;

				if ( typeof dialog_inst.options.modes[mode].title !== 'undefined' )
				{
					dialog_inst.setTitle(dialog_inst.options.modes[mode].title);
				}

				return true;
			},
			getMode: function()
			{
				return dialog.mode;
			},
			setTitle: function(title)
			{
				this.$header.querySelector('.core-dialog-header-title').innerHTML = title;
			},
			open: function(mode)
			{
				var dialog_inst = this;

				if ( mode !== null )
				{
					if ( dialog_inst.setMode(mode) === false )
					{
						return false;
					}
				}

				if ( dialog_inst.options.modal === true && $('#' + inst.mask_id).length === 0 )
				{
					$('body').append('<div id="' + inst.mask_id + '" class="open"></div>');

					inst.$mask = $('#' + inst.mask_id);

					inst.$mask.on('click', function()
					{
						dialog_inst.close();
					});
				}

				dialog_inst.showLoader(dialog_inst.options.loadingText);

				dialog_inst.$dialog.classList.add('open');
				$('body').append(dialog_inst.$dialog);

				var buttons = dialog_inst.getButtons();

				var buttons_html = '',
					button_key;

				for ( button_key in buttons )
				{
					var button = buttons[button_key];

					buttons_html += button.html;
				}

				dialog_inst.$buttons.innerHTML = buttons_html;

				for ( button_key in buttons )
				{
					var button = buttons[button_key];

					if ( $core.isFunction(button.click) )
					{
						(function(button)
						{
							$(button.selector).on('click', function()
							{
								if ( button.click(button) === false )
								{
									return;
								}
							});
						})(button);
					}
				}

				$(dialog_inst.selector).find('.core-dialog-close-button').on('click', function()
				{
					dialog_inst.close();
				});

				inst.current_dialog_id = dialog_id;

				$core.ajax.get
				(
					dialog_inst.options.getURL,
					dialog_inst.options.getData,
					{
						success: function(result)
						{
							dialog_inst.$content.innerHTML = result.data.html;

							[].forEach.call(dialog_inst.$content.querySelectorAll('input'), function(input)
							{
								input.addEventListener('keyup', function(e)
								{
									if ( (e.keyCode || e.which) === 13 )
									{
										return false;
									}
								}, false);
							});

							dialog.$content.style.lineHeight = dialog.default_line_height;

							dialog_inst.hideLoader();
							dialog_inst.enableButtons();

							if ( $core.isFunction(dialog_inst.options.afterLoaded) )
							{
								dialog_inst.options.afterLoaded(true, result);
							}
						},
						error: function(result)
						{
							dialog_inst.setError('Could not load dialog.');

							dialog_inst.hideLoader();
							dialog_inst.enableButtons(true);

							if ( $core.isFunction(dialog_inst.options.afterLoaded) )
							{
								dialog_inst.options.afterLoaded(false, result);
							}
						}
					}
				);
			},
			close: function()
			{
				var dialog_inst = this;

				dialog_inst.$dialog.classList.add('close');

				$(dialog_inst.selector).one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function()
				{
					dialog_inst.$dialog.classList.remove('close');
					dialog_inst.$content.classList.remove('error');

					$(dialog_inst.selector).remove();
				});

				if ( dialog_inst.options.modal === true )
				{
					inst.$mask.removeClass('open').addClass('close');
					inst.$mask.one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function()
					{
						if ( inst.$mask === null )
						{
							return;
						}

						inst.$mask.remove();
						inst.$mask = null;
					});
				}

				inst.current_dialog_id = null;
			},
			getButton: function(button_id)
			{
				if ( this.options.modes !== null )
				{
					var mode = this.mode;

					for ( var button_key in this.options.modes[mode].buttons )
					{
						if ( this.options.modes[mode].buttons[button_key].id === button_id )
						{
							return this.options.modes[mode].buttons[button_key];
						}
					}
				}
				else
				{
					for ( var i = 0; i < num_buttons; i++ )
					{
						if ( this.options.buttons[i].id === button_id )
						{
							return this.options.buttons[i];
						}
					}
				}

				return null;
			},
			getButtons: function()
			{
				if ( this.options.modes !== null )
				{
					return this.options.modes[dialog.mode].buttons;
				}
				else
				{
					return this.options.buttons;
				}
			},
			enableButtons: function(exclude_save_buttons)
			{
				if ( $core.isUndefined(exclude_save_buttons) )
				{
					exclude_save_buttons = false;
				}

				var buttons = this.getButtons();

				for ( var button_key in buttons )
				{
					var button = buttons[button_key];

					if ( exclude_save_buttons === false || (exclude_save_buttons === true && typeof button.save !== 'undefined' && button.save === false)  )
					{
						$(button.selector).removeClass('disabled');
					}
				}
			},
			disableButtons: function(only_save_buttons)
			{
				if ( $core.isUndefined(only_save_buttons) )
				{
					only_save_buttons = false;
				}

				var buttons = this.getButtons();

				for ( var button_key in buttons )
				{
					var button = buttons[button_key];

					if ( only_save_buttons === false || (only_save_buttons && (typeof button.save !== 'undefined' && button.save === true)) )
					{
						$(button.selector).addClass('disabled');
					}
					else
					{
						$(button.selector).removeClass('disabled');
					}
				}
			},
			resetButtons: function()
			{
				var buttons = this.getButtons();

				for ( var button_key in buttons )
				{
					var button = buttons[button_key];

					$(button.selector).html(button.text).removeClass('disabled');
				}
			},
			showLoader: function(text)
			{
				if ( $core.isUndefined(text) )
				{
					text = this.options.loadingText;
				}

				dialog.$dialog.classList.add('loading');
				dialog.$content.style.lineHeight = dialog.$content.style.height;
				dialog.$loader_text.innerHTML = text;
				dialog.$loader.style.display = 'block';
			},
			setLoaderText: function(text)
			{
				dialog.$loader_text.innerHTML = text;
			},
			hideLoader: function()
			{
				dialog.$dialog.classList.remove('loading');
				dialog.$loader.style.display = 'none';
			},
			save: function(data)
			{
				var dialog_inst = this;

				dialog_inst.showLoader(dialog_inst.options.savingText);
				dialog_inst.disableButtons();

				if ( typeof data._token === 'undefined' )
				{
					data._token = $core.options.csrf_token;
				}

				$core.ajax.post
				(
					dialog_inst.options.saveURL,
					data,
					{
						success: function(result)
						{
							if ( $core.isFunction(dialog_inst.options.afterSaved) )
							{
								dialog_inst.options.afterSaved();
							}

							if ( dialog_inst.options.closeAfterSave === true )
							{
								dialog_inst.close();
							}
						},
						error: function()
						{
							dialog_inst.setError('Could not save promo.');
						},
						always: function()
						{
							dialog_inst.hideLoader();

							if ( dialog_inst.options.closeAfterSave === false )
							{
								dialog_inst.enableButtons();
							}
						}
					}
				);
			},
			setError: function(error)
			{
				this.$content.style.lineHeight = dialog.$content.style.height;
				this.$content.className = 'core-dialog-content error';
				this.$content.innerHTML = error;

				this.disableButtons(true);
			},
			setGetData: function(data)
			{
				this.options.getData = data;
			},
			getGetData: function(key)
			{
				return this.options.getData[key];
			}
		};

		var content_height = dialog.options.height - (dialog.options.verticalPadding * 2) - dialog.options.headerHeight - dialog.options.buttonsContainerHeight;

		dialog.$loader = document.createElement('div');
		dialog.$loader.className = 'ui segment core-dialog-loader-container';
		dialog.$loader.style.height = content_height + 'px';

		var $loader_dimmer = document.createElement('div');
		$loader_dimmer.className = 'ui active inverted dimmer';

		dialog.$loader_text = document.createElement('div');
		dialog.$loader_text.setAttribute('id', 'core_dialog_' + dialog.id + '_loader_text');

		var loader_size;

		if ( dialog.options.height > 640 )
		{
			loader_size = 'large';
		}
		else if ( dialog.options.height > 480 )
		{
			loader_size = 'medium';
		}
		else
		{
			loader_size = 'small';
		}

		dialog.$loader_text.className = 'ui ' + loader_size + ' text loader';
		dialog.$loader_text.innerHTML = dialog.options.loadingText;

		$loader_dimmer.appendChild(dialog.$loader_text);
		dialog.$loader.appendChild($loader_dimmer);

		dialog.$header = document.createElement('div');
		dialog.$header.className = 'core-dialog-header';
		dialog.$header.style.height = dialog.options.headerHeight + 'px';
		dialog.$header.innerHTML = '<div class="core-dialog-header-title">' + dialog.options.title + '</div>' + (dialog.options.closeButton === true ? '<div class="core-dialog-header-buttons"><a href="javascript:" class="core-dialog-close-button"><i class="remove icon"></i></a></div>' : '');

		dialog.$content = document.createElement('div');
		dialog.$content.className = 'core-dialog-content';

		dialog.$content.style.height = content_height + 'px';

		dialog.$buttons_container = document.createElement('div');
		dialog.$buttons_container.className = 'core-dialog-buttons-container';
		dialog.$buttons_container.style.height = dialog.options.buttonsContainerHeight + 'px';

		dialog.$buttons = document.createElement('div');
		dialog.$buttons.className = 'core-dialog-buttons';

		var initButton = function(button)
		{
			button.selector = '#dialog_' + dialog.id + '_button_' + (i + 1);
			button.html = '<a href="javascript:" id="' + button.selector.substring(1) + '" class="ui button tiny disabled' + (typeof button.save !== 'undefined' && button.save === true ? ' blue' : '') + '">' + button.text + '</a>';

			(function(button, i)
			{
				button.setText = function(text)
				{
					$(button.selector).html(text);

					return button;
				};

				button.setLoadingText = function()
				{
					button.setText(button.loadingText);
				};

				button.enable = function()
				{
					$(button.selector).removeClass('disabled');

					return button;
				};

				button.disable = function()
				{
					$(button.selector).addClass('disabled');

					return button;
				};

				button.spin = function()
				{
					$(button.selector).addClass('loading');

					return button;
				};

				button.stopSpin = function()
				{
					$(button.selector).removeClass('loading');

					return button;
				};
			})(button, i);

			return button;
		};

		if ( dialog.options.modes !== null )
		{
			for ( var mode_key in dialog.options.modes )
			{
				var mode = dialog.options.modes[mode_key];

				if ( $core.isDefined(mode.buttons) )
				{
					for ( var i = 0, num_buttons = mode.buttons.length; i < num_buttons; i++ )
					{
						dialog.options.modes[mode_key].buttons[i] = initButton(dialog.options.modes[mode_key].buttons[i]);
					}
				}
			}
		}
		else
		{
			for ( var i = 0, num_buttons = options.buttons.length; i < num_buttons; i++ )
			{
				dialog.options.buttons[i] = initButton(dialog.options.buttons[i]);
			}
		}

		dialog.$buttons_container.appendChild(dialog.$buttons);

		var $dialog = document.createElement('div');
		$dialog.className = 'core-dialog';
		$dialog.setAttribute('id', 'core_dialog_' + dialog.id);
		$dialog.style.width = dialog.options.width + 'px';
		$dialog.style.height = dialog.options.height + 'px';
		$dialog.style.marginTop = -(dialog.options.height / 2) + 'px';
		$dialog.style.marginLeft = -(dialog.options.width / 2) + 'px';
		$dialog.style.padding = dialog.options.verticalPadding + 'px' + ' ' + dialog.options.horizontalPadding + 'px';

		$dialog.appendChild(dialog.$loader);
		$dialog.appendChild(dialog.$header);
		$dialog.appendChild(dialog.$loader);
		$dialog.appendChild(dialog.$content);
		$dialog.appendChild(dialog.$buttons_container);

		dialog.default_line_height = dialog.$content.style.lineHeight;

		dialog.$dialog = $dialog;

		inst.dialogs[dialog.id] = dialog;

		return dialog;
	}
};