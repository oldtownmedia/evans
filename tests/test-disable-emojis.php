<?php
namespace evans;

class DisableEmojisTests extends \PHPUnit_Framework_TestCase {

	public function setUp() {
	    \WP_Mock::setUp();

	    require_once dirname( __FILE__ ) . '/../admin/disable-emojis.php';
	}

	public function tearDown() {
        \WP_Mock::tearDown();
    }

	public function test_disable_emojis_tinymce(){

		$this->assertEmpty( disable_emojis_tinymce( 'string' ) );
		$this->assertNotContains( disable_emojis_tinymce( 'wpemoji', array( 'tinymce', 'stuffs', 'wpemoji' ) ) );

	}



}