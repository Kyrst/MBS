function Core_DynamicItem() {}

Core_DynamicItem.prototype =
{
	form_selector: '#dynamic_item_form',
	$form: null,

	loader_selector: '#dynamic_item_form_loader',
	$loader: null,
	$loader_text: null,

	tabs_selector: '#dynamic_item_tabs',
	$tabs: null,

	save_button_selector: '#dynamic_item_save_button',
	$save_button: null,

	types_with_custom_saving: ['image'],

	init: function()
	{
		var inst = this;

		inst.$form = $(inst.form_selector);
		inst.$form.on('submit', function()
		{
			return false;
		});

		inst.$loader = $(inst.loader_selector);
		inst.$loader_text = inst.$loader.children('.text');

		inst.$save_button = $(inst.save_button_selector);
		inst.$save_button.attr('data-default_text', inst.$save_button.text());

		if ( inst.$form.length === 1 )
		{
			inst.$form.find('.image-card-replace-button').on('click', function()
			{
				var $image_card_replace_button = $(this),
					$input_file = $image_card_replace_button.parents('.image-card').find('.image-card-file-input');

				$input_file.on('change', function()
				{
					var field_id = $image_card_replace_button.parents('.field').data('id'),
						cancel_replace_button_id = 'cancel_replace_' + field_id,
						$buttons = $image_card_replace_button.closest('.ui.buttons'),
						$status_text = $image_card_replace_button.parents('.extra').find('.image-card-status-text');

					$buttons.hide();
					$status_text.html(this.files[0].name + ' <a href="javascript:" id="' + cancel_replace_button_id + '">(Cancel)</a>').show();

					$('#' + cancel_replace_button_id).on('click', function()
					{
						$input_file.val('');
						$status_text.hide().html('');
						$buttons.show();
					});
				});

				$input_file.trigger('click');
			});

			inst.$form.find('.image-card-delete-button').on('click', function()
			{
				var $image_card_delete_button = $(this),
					field_id = $image_card_delete_button.parents('.field').data('id'),
					hidden_selector_id = 'delete_' + field_id;

				if ( $image_card_delete_button.hasClass('active') )
				{
					$('#' + hidden_selector_id).remove();
					$image_card_delete_button.removeClass('active');
				}
				else
				{
					inst.$form.prepend('<input type="hidden" name="items_to_delete[]" id="' + hidden_selector_id + '" value="' + field_id + '">');
					$image_card_delete_button.addClass('active');
				}
			});

			var validation_rules = {},
				custom_saving_columns = [];

			for ( var column_id in dynamic_item_columns )
			{
				if ( dynamic_item_columns.hasOwnProperty(column_id) )
				{
					dynamic_item_columns[column_id].id = column_id;

					var column = dynamic_item_columns[column_id],
						rules = [];

					if ( $core.inArray(column.form.type, inst.types_with_custom_saving) )
					{
						custom_saving_columns.push(column);
					}

					if ( typeof column.form.validation === 'object' )
					{
						if ( typeof column.form.validation.required === 'number' && (column.form.validation.required === ALWAYS_REQUIRED || item_id_to_edit === null && column.form.validation.required === REQUIRED_ON_ADD) )
						{
							var error_message = '';

							if ( column.form.type === 'select' )
							{
								error_message = 'Choose a ' + column.title.toLowerCase();
							}
							else
							{
								error_message = column.title + ' is required';
							}

							rules.push({ type: 'empty', prompt: error_message });
						}

						if ( typeof column.form.maxlength === 'number' )
						{
							rules.push({ type: 'maxLength[' + column.validation.maxlength + ']', prompt: 'Name can\'t be longer than ' + column.validation.maxlength + ' character' + (column.validation.maxlength !== 1 ? 's' : '') });
						}
					}

					validation_rules[column_id] =
					{
						identifier: column_id,
						rules: rules
					};
				}
			}

			var num_custom_saving_columns = custom_saving_columns.length;

			inst.$form.form
			(
				validation_rules,
				{
					inline: true,
					onSuccess: function()
					{
						if ( typeof inst.$save_button.data('loading_text') === 'string' )
						{
							inst.$save_button.text(inst.$save_button.data('loading_text'));
						}

						inst.$save_button.prop('disabled', true);

						inst.$loader.show();

						var have_custom_saving_columns = (num_custom_saving_columns > 0);

						var post_data = inst.$form.find(':input').serialize();
						post_data += '&have_custom_saving_columns=' + (have_custom_saving_columns === true ? 'yes' : 'no');

						$core.ajax.post
						(
							inst.$form.attr('action'),
							post_data,
							//inst.$form.serializeArray(),
							{
								success: function(result)
								{
									var saved_id = (item_id_to_edit !== null ? item_id_to_edit : result.data.added_item_id);

									var custom_saving_columns_to_save = [];

									// Find first custom savings column to save
									for ( var i = 0, num_custom_saving_columns = custom_saving_columns.length; i < num_custom_saving_columns; i++ )
									{
										var custom_saving_column = custom_saving_columns[i],
											$form_field = $('[name="' + custom_saving_column.form.name + '"]');

										if ( $form_field.length === 1 && $form_field.val() !== '' )
										{
											custom_saving_columns_to_save.push(custom_saving_columns[i]);
										}
									}

									var num_custom_saving_columns_to_save = custom_saving_columns_to_save.length,
										have_custom_saving_columns_to_save = (num_custom_saving_columns_to_save > 0),
										first_custom_savings_column_to_save = (have_custom_saving_columns_to_save ? custom_saving_columns_to_save[0] : null);

									inst.setLoaderText($core.ucfirst(dynamic_item_options.identifier.singular) + ' ' + (item_id_to_edit === null ? 'added' : 'saved') + '! ' + (first_custom_savings_column_to_save !== null ? ' Start uploading ' + $core.lcfirst(first_custom_savings_column_to_save.title) + '...' : 'Redirecting...'));

									var redirect = function()
									{
										$core.uri.redirect((item_id_to_edit !== null ? ($core.isDefined(dynamic_item_options.save.edit.success.redirect) ? dynamic_item_options.save.edit.success.redirect : '') : ($core.isDefined(dynamic_item_options.save.add.success.redirect) ? dynamic_item_options.save.add.success.redirect : '')));
									};

									var setDone = function()
									{
										inst.setLoaderText('We\'re all set! Redirecting...');
									};

									if ( have_custom_saving_columns_to_save )
									{
										var current_custom_saving_column_index = 0;

										var processCustomSavingColumn = function(index)
										{
											var column = custom_saving_columns_to_save[index],
												column_selector = '[name="' + column.form.name + '"]';

											var handleNext = function()
											{
												current_custom_saving_column_index++;

												if ( current_custom_saving_column_index === num_custom_saving_columns_to_save )
												{
													setDone();
													redirect();
												}
												else if ( current_custom_saving_column_index < num_custom_saving_columns_to_save )
												{
													processCustomSavingColumn(current_custom_saving_column_index);
												}
											};

											// Process
											if ( column.form.type === 'image' )
											{
												var custom_saving_column_file_upload = $core.form.file_upload.init
												(
													column_selector,
													{
														progressBarType: $core.form.file_upload.PROGRESS_BAR_TYPE_DYNAMIC_ITEM,
														progressBarKeyword: 'mobile image',
														saveURL: column.form.save_url,
														allowedFileTypes: column.form.allowed_file_types,
														data:
														{
															id: saved_id,
															column_id: column.id
														},
														onProgress: function(percent)
														{
															inst.setLoaderText('Uploading ' + $core.lcfirst(column.title) + ' (' + percent + '%)...');
														},
														onComplete: function()
														{
															handleNext();
														},
														onError: function()
														{
															handleNext();
														}
													}
												);

												custom_saving_column_file_upload.start();
											}
										};

										// Start
										processCustomSavingColumn(0);
									}
									else // No custom columns to save, just redirect
									{
										redirect();
									}
								},
								error: function()
								{
									inst.$loader.hide();

									$core.ui.message.error('Could not save ' + dynamic_item_options.identifier.singular + '.');

									if ( typeof inst.$save_button.data('default_text') === 'string' )
									{
										inst.$save_button.text(inst.$save_button.data('default_text'));
									}

									inst.$save_button.prop('disabled', false);
								}
							}
						);

						return false;
					}
				}
			);

			inst.$tabs = $(inst.tabs_selector);

			if ( inst.$tabs.length === 1 )
			{
				$(inst.tabs_selector).find('.item').tab();
			}
		}
	},

	setLoaderText: function(text)
	{
		this.$loader_text.html(text);
	}
};