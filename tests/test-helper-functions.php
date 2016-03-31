<?php
namespace evans;

class HelperFunctionsTests extends \PHPUnit_Framework_TestCase {

	public function setUp() {
	    \WP_Mock::setUp();

	    require_once dirname( __FILE__ ) . '/../admin/helper-functions.php';
	}

	public function tearDown() {
        \WP_Mock::tearDown();
    }

	public function test_cmb_prefix(){

		$slug = 'portfolio';
		$this->assertEquals( '_cmb2_portfolio_', cmb_prefix( $slug ) );

	}

	public function test_add_loading_variables(){

		\WP_Mock::wpFunction( 'is_admin', array(
		    'return_in_order' => array(
		    	// Defer checks
		    	false, false,
		    	false, false,
		    	false, false,
		    	true, true,

		    	// Async checks
		    	false, false,
		    	false, false,
		    	false, false,
		    	true, true
		    )
		) );

		// Defer checks
		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1", add_loading_variables( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1#deferload';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1' defer='defer", add_loading_variables( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js #deferload';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js' defer='defer", add_loading_variables( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js#deferload';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js", add_loading_variables( $url ) );

		// Async checks
		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1", add_loading_variables( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1#asyncload';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1' async='async", add_loading_variables( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js #asyncload';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js' async='async", add_loading_variables( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js#asyncload';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js", add_loading_variables( $url ) );

	}

	public function test_remove_script_version(){

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js", remove_script_version( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=4.12';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js", remove_script_version( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js?ver=1.2458.78';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/js/myscript.js", remove_script_version( $url ) );

		$url = 'http://oldtownmediainc.com/wp-content/themes/otm/style/main.css?ver=4.12';
		$this->assertEquals( "http://oldtownmediainc.com/wp-content/themes/otm/style/main.css", remove_script_version( $url ) );

	}

	//public function test_otm_set_messages(){}

}