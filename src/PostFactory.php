<?php
namespace Bcgov\WordpressChefsIntegration;

use WP_Error;

/**
 * Creates WP posts of a given type using CHEFS submission data and a mapping
 * of CHEFS field ids to WP metadata/taxonomy ids.
 */
class PostFactory {

    /**
     * The post type to create.
     *
     * @var string
     */
    protected $post_type;

    /**
     * The mapping array between CHEFS fields and WP metadata/taxonomies.
     *
     * @var array
     */
    protected $map;

    /**
     * Constructor for PostFactory.
     *
     * @param string $post_type
     * @param array  $map
     */
    public function __construct(
        string $post_type,
        array $map
    ) {
        $this->post_type = $post_type;
        $this->map       = $map;
    }

    /**
     * Create a WP post from the given CHEFS submission data.
     *
     * @param array $submission
     * @return int|WP_Error
     */
    public function create_post(
        array $submission
    ) {
        $post_array = $this->parse_submission( $submission );

        $post_id = wp_insert_post(
            $post_array,
            true
        );

        // Terms can't be set in wp_insert_post, so set them after.
        if ( ! is_wp_error( $post_id ) ) {
            foreach ( $post_array['tax_input'] as $taxonomy => $terms ) {
                wp_set_object_terms( $post_id, $terms, $taxonomy );
            }
        }

        return $post_id;
    }

    /**
     * Parses CHEFS submission data into a WP post array that can be passed into
     * wp_insert_post().
     *
     * @param array $submission
     * @return array
     */
    protected function parse_submission( array $submission ): array {
        $post_array = [
            'post_type'    => $this->post_type,
            'post_title'   => 'placeholder',
            // TODO: Allow these to be set using submission data.
            'post_content' => 'placeholder',
            'post_excerpt' => 'placeholder',
            // TODO: Set this to a defined user, service account?
            'post_author'  => 1,
        ];
        $meta_input = [];
        $tax_input  = [];
        foreach ( $submission['data'] as $field => $value ) {
            $field_mapping = $this->map[ $field ] ?? null;
            if ( ! $field_mapping ) {
                continue;
            }
            $field_type = $field_mapping['type'] ?? 'meta_input';
            $field_name = $field_mapping['name'];
            if ( 'meta_input' === $field_type ) {
                $meta_input[ $field_name ] = $value;

                // Sets post title to this value if the map has is_title flag.
                if ( $field_mapping['is_title'] ?? null ) {
                    $post_array['post_title'] = $value;
                }
            } elseif ( 'tax_input' === $field_type ) {
                $tax_input[ $field_name ] = $this->extract_taxonomy_value( $value );
			}
        }

        $post_array['meta_input'] = $meta_input;
        $post_array['tax_input']  = $tax_input;

        return $post_array;
    }

    /**
     * Extracts values(s) from CHEFS value for taxonomy terms.
     *
     * @param mixed $value Raw value of CHEFS field.
     * @return array Array of term slugs.
     */
    protected function extract_taxonomy_value( $value ): array {
        if ( empty( $value ) ) {
            $term_slugs = [];
        } elseif ( is_array( $value ) ) {
            if ( is_string( array_keys( $value )[0] ) ) {
                /**
                 * CHEFS checkbox inputs come in a format like:
                 * "checkboxInput": {"option1": true, "option2": false ...}
                 * So we have to get the keys which have a true value.
                 * Note: CHEFS can't fetch checkbox values from an external source.
                 */
                $term_slugs = array_keys(
                    array_filter(
                        $value,
                        function ( $value ) {
                            return true === $value;
                        }
                    )
                );
            } else {
                /**
                 * CHEFS multi-select inputs come as an array of slugs (or ids possibly).
                 * Note: CHEFS CAN fetch select values from an external source, eg. WP REST API
                 * for taxonomy terms.
                 */
                $term_slugs = $value;
            }
        } else {
            // If the CHEFS field value is not an array, make it one because
            // WP taxonomy terms must be arrays.
            $term_slugs = [ $value ];
        }

        return $term_slugs;
    }
}
