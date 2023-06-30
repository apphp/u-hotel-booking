<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */
// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return array(
	 "base_url" => "".APPHP_BASE."modules/hybridauth/",
	 "providers" => array(
		// openid providers
		"Facebook" => array(
			"enabled" => true,
			"keys" => array("id" => "", "secret" => ""),
			"trustForwarded" => false
		),
		"Google" => array(
			"enabled" => true,
			// "keys" => array("id" => "", "secret" => ""),
			"keys" => array("id" => "", "secret" => ""),
		),
		"Twitter" => array(
			"enabled" => true,
			"keys" => array("key" => "", "secret" => ""),
			"includeEmail" => false
		),
	),
	// If you want to enable logging, set 'debug_mode' to true.
	// You can also set it to
	// - "error" To log only error messages. Useful in production
	// - "info" To log info and error messages (ignore debug messages)
	"debug_mode" => false,
	// Path to file writable by the web server. Required if 'debug_mode' is not false
	"debug_file" => "errors.log",
);
