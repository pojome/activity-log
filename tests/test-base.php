<?php

class AAL_Test_Base extends WP_UnitTestCase {
	
	public function test_getinstance() {
		$this->assertInstanceOf( 'AAL_Main', AAL_Main::instance() );
	}

	public function test_create_post() {
		global $wpdb;
		
		$post = $this->factory->post->create_and_get( array(
			'post_title' => 'aal-test-create-post',
		) );
		
		$row = $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM %1$s
				WHERE `action` = \'%2$s\'
					AND `object_type` = \'%3$s\'
					AND `object_subtype` = \'%4$s\'
					AND `object_name` = \'%5$s\'
			',
			$wpdb->activity_log,
			'updated',
			'Post',
			$post->post_type,
			$post->post_title
		) );
		
		$this->assertNotEmpty( $row );
	}
	
	public function test_delete_post() {
		global $wpdb;
		
		$post = $this->factory->post->create_and_get( array(
			'post_title' => 'aal-test-delete-post',
		) );
		
		wp_delete_post( $post->ID );

		$row = $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM %1$s
				WHERE `action` = \'%2$s\'
					AND `object_type` = \'%3$s\'
					AND `object_subtype` = \'%4$s\'
					AND `object_name` = \'%5$s\'
			',
			$wpdb->activity_log,
			'deleted',
			'Post',
			$post->post_type,
			$post->post_title
		) );

		$this->assertNotEmpty( $row );
	}
	
}