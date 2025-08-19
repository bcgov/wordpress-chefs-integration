<?php

use Bcgov\WordpressChefsIntegration\PostFactory;
use Bcgov\Theme\FoodDirectory\Post\Producer;
use Bcgov\Theme\FoodDirectory\Post\Product;

/**
 * Unit tests for the PostFactory class.
 */
class PostFactoryTest extends WP_UnitTestCase {

    /**
     * Sets up unit test suite.
     *
     * @return void
     */
    public function set_up(): void {
        register_taxonomy( 'taxonomy1', 'post' );
        wp_create_term( 'term1', 'taxonomy1' );

        // TODO: Remove dependency on BCFD classes?
        $producer = new Producer();
        $producer->register_cpt();
        $product = new Product();
        $product->register_cpt();
    }

    /**
     * Tests generic post creation.
     *
     * @return void
     */
    public function test_create_post() {
        $map     = json_decode( file_get_contents( __DIR__ . '/maps/post.json' ), true );
        $factory = new PostFactory( 'post', $map );

        $submission = json_decode( file_get_contents( __DIR__ . '/submissions/post.json' ), true );
        $post_id    = $factory->create_post( $submission );

        $terms = wp_get_post_terms( $post_id, 'taxonomy1' );

        // Metadata.
        $this->assertEquals( 'value1', get_post_meta( $post_id, 'field1', true ) );
        $this->assertEquals( 2, get_post_meta( $post_id, 'field2', true ) );

        // Taxonomy terms.
        $this->assertEquals( [ 'term1' ], array_column( $terms, 'slug' ) );
    }

    /**
     * Tests BCFD Producer CPT creation.
     *
     * @return void
     */
    public function test_create_post_producer() {
        $map     = json_decode( file_get_contents( __DIR__ . '/../maps/producer.json' ), true );
        $factory = new PostFactory( 'producer', $map );

        $submission = json_decode( file_get_contents( __DIR__ . '/submissions/producer.json' ), true );
        $post_id    = $factory->create_post( $submission );

        // Post title.
        $this->assertEquals( 'Test Business', get_the_title( $post_id ) );

        // Metadata.
        $this->assertEquals( 'Test Business', get_post_meta( $post_id, 'bcfd_producer_business_name', true ) );
        $this->assertEquals( 3, get_post_meta( $post_id, 'bcfd_producer_full_time', true ) );
        // TODO: BCFD expects the value to be "yes", not 1 so this doesn't currently work.
        $this->assertEquals( 1, get_post_meta( $post_id, 'bcfd_producer_consent_terms', true ) );

        // Taxonomy terms.
        $region_terms = wp_get_post_terms( $post_id, 'product-region' );
        $this->assertEquals(
            [ 'thompson-okanagan' ],
            array_column( $region_terms, 'slug' )
        );

        $operations_terms = wp_get_post_terms( $post_id, 'producer-operations' );
        $this->assertEquals(
            [ 'indigenous-owned-operated', 'person-of-colour-owned-operated', 'women-owned-operated' ],
            array_column( $operations_terms, 'slug' )
        );
    }
}
