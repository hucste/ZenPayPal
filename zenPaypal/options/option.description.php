<?php
$this->options['desc'] = array (
	'business' => 'Your PayPal User ID: currently, your email!',
	'button_size' => "To define PayPal Button's size! <em>This is not PayPal's option!</em>",

	'cbt' => 'Set the text of the Return to Merchant button on the PayPal Payment Complete page',
	'charset' => 'Sets the character encoding or the information that PayPal returns to you as a result of checkout processes initiated by the payment button.',
	'cmd' => 'How act this plugin: as cart or button [Buy Now]',
	'cpp_header_image' => "The image at the top left of the checkout page. The image's maximum size is 750 pixels wide by 90 pixels high.",
	'cpp_headerback_color' => "To define Header Page PayPal's Background Color. Code HTML Hexa without #, six characters",
	'cpp_headerborder_color' => "To define Header Page PayPal's Border Color. Code HTML Hexa without #, six characters",
	'cpp_payflow_color' => "To define Header Page PayPalFlow's Background Color. Code HTML Hexa without #, six characters",
	'cs' => 'To define Background Color Page PayPal; default is white.',
	'currency_code' => 'The currency of yours transactions: USD for USA, EUR for Europe, etc...',

	'encrypted_information' =>
		'Paste the HTML Code that PayPal returns when you create an encrypted button!<br/>'.
		'ASAP only the text with delimiter "-----BEGIN PKCS7-----" and "-----END PKCS7-----" ...<br/>'.
		'<strong style="color: #900">This is an alpha development - try if you want, at your perils ;-)</strong>',

	'image_url' => 'The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the PayPal checkout pages.',
	'item_desc' => "To define order of informations in PayPal's Description. <em>This is not PayPal's option!</em>",

	'no_note' => 'To permit the buyer to add a comment.',
	'no_shipping' => 'To invite the buyer to type a shipping address.',

	'page_style' =>
		'The custom payment page style for checkout pages.<br/>'.
		'<em>The default is primary if you added a custom payment page style to your account profile. Otherwise, the default is paypal</em>'.
		'<br/>See your PayPal account profile.',
	'page_style_name' => 'The custom payment page style from your PayPal account profile that has the specified name.',
	'pricelist' =>
		"<strong>Your pricelist!</strong><br />This option's format is <em>price elements</em>, ".
		"separated by the symbole '<strong>|</strong>'. <em>([Alt Gr] + [6])</em><br/>".
		"A <em>price element</em> has this format: <em>size</em>;<em>media</em>;<em>price</em><br/>".
		"<em>media</em> is a text with or without spaces<br />".
		"<br />example: <code>4x6;Paper Matte Thin;5.75|8x10;Xtrem Glossy;20.00|11x14;Paper;15.35</code>",
	'pricelist_html_id' =>
		"The bloc DIV's IDentifiant of the price list.".'<br/><strong style="color: #900;">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",
	'pricelist_html_tag' =>
		'HTML element for the link text of the price list. Default: h3.<br/><strong style="color: #900">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",
	'pricelist_html_text' =>
		'The text of the link to displaying the table html to list objects -> price. '.
		"<br/><em>This is not PayPal's option!</em>",

    'pricelist_table_caption' => 'Title table of price list.'.'<br/><strong style="color: #900;">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",
    'pricelist_thead_media' => 'Text of header table media'.'<br/><strong style="color: #900;">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",
    'pricelist_thead_price' => 'Text of header table price'.'<br/><strong style="color: #900;">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",
    'pricelist_thead_qty' => 'Text of header table quantity'.'<br/><strong style="color: #900;">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",
    'pricelist_thead_size' => 'Text of header table size'.'<br/><strong style="color: #900;">'.
		"Only modify this if you know what's doing</strong>. <em>This is not PayPal's option!</em>",

	'quantity_max' => "Choose an index to define an item's quantity max!",

	'rm' =>
		'The FORM METHOD used to send data to the URL specified by the return variable after payment completion'.
		'<br/><em>Default: <strong>Full GET method</strong>.</em>',

	'sandbox' => "Use PayPal's Sandbox  to test zenPaypal. By default: no coche!",
	'shipping' => 'What do you charge to shipping for?',

	'tax' => 'What do you charge to tax for?',

    'text_logo_img_alt' => 'Div ZenPaypal: Text for the Image Alt PayPal. '."<em>This is not PayPal's option!</em>",
    'text_logo_title' => 'Div ZenPaypal: Title for the Logo PayPal. '."<em>This is not PayPal's option!</em>",

    'text_paypal_img_alt' => 'Div ZenPaypal: Text for PayPal alt image. '."<em>This is not PayPal's option!</em>",
    'text_paypal_label_qty' => 'Div ZenPaypal: Text for the label quantity. '."<em>This is not PayPal's option!</em>",
    'text_paypal_label_on0' => 'Div ZenPaypal: Text for the label on0. '."<em>This is not PayPal's option!</em>",
    'text_paypal_label_os0' => 'Div ZenPaypal: Text for the label os0. '."<em>This is not PayPal's option!</em>",
    'text_paypal_title' => 'Div ZenPaypal: Title for the div ZenPaypal. '."<em>This is not PayPal's option!</em>",

	'use_button_encrypted' =>
		'Do you want to try button encrypted?<br />'.
		"<strong>Need to paste PayPal's button encrypted information in the option 'PayPal: HTML Code of button encrypted'...</strong><br/>".
		'<strong style="color: #900">This is an alpha development - try if you want, at your perils ;-)</strong>',
	'use_tax_rate' => "If you desire to use tax rate, and not tax flat... ",

	'viewing_pricelist' => "Adding a table html to view a list objects -> price. <em>This is not PayPal's option!</em>",
);
?>
