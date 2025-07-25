<?php
namespace Bcgov\WordpressChefsIntegration;

use Bcgov\WordpressChefsIntegration\HttpClient;
use Bcgov\WordpressChefsIntegration\PostFactory;

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
     * The PostFactory instance to create WP posts.
     *
     * @var PostFactory
     */
    protected $factory;

    /**
     * The endpoint this controller is for, eg. "producer", "product".
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Constructor for RestController.
     *
     * @param HttpClient  $http_client The HttpClient instance to use to make requests to CHEFS API.
     * @param PostFactory $factory    The PostFactory instance to use to create WP posts.
     * @param string      $endpoint    The name of the WP REST API endpoint to create.
     */
    public function __construct(
        HttpClient $http_client,
        PostFactory $factory,
        string $endpoint
    ) {
        $this->http_client = $http_client;
        $this->factory     = $factory;
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
        // The subscriptionEvent must be eventSubmission, ignore any others.
        $subscription_event = $request->get_param( 'subscriptionEvent' );
        if ( 'eventSubmission' !== $subscription_event ) {
            return $this->create_error_response( 'Not eventSubmission event. Ignoring.', 200 );
        }

        // We only get a submission id in this request so we have to make a
        // request back to the CHEFS API to get the submission details.
        $submission_id = $request->get_param( 'submissionId' );
        if ( ! $submission_id ) {
            return $this->create_error_response( 'Must provide a submissionId parameter.' );
        }

        // Use the submission id to get submission details.
        $submission = $this->http_client->get_submission( $submission_id );
        if ( is_wp_error( $submission ) ) {
            return $this->create_error_response( 'Error getting submission: ' . $submission->get_error_message() );
        }

        // Create the WP post from $submission details.
        $post_id = $this->factory->create_post( $submission );
        if ( is_wp_error( $post_id ) ) {
            return $this->create_error_response( 'Error creating post: ' . $post_id->get_error_message(), 500 );
        }

        return rest_ensure_response( [ 'message' => 'Success.' ] );
    }

    /**
     * Creates an error response to return to client.
     *
     * @param string  $message
     * @param integer $status
     * @return WP_REST_Response
     */
    protected function create_error_response( string $message, int $status = 400 ): WP_REST_Response {
        $response = new WP_REST_Response(
            [ 'message' => 'Error getting submission: ' . $message ],
            $status
        );
        return rest_ensure_response( $response );
    }

    /**
     * Determines whether the user making the request has permission to access the endpoint.
     *
     * @return bool
     */
    public function has_permission() {
        // TODO: Add WP application password check.
        return true;
    }
}
