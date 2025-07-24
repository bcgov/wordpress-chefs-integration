<?php
namespace Bcgov\WordpressChefsIntegration;

use Bcgov\WordpressChefsIntegration\HttpClient;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * RestController class for handling REST API endpoints.
 */
class RestController {

    /**
     * The HttpClient to make the requests.
     *
     * @var HttpClient
     */
    protected $http_client;

    /**
     * The endpoint this controller is for, eg. "producer", "product".
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Constructor for RestController.
     *
     * @param HttpClient $http_client The HttpClient instance to use to make requests to CHEFS API.
     * @param string     $endpoint    The name of the WP REST API endpoint to create.
     */
    public function __construct(
        HttpClient $http_client,
        string $endpoint
    ) {
        $this->http_client = $http_client;
        $this->endpoint    = $endpoint;
    }

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
            '/' . $this->endpoint,
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'endpoint_callback' ],
                'permission_callback' => [ $this, 'has_permission' ],
            ]
        );
    }

    /**
     * Callback function for handling requests to the endpoint.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function endpoint_callback( WP_REST_Request $request ) {
        $subscription_event = $request->get_param( 'subscriptionEvent' );
        if ( 'eventSubmission' !== $subscription_event ) {
            return rest_ensure_response( [ 'message' => 'Not eventSubmission event. Ignoring.' ] );
        }

        $submission_id = $request->get_param( 'submissionId' );
        if ( ! $submission_id ) {
            $response = new WP_REST_Response(
                [ 'message' => 'Must provide a submissionId parameter.' ],
                400
            );
            return rest_ensure_response( $response );
        }

        $submission = $this->http_client->get_submission( $submission_id );
        if ( is_wp_error( $submission ) ) {
            $response = new WP_REST_Response(
                [ 'message' => 'Error getting submission: ' . $submission->get_error_message() ],
                400
            );
            return rest_ensure_response( $response );
        }

        return rest_ensure_response( [ 'message' => 'Success.' ] );
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
