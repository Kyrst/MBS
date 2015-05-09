function Core_DynamicTable() {}

Core_DynamicTable.prototype =
{
	container_selector: '#dynamic_table_container',
	search_input_selector: '#dynamic_table_search',

	$container: null,
	$paging_containers: null,

	$search_input: null,
	search_query: '',
	searching: false,

	loading_html: null,

	current_page: 1,
	num_pages: null,

	init: function()
	{
		if ( typeof dynamic_table === 'object' )
		{
			this.$container = $(this.container_selector);

			if ( this.$container.length === 0 )
			{
				$core.log('Could not find dynamic table element "' + this.container_selector + '".');

				return;
			}

			if ( typeof dynamic_table.urls === 'undefined' )
			{
				this.show_error();

				$core.log('Required attribute "urls" is not defined.');

				return;
			}

			this.loading_html = this.$container.html();

			this.refresh(true);
		}
	},

	refresh: function(init)
	{
		var inst = this;

		if ( init === false )
		{
			this.show_loader();
		}

		var ajax_data = {};

		if ( dynamic_table.paging.enabled === true )
		{
			ajax_data.page = inst.current_page;
		}

		if ( dynamic_table.search.enabled === true )
		{
			ajax_data.search_query = inst.search_query;
		}

		$core.ajax.get
		(
			dynamic_table.urls.get,
			ajax_data,
			{
				success: function(result)
				{
					$(result.data.html).imagesLoaded().always(function()
					{
						inst.$container.html(result.data.html);

						if ( dynamic_table.paging.enabled === true )
						{
							inst.current_page = result.data.paging.current_page;
							inst.num_pages = result.data.paging.num_pages;
						}

						inst.binds();
					});
				},
				error: function()
				{
					inst.show_error();
				}
			}
		);
	},

	show_loader: function()
	{
		this.$container.html(this.loading_html);
	},

	show_error: function()
	{
		this.$container.html('Could not load ' + dynamic_table.identifier.plural + '.');
	},

	binds: function(id)
	{
		var inst = this,
			$container = ($core.isDefined(id) ? $('#dynamic_table_item_' + id) : inst.$container);

		$container.find('.manage-dropdown').dropdown($core.options.dynamic_table.manage_dropdown_settings);

		$container.find('.delete-button').on('click', function(e)
		{
			e.preventDefault();

			var $delete_button = $(this),
				$tr = $delete_button.closest('tr'),
				item_id = $tr.data('id'),
				item_title = $tr.data('title'),
				delete_url = $delete_button.attr('href');

			$core.ui.message.confirm('Are you sure you want to delete ' + dynamic_table.identifier.singular + ' "' + item_title + '"?', function()
			{
				var $dynamic_table_item = $('#dynamic_table_item_' + item_id),
					dynamic_table_item_html = $dynamic_table_item.html();

				$dynamic_table_item.html('<td colspan="' + $core.getObjectSize(dynamic_table.table_columns) + '">Deleting...</td>');

				$core.ajax.post
				(
					delete_url,
					{
						id: item_id,
						_token: csrf_token
					},
					{
						success: function()
						{
							inst.refresh(false);

							$core.ui.message.success(dynamic_table.identifier.singular + ' "' + item_title + '" was deleted.');
						},
						error: function()
						{
							$core.ui.message.error('Could not delete ' + dynamic_table.identifier.singular + ' "' + item_title + '".');

							$dynamic_table_item.html(dynamic_table_item_html);

							inst.binds(item_id);
						}
					}
				);
			});
		});

		if ( dynamic_table.paging.enabled === true )
		{
			inst.$paging_containers = inst.$container.find('.pagination');

			inst.$paging_containers.find('.prev:not(.disabled)').on('click', function()
			{
				inst.current_page--;

				inst.refresh(false);
			});

			inst.$paging_containers.find('.next:not(.disabled)').on('click', function()
			{
				inst.current_page++;

				inst.refresh(false);
			});
		}

		if ( dynamic_table.search.enabled === true )
		{
			inst.$search_input = $(inst.search_input_selector);

			if ( inst.searching === true )
			{
				$core.ui.setCaretAtEnd(document.querySelector(inst.search_input_selector));

				inst.searching = false;
			}

			if ( inst.$search_input.length === 0 )
			{
				$core.log('Could not find dynamic table search input ' + inst.search_input_selector + '.');
			}
			else
			{
				inst.$search_input.on('keyup', function(e)
				{
					if ( (e.keyCode || e.which) === 13 )
					{
						inst.search_query = inst.$search_input.val();
						inst.searching = true;

						inst.refresh(false);
					}
				});
			}
		}
	}
};