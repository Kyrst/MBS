function Core() {}

Core.prototype =
{
	options:
	{
		csrf_token: null,
		uri:
		{
			base_url: null
		},
		message:
		{
			engine: null
		},
		dynamic_table:
		{
			manage_dropdown_settings:
			{
				transition: 'drop',
				duration: 125
			}
		}
	},

	uri: null,
	ui: null,
	ajax: null,
	form: null,
	dynamic_table: null,
	dynamic_item: null,

	init: function(options)
	{
		if ( typeof options === 'object' )
		{
			this.options = $.extend(this.options, options);
		}

		this.uri = new Core_URI();

		this.ajax = new Core_Ajax();

		this.ui = new Core_UI();
		this.ui.init();

		this.form = new Core_Form();
		this.form.init();

		this.dynamic_table = new Core_DynamicTable();
		this.dynamic_table.init();

		this.dynamic_item = new Core_DynamicItem();
		this.dynamic_item.init();
	},

	afterDomInit: function()
	{
		this.ui.afterDomInit();
	},

	isEmpty: function(object)
	{
		if ( object === null )
		{
			return true;
		}

		if ( this.isArray(object) || this.isString(object) )
		{
			return (object.length === 0);
		}


		for ( var key in object )
		{
			if ( object.hasOwnProperty(key) )
			{
				if ( this.has(object, key) )
				{
					return false;
				}
			}
		}

		return true;
	},

	isUndefined: function(object)
	{
		return object == void 0;
	},

	isDefined: function(object)
	{
		return !this.isUndefined(object);
	},

	exists: function(object)
	{
		return object.length > 0;
	},

	isObject: function(object)
	{
		return object === Object(object);
	},

	isArray: Array.isArray || function(object)
	{
		return Object.prototype.toString.call(object) == '[object Array]';
	},

	inArray: function(subject, array)
	{
		return ($.inArray(subject, array) !== -1);
	},

	isFunction: function(object)
	{
		return (typeof object === 'function');
	},

	isString: function(object)
	{
		return Object.prototype.toString.call(object) === '[object String]';
	},

	isNumber: function(object)
	{
		return !isNaN(object);
	},

	isInteger: function(object)
	{
		return Object.prototype.toString.call(object) === '[object Number]';
	},

	has: function(object, key)
	{
		return Object.prototype.hasOwnProperty.call(object, key);
	},

	log: function(message)
	{
		window.console && console.log('Core: ' + message);
	},

	isEmail: function(str)
	{
		var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

		return str.length && regex.test(str);
	},

	getUnixTimestamp: function()
	{
		if ( !Date.now )
		{
			Date.now = function ()
			{
				return +new Date();
			};
		}

		return Date.now();
	},

	round: function(number, decimals)
	{
		return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
	},

	formatSize: function(size)
	{
		if ( size >= 1073741824 )
		{
			return this.round(size / 1073741824, 2) + ' GB';
		}

		if ( size >= 1048576 )
		{
			return this.round(size / 1048576, 2) + ' MB';
		}

		if ( size >= 1024 )
		{
			return this.round(size / 1024, 0) + ' KB';
		}

		return size + ' B';
	},

	escape: function(html)
	{
		return html
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	},

	shake: function($element, num_times, size)
	{
		for ( var i = 0; i < num_times; i++ )
		{
			$element.css('postion','relative').animate({ 'margin-left': '+=' + (size = -size) + 'px' }, 50);
		}
	},

	format: function(format)
	{
		var args = Array.prototype.slice.call(arguments, 1);

		return format.replace(/{(\d+)}/g, function(match, number)
		{
			return typeof args[number] != 'undefined' ? args[number] : match;
		});
	},

	implode: function(glue, pieces, last_glue)
	{
		var i = '',
			return_value = '',
			append_glue = '';

		last_glue = last_glue || null;

		if ( arguments.length === 1 )
		{
			pieces = glue;
			glue = '';
		}

		if ( typeof pieces === 'object' )
		{
			if ( Object.prototype.toString.call(pieces) === '[object Array]' && last_glue === null )
			{
				return pieces.join(glue);
			}

			var num_pieces = pieces.length;

			for ( i in pieces )
			{
				return_value += append_glue + pieces[i];
				append_glue = (i < (num_pieces - 2) ? glue : last_glue);
			}

			return return_value;
		}

		return pieces;
	},

	getObjectSize: function(object)
	{
		var size = 0,
			key;

		for ( key in object )
		{
			if ( object.hasOwnProperty(key) )
			{
				size++;
			}
		}

		return size;
	},

	ucfirst: function(str)
	{
		return str.charAt(0).toUpperCase() + str.slice(1);
	},

	lcfirst: function(str)
	{
		return str.charAt(0).toLowerCase() + str.slice(1);
	}
};
