function Core_Form() {}

Core_Form.prototype =
{
	file_upload: null,

	init: function()
	{
		this.file_upload = new Core_Form_File_Upload();
	}
};