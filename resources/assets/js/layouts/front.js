$core = new Core();

$core.init(
{
	csrf_token: encrypted_csrf_token,
	uri:
	{
		base_url: base_url
	},
	message:
	{
		engine: new Core_UI_Message_Engine_SweetAlert()
	}
});

$core.afterDomInit();