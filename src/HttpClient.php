<?php
namespace Bcgov\WordpressChefsIntegration;

use WP_Error;

/**
 * HttpClient for making requests to CHEFS API.
 */
class HttpClient {

    /**
     * The url to the CHEFS API.
     *
     * @var string
     */
    protected $chefs_api_url;

    /**
     * The id of the form.
     *
     * @var string
     */
    protected $form_id;

    /**
     * The API key of the form.
     *
     * @var string
     */
    protected $api_key;

    /**
     * Constructor for HttpClient.
     *
     * @param string $chefs_api_url
     * @param string $form_id
     * @param string $api_key
     */
    public function __construct(
        string $chefs_api_url,
        string $form_id,
        string $api_key
    ) {
        $this->chefs_api_url = $chefs_api_url;
        $this->form_id       = $form_id;
        $this->api_key       = $api_key;
    }

    /**
     * Get submission details by submission id.
     *
     * @param string $submission_id
     * @return array|WP_Error
     */
    public function get_submission( string $submission_id ) {
        $response = $this->do_request( 'submissions/' . $submission_id );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_body = json_decode( $response['body'], true );

        return $response_body['submission']['submission'];
    }

    /**
     * Perform a request to the specified endpoint.
     *
     * @param string $endpoint
     * @param string $method
     * @param string $body
     * @param array  $headers
     * @return array|WP_Error
     */
    protected function do_request(
        string $endpoint,
        string $method = 'GET',
        string $body = '',
        array $headers = []
    ) {
        // Set up url and request arguments, merge in any extra headers.
        $url  = $this->chefs_api_url . $endpoint;
        $args = [
            'method'  => $method,
            'headers' => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . base64_encode( $this->form_id . ':' . $this->api_key ),
            ],
        ];

        $args['headers'] = array_merge( $args['headers'], $headers );

        // Perform request.
        $raw_response = wp_remote_request( $url, $args );

        return $raw_response;
    }
}
