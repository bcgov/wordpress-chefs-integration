<?php
/**
 * Plugin Name: WordPress CHEFS Integration
 * Plugin URI: https://github.com/bcgov/wordpress-chefs-integration
 * Author: govwordpress@gov.bc.ca
 * Author URI: https://citz-gdx.atlassian.net/browse/DESCW-3064
 * Description: WordPress CHEFS Integration Plugin is a plugin that adds integration with CHEFS API.
 * Requires at least: 6.4.4
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Version: 1.0.0
 * License: Apache License Version 2.0
 * License URI: LICENSE
 * Text Domain: WordpressChefsIntegration
 * Tags:
 *
 * @package WordpressChefsIntegration
 */

use Bcgov\WordpressChefsIntegration\HttpClient;
use Bcgov\WordpressChefsIntegration\RestController;

$local_composer = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $local_composer ) ) {
    require_once $local_composer;
}
if ( ! class_exists( 'Bcgov\\WordpressChefsIntegration\\RestController' ) ) {
	return;
}

$env                  = parse_ini_file( '.env' );
$producer_http_client = new HttpClient(
    $env['CHEFS_API_URL'],
    $env['PRODUCER_FORM_ID'],
    $env['PRODUCER_FORM_API_KEY'],
);
$producer_controller  = new RestController(
    $producer_http_client,
    'producer'
);
$producer_controller->init();
