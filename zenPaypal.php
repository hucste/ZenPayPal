<?php
include(dirname(__FILE__).'/zenPaypal/class.ZenPayPal.php');
// here declarate new zenpaypal !
$zenpaypal = new ZenPayPal();
$zenpaypal_options = $zenpaypal->getOptions();
$zenpaypal_version = $zenpaypal->getPluginVersion();
/**
 * zenPaypal -- PayPal ordering support
 *
 * Provides a PayPal ordering form for image print ordering.
 *
 * Price lists can also be passed as a parameter to the zenPaypal() function. See also
 * zenPaypalPricelistFromString() for parsing a string into the pricelist array. This could be used,
 * for instance, by storing a pricelist string in the 'customdata' field of your images and then parsing and
 * passing it in the zenPaypal() call. This would give you individual pricing by image.
 *
 * @author Ebrahim Ezzy (Nimbuz) adapted as a plugin by Stephen Billard (sbillard)
 *  modified by Stephane HUC (hucste) <devs@stephane-huc.net>
 *
 * @package plugins
 *
 */
$option_interface = 'zenPaypalOptions';

$plugin_author = gettext('Ebrahim Ezzy (Nimbuz), adapted as a plugin by Stephen Billard (sbillard), modified by Stephane HUC (hucste).');
$plugin_description =  gettext('PayPal Integration');
$plugin_disable = (version_compare(PHP_VERSION, '5.0.0') != 1) ? gettext('PHP version 5 or greater is required.') : false;
$plugin_is_filter = 5|ADMIN_PLUGIN|THEME_PLUGIN;
if(!empty($zenpaypal_version)) $plugin_version = $zenpaypal_version;
$plugin_URL = 'http://zenphoto.dev.stephane-huc.net/pages/zenPaypal-Plugin';

zp_register_filter('admin_head','ZenPayPal');
zp_register_filter('theme_head','printCSS');
zp_register_filter('theme_head','printJS');

/**
 * Plugin option handling class
 *
 */
class zenPaypalOptions {

	function zenPaypalOptions() {
		
		global $zenpaypal_options;
		
		foreach($zenpaypal_options['key'] as $value) {
			setOptionDefault('zenPaypal_'.$value, $zenpaypal_options['default'][$value]);
		}
		unset($value);
		
	}


	function getOptionsSupported() {
		
		global $zenpaypal_options;
		
		$options = array();
		
		foreach($zenpaypal_options['key'] as $key => $value) {
			$title = gettext($zenpaypal_options['title'][$value]);
			$type = $zenpaypal_options['type'][$value];
			
			switch($type) {
				case 'checkbox': $option_type = OPTION_TYPE_CHECKBOX; break;
				case 'cleartext': $option_type = OPTION_TYPE_CLEARTEXT; break;
				case 'selector': 
					$option_type = OPTION_TYPE_SELECTOR; 
					
					$selections = array();
					foreach($zenpaypal_options['selections'][$value]['code'] as $key1 => $value1) {
						$title1 = gettext($zenpaypal_options['selections'][$value]['text'][$key1]);
						
						$selections[$title1] = $value1;
					}
					unset($title1, $key1, $value1);
					
				break;
				case 'textarea': 
					$option_type = OPTION_TYPE_TEXTAREA;
					$options[$title]['multilingual'] = 0;
				break;
				case 'textbox': 
					$option_type = OPTION_TYPE_TEXTBOX; 
					$options[$title]['multilingual'] = 0;
				break;
			}
			
			$options[$title]['desc'] = gettext($zenpaypal_options['desc'][$value]);
			$options[$title]['key'] = 'zenPaypal_'.$value;
			$options[$title]['type'] = $option_type;
			
			if(!empty($selections)) {
				$options[$title]['selections'] = $selections;
			}
		}
		unset($key, $value);
		
		return $options;
		
	}

 	function handleOption($option, $currentValue) {	}
}

/**
 * Place code CSS
 */
function printCSS() {
	global $zenpaypal;
	$zenpaypal->printLinkCSS();
}
/**
 * Place code JavaScript
 */
function printJS() {
	global $zenpaypal;
	$zenpaypal->printJS();
}

/**
 * Place a PayPal's button, in the script image...
 */
function zenPaypal() {
	global $zenpaypal;
	$zenpaypal->printPayPalButton();
}

/***
 *
 * Print a PayPal's Logo !
 *
 ***/
function printZenPayPalLogo() {
	global $zenpaypal;
	$zenpaypal->printPayPalLogo();
}

?>
