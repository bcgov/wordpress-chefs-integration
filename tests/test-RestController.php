<?php

use Bcgov\WordpressChefsIntegration\RestController;
use Bcgov\WordpressChefsIntegration\HttpClient;

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
     * WordPress REST Server instance.
     *
     * @var WP_REST_Server
     */
    private $rest_server;

    /**
     * HttpClient.
     *
     * @var HttpClient
     */
    private $http_client;

    /**
     * The name of the endpoint used for testing.
     *
     * @var string
     */
    protected $endpoint = 'test';

    /**
     * Sets up unit test suite.
     *
     * @return void
     */
    public function set_up(): void {
        global $wp_rest_server;
        $wp_rest_server    = new \WP_REST_Server();
        $this->rest_server = $wp_rest_server;

        $this->http_client = $this->createMock( HttpClient::class );
        $this->controller  = new RestController( $this->http_client, $this->endpoint );
        $this->controller->init();
        do_action( 'rest_api_init' );
    }

    /**
     * Tests whether the CHEFS endpoints were successfully registered.
     *
     * @return void
     */
    public function test_register_chefs_routes() {
        $routes     = $this->rest_server->get_routes();
        $test_route = '/chefs/v1/' . $this->endpoint;
        $route      = $routes[ $test_route ][0];

        $this->assertArrayHasKey( $test_route, $routes );
        $this->assertEquals( [ 'POST' ], array_keys( $route['methods'] ) );
        $this->assertEquals( [ $this->controller, 'endpoint_callback' ], $route['callback'] );
        $this->assertEquals( [ $this->controller, 'has_permission' ], $route['permission_callback'] );
    }
}
