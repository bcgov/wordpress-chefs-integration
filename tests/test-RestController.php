<?php

use Bcgov\WordpressChefsIntegration\RestController;

/**
 * Unit tests for the RestController class.
 */
class RestControllerTest extends WP_UnitTestCase {

    /**
     * RestController instance.
     *
     * @var RestController
     */
    private $controller;

    /**
     * Undocumented variable
     *
     * @var WP_REST_Server
     */
    private $rest_server;

    /**
     * Sets up unit test suite.
     *
     * @return void
     */
    public function set_up(): void {
        $this->controller = new RestController();
        $this->controller->init();
        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();

        $this->rest_server = $wp_rest_server;
        do_action( 'rest_api_init', $this->rest_server );
    }

    /**
     * Tests whether the CHEFS endpoints were successfully registered.
     *
     * @return void
     */
    public function test_register_chefs_routes() {
        $routes = $this->rest_server->get_routes();
        $route  = $routes['/chefs/v1/producer'][0];

        $this->assertArrayHasKey( '/chefs/v1/producer', $routes );
        $this->assertEquals( [ 'POST' ], array_keys( $route['methods'] ) );
        $this->assertEquals( [ $this->controller, 'handle_producer' ], $route['callback'] );
        $this->assertEquals( [ $this->controller, 'has_permission' ], $route['permission_callback'] );
    }
}
