<?php
namespace Bcgov\WordpressChefsIntegration;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * RestController class for handling REST API endpoints.
 */
class RestController {

    /**
     * Initializes the RestController Class.
     *
     * @return void
     */
    public function init() {
        add_action( 'rest_api_init', [ $this, 'register_chefs_routes' ] );
    }

    /**
     * Registers the chefs/v1 endpoints.
     *
     * @return void
     */
    public function register_chefs_routes() {
        register_rest_route(
            'chefs/v1',
            '/producer',
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'handle_producer' ],
                'permission_callback' => [ $this, 'has_permission' ],
            ]
        );
    }

    /**
     * Callback function for handling requests to the producer endpoint.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function handle_producer( $request ) {
        return rest_ensure_response( [ 'message' => 'success' ] );
    }

    /**
     * Determines whether the user making the request has permission to access the endpoint.
     *
     * @return bool
     */
    public function has_permission() {
        return true;
    }
}
