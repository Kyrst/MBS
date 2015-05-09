<?php
if ( count($js_variables) > 0 )
{
	$html = '<script>';

	foreach ( $js_variables as $js_variable_name => $js_variable_value )
	{
		if ( is_array($js_variable_value) )
		{
			$html .= 'var ' . $js_variable_name . ' = ' . json_encode($js_variable_value) . ';' . PHP_EOL;
		}
		else
		{
			if ( is_bool($js_variable_value) )
			{
				$html .= 'var ' . $js_variable_name . ' = ' . ($js_variable_value === TRUE ? 'true' : 'false') . ';' . PHP_EOL;
			}
			else
			{
				$html .= 'var ' . $js_variable_name . ' = ' . ($js_variable_value !== NULL ? (is_numeric($js_variable_value) ? $js_variable_value : '\'' . $js_variable_value . '\'') : 'null') . ';' . PHP_EOL;
			}
		}
	}

	$html .= '</script>';

	echo \JShrink\Minifier::minify($html);
}