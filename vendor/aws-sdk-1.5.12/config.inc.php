<?php if (!class_exists('CFRuntime')) die('No direct access allowed.');
/**
 * Stores your AWS account information. Add your account information, and then rename this file
 * to 'config.inc.php'.
 *
 * @version 2011.12.14
 * @license See the included NOTICE.md file for more information.
 * @copyright See the included NOTICE.md file for more information.
 * @link http://aws.amazon.com/php/ PHP Developer Center
 * @link http://aws.amazon.com/security-credentials AWS Security Credentials
 */


/*###################################################################################################

	As of version 1.5, the AWS SDK for PHP uses the CFCredentials class to handle credentials.
	This class has the advantage of being able to support multiple sets of credentials at a time,
	including the ability for credential sets to inherit settings from other credential sets.

	Some example uses are noted at https://gist.github.com/1478912

	Notes:

	* You can define one or more credential sets.

	* Credential sets can be named anything that PHP allows for an associative array key;
	  "production", "staging", etc., are just sample values. Feel free to rename them.

	* A credential set only has four required entries: key, secret, default_cache_config and
	  certificate_authority. Aside from these, you can add any additional bits of information
	  you'd like to keep easily accessible (e.g., multi-factor authentication device key, your
	  AWS Account ID, your canonical identifiers).

	* Additional credential sets can inherit the properties of another credential set using the
	  @inherit keyword.

	* If more than one credential set is provided, a default credential set must be specified
	  using the @default keyword.

	* If you only have one credential set, you can set it to the @default keyword.

	* View the documentation for the CFCredentials::set() method to view usage examples.

###################################################################################################*/
