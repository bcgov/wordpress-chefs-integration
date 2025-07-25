<?php

use Bcgov\WordpressChefsIntegration\RestController;
use Bcgov\WordpressChefsIntegration\HttpClient;
use Bcgov\WordpressChefsIntegration\PostFactory;

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
     * PostFactory.
     *
     * @var PostFactory
     */
    protected $factory;

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
        $this->factory     = $this->createMock( PostFactory::class );
        $this->controller  = new RestController( $this->http_client, $this->factory, $this->endpoint );
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

    /**
     * Tests the POST endpoint callback.
     *
     * @dataProvider provider_test_post
     * @param object         $request The request from CHEFS' event subscription service.
     * @param array|WP_Error $chefs_response The response to our request to CHEFS API.
     * @param object         $expects The assertions we are making about the tests.
     * @return void
     */
    public function test_post( object $request, $chefs_response, object $expects ) {
        $this->http_client
            ->expects( $this->exactly( $expects->http_client_times ) )
            ->method( 'get_submission' )
            ->with( $request->body->submissionId ?? null )
            ->willReturn( $chefs_response );

        $this->factory
            ->expects( $this->exactly( $expects->factory_times ) )
            ->method( 'create_post' );

        $response = $this->send_request( $request );

        $this->assertSame( $expects->status, $response->get_status() );
    }

    /**
     * Data provider for test_post().
     *
     * @return array
     */
    public function provider_test_post() {
        return [
            'Success'                 => [
                'request'        => (object) [
                    'method' => 'POST',
                    'body'   => (object) [
                        'formId'            => 'c78ef40a-ad9c-4f81-8c38-bf4ff0187f38',
                        'formVersion'       => 'a3dbd953-bffd-44ea-ab1c-5e09c9dcbb5f',
                        'subscriptionEvent' => 'eventSubmission',
                        'submissionId'      => 'a94984b1-7c7c-4cbd-8a55-10fda3cd9319',
                    ],
                ],
                'chefs_response' => [
                    'test' => 'test',
                ],
                'expects'        => (object) [
                    'http_client_times' => 1,
                    'factory_times'     => 1,
                    'status'            => 200,
                ],
            ],
            'Wrong subscriptionEvent' => [
                'request'        => (object) [
                    'method' => 'POST',
                    'body'   => (object) [
                        'formId'            => 'c78ef40a-ad9c-4f81-8c38-bf4ff0187f38',
                        'formVersion'       => 'a3dbd953-bffd-44ea-ab1c-5e09c9dcbb5f',
                        'subscriptionEvent' => 'notEventSubmission',
                        'submissionId'      => 'a94984b1-7c7c-4cbd-8a55-10fda3cd9319',
                    ],
                ],
                'chefs_response' => [
                    'test' => 'test',
                ],
                'expects'        => (object) [
                    'http_client_times' => 0,
                    'factory_times'     => 0,
                    'status'            => 200,
                ],
            ],
            'Missing submissionId'    => [
                'request'        => (object) [
                    'method' => 'POST',
                    'body'   => (object) [
                        'formId'            => 'c78ef40a-ad9c-4f81-8c38-bf4ff0187f38',
                        'formVersion'       => 'a3dbd953-bffd-44ea-ab1c-5e09c9dcbb5f',
                        'subscriptionEvent' => 'eventSubmission',
                    ],
                ],
                'chefs_response' => [
                    'test' => 'test',
                ],
                'expects'        => (object) [
                    'http_client_times' => 0,
                    'factory_times'     => 0,
                    'status'            => 400,
                ],
            ],
            'Error from CHEFS API'    => [
                'request'        => (object) [
                    'method' => 'POST',
                    'body'   => (object) [
                        'formId'            => 'c78ef40a-ad9c-4f81-8c38-bf4ff0187f38',
                        'formVersion'       => 'a3dbd953-bffd-44ea-ab1c-5e09c9dcbb5f',
                        'subscriptionEvent' => 'eventSubmission',
                        'submissionId'      => 'a94984b1-7c7c-4cbd-8a55-10fda3cd9319',
                    ],
                ],
                'chefs_response' => new WP_Error( 'error' ),
                'expects'        => (object) [
                    'http_client_times' => 1,
                    'factory_times'     => 0,
                    'status'            => 400,
                ],
            ],
        ];
    }

    /**
     * Helper function for sending a request to our API endpoint.
     *
     * @param object $request_file
     * @return WP_REST_Response|WP_Error
     */
    private function send_request( object $request_file ) {
        $request = new WP_REST_Request( $request_file->method, '/chefs/v1/' . $this->endpoint );
        $request->set_body( wp_json_encode( $request_file->body ) );
        $request->add_header( 'Content-Type', 'application/json' );

        // TODO: Make actual request instead of faking it through the callback.
        // return rest_get_server()->dispatch($request);.
        return $this->controller->endpoint_callback( $request );
    }
}
