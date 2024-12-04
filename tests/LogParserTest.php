<?php

use AdminDebugTools\Plugin\Core\LogLine;
use AdminDebugTools\Plugin\Core\LogParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LogParserTest extends TestCase {

	private $logParser;

	protected function setUp(): void {
		$this->logParser = new LogParser();
	}

	public function testParseWithNoChildren() {
		$lines = array(
			'[01-Jan-2022 00:00:00 UTC] PHP Notice: Some log message',
			'[01-Jan-2022 00:00:01 UTC] PHP Warning: Another log message',
		);

		$parsed = $this->logParser->parse( $lines );

		$this->assertCount( 2, $parsed );
		$this->assertEquals( 'Some log message', $parsed[0]->get_message() );
		$this->assertEquals( 'Another log message', $parsed[1]->get_message() );
	}

	public function testParseWithChildren() {
		$lines = array(
			'[01-Jan-2022 00:00:00 UTC] PHP Notice: Some log message',
			'Child log message',
			'[01-Jan-2022 00:00:01 UTC] PHP Warning: Another log message',
		);

		$parsed = $this->logParser->parse( $lines );

		$this->assertCount( 2, $parsed );
		$this->assertEquals( 'Some log message', $parsed[0]->get_message() );
		$this->assertCount( 1, $parsed[0]->get_children() );
		$this->assertEquals( 'Child log message', $parsed[0]->get_children()[0]->get_message() );
		$this->assertEquals( 'Another log message', $parsed[1]->get_message() );
	}

	public function testParseWithChildren2() {
		$lines = array(
			'[09-Jun-2024 12:09:02 UTC] Xdebug: [Step Debug] Could not connect to debugging client. Tried: ::1:9003 (from HTTP_X_FORWARDED_FOR HTTP header), localhost:9003 (fallback through xdebug.client_host/xdebug.client_port) :-(',
			'[09-Jun-2024 12:09:03 UTC] Debug BackTrace:',
			'/var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php:300 - get()',
			'/var/www/testing-site/wp-includes/class-wp-hook.php:324 - resolve_route()',
			'/var/www/testing-site/wp-includes/plugin.php:205 - apply_filters()',
			'/var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php:1224 - apply_filters()',
			'/var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php:1063 - respond_to_request()',
			'/var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php:439 - dispatch()',
			'/var/www/testing-site/wp-includes/rest-api.php:428 - serve_request()',
			'/var/www/testing-site/wp-includes/class-wp-hook.php:324 - rest_api_loaded()',
			'/var/www/testing-site/wp-includes/class-wp-hook.php:348 - apply_filters()',
			'/var/www/testing-site/wp-includes/plugin.php:565 - do_action()',
			'/var/www/testing-site/wp-includes/class-wp.php:418 - do_action_ref_array()',
			'/var/www/testing-site/wp-includes/class-wp.php:813 - parse_request()',
			'/var/www/testing-site/wp-includes/functions.php:1336 - main()',
			'/var/www/testing-site/wp-blog-header.php:16 - wp()',
			'/var/www/testing-site/index.php:17 - require()',
			'[09-Jun-2024 12:09:03 UTC] Exception Stack Trace:',
			'#0 /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php(300): SamplePlugin\Plugin\RestApi\Controllers\SampleController->get(Object(WP_REST_Request))',
			'#1 /var/www/testing-site/wp-includes/class-wp-hook.php(324): SamplePlugin\Plugin\RestApi\RestApi->resolve_route(NULL, Object(WP_REST_Request), Array, Array)',
			'#2 /var/www/testing-site/wp-includes/plugin.php(205): WP_Hook->apply_filters(NULL, Array)',
			"#3 /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php(1224): apply_filters('rest_dispatch_r...', NULL, Object(WP_REST_Request), '/xptox/v1/dashb...', Array)",
			"#4 /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php(1063): WP_REST_Server->respond_to_request(Object(WP_REST_Request), '/xptox/v1/dashb...', Array, NULL)",
			'#5 /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php(439): WP_REST_Server->dispatch(Object(WP_REST_Request))',
			"#6 /var/www/testing-site/wp-includes/rest-api.php(428): WP_REST_Server->serve_request('/xptox/v1/dashb...')",
			'#7 /var/www/testing-site/wp-includes/class-wp-hook.php(324): rest_api_loaded(Object(WP))',
			"#8 /var/www/testing-site/wp-includes/class-wp-hook.php(348): WP_Hook->apply_filters('', Array)",
			'#9 /var/www/testing-site/wp-includes/plugin.php(565): WP_Hook->do_action(Array)',
			"#10 /var/www/testing-site/wp-includes/class-wp.php(418): do_action_ref_array('parse_request', Array)",
			"#11 /var/www/testing-site/wp-includes/class-wp.php(813): WP->parse_request('')",
			"#12 /var/www/testing-site/wp-includes/functions.php(1336): WP->main('')",
			'#13 /var/www/testing-site/wp-blog-header.php(16): wp()',
			"#14 /var/www/testing-site/index.php(17): require('/var/www...')",
			'#15 {main}',
			'[09-Jun-2024 12:09:03 UTC] PHP Warning:  Undefined property: SamplePlugin\Plugin\RestApi\Controllers\SampleController::$repository in /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/Controllers/SampleController.php on line 87',
			'[09-Jun-2024 12:09:03 UTC] PHP Stack trace:',
			'[09-Jun-2024 12:09:03 UTC] PHP   1. {main}() /var/www/testing-site/index.php:0',
			'[09-Jun-2024 12:09:03 UTC] PHP   2. require() /var/www/testing-site/index.php:17',
			'[09-Jun-2024 12:09:03 UTC] PHP   3. wp($query_vars = *uninitialized*) /var/www/testing-site/wp-blog-header.php:16',
			"[09-Jun-2024 12:09:03 UTC] PHP   4. WP->main(\$query_args = '') /var/www/testing-site/wp-includes/functions.php:1336",
			"[09-Jun-2024 12:09:03 UTC] PHP   5. WP->parse_request(\$extra_query_vars = '') /var/www/testing-site/wp-includes/class-wp.php:813",
			"[09-Jun-2024 12:09:03 UTC] PHP   6. do_action_ref_array(\$hook_name = 'parse_request', \$args = [0 => class WP { public \$public_query_vars = [...]; public \$private_query_vars = [...]; public \$extra_query_vars = [...]; public \$query_vars = [...]; public \$query_string = ''; public \$request = 'wp-json/xptox/v1/dashboard'; public \$matched_rule = '^wp-json/(.*)?'; public \$matched_query = 'rest_route=/xptox%2Fv1%2Fdashboard'; public \$did_permalink = TRUE }]) /var/www/testing-site/wp-includes/class-wp.php:418",
			"[09-Jun-2024 12:09:03 UTC] PHP   7. WP_Hook->do_action(\$args = [0 => class WP { public \$public_query_vars = [...]; public \$private_query_vars = [...]; public \$extra_query_vars = [...]; public \$query_vars = [...]; public \$query_string = ''; public \$request = 'wp-json/xptox/v1/dashboard'; public \$matched_rule = '^wp-json/(.*)?'; public \$matched_query = 'rest_route=/xptox%2Fv1%2Fdashboard'; public \$did_permalink = TRUE }]) /var/www/testing-site/wp-includes/plugin.php:565",
			"[09-Jun-2024 12:09:03 UTC] PHP   8. WP_Hook->apply_filters(\$value = '', \$args = [0 => class WP { public \$public_query_vars = [...]; public \$private_query_vars = [...]; public \$extra_query_vars = [...]; public \$query_vars = [...]; public \$query_string = ''; public \$request = 'wp-json/xptox/v1/dashboard'; public \$matched_rule = '^wp-json/(.*)?'; public \$matched_query = 'rest_route=/xptox%2Fv1%2Fdashboard'; public \$did_permalink = TRUE }]) /var/www/testing-site/wp-includes/class-wp-hook.php:348",
			"[09-Jun-2024 12:09:03 UTC] PHP   9. rest_api_loaded(class WP { public \$public_query_vars = [0 => 'm', 1 => 'p', 2 => 'posts', 3 => 'w', 4 => 'cat', 5 => 'withcomments', 6 => 'withoutcomments', 7 => 's', 8 => 'search', 9 => 'exact', 10 => 'sentence', 11 => 'calendar', 12 => 'page', 13 => 'paged', 14 => 'more', 15 => 'tb', 16 => 'pb', 17 => 'author', 18 => 'order', 19 => 'orderby', 20 => 'year', 21 => 'monthnum', 22 => 'day', 23 => 'hour', 24 => 'minute', 25 => 'second', 26 => 'name', 27 => 'category_name', 28 => 'tag', 29 => 'feed', 30 => 'author_name', 31 => 'pagename', 32 => 'page_id', 33 => 'error', 34 => 'attachment', 35 => 'attachment_id', 36 => 'subpost', 37 => 'subpost_id', 38 => 'preview', 39 => 'robots', 40 => 'favicon', 41 => 'taxonomy', 42 => 'term', 43 => 'cpage', 44 => 'post_type', 45 => 'embed', 46 => 'post_format', 47 => 'seedprod', 48 => 'rest_route', 49 => 'sitemap', 50 => 'sitemap-subtype', 51 => 'sitemap-stylesheet', 52 => 'mailpoet_page', 53 => 'xptox_page', 54 => 'xptox_page_action', 55 => 'xpttk', 56 => 'xptfi']; public \$private_query_vars = [0 => 'offset', 1 => 'posts_per_page', 2 => 'posts_per_archive_page', 3 => 'showposts', 4 => 'nopaging', 5 => 'post_type', 6 => 'post_status', 7 => 'category__in', 8 => 'category__not_in', 9 => 'category__and', 10 => 'tag__in', 11 => 'tag__not_in', 12 => 'tag__and', 13 => 'tag_slug__in', 14 => 'tag_slug__and', 15 => 'tag_id', 16 => 'post_mime_type', 17 => 'perm', 18 => 'comments_per_page', 19 => 'post__in', 20 => 'post__not_in', 21 => 'post_parent', 22 => 'post_parent__in', 23 => 'post_parent__not_in', 24 => 'title', 25 => 'fields']; public \$extra_query_vars = []; public \$query_vars = ['rest_route' => '/xptox/v1/dashboard']; public \$query_string = ''; public \$request = 'wp-json/xptox/v1/dashboard'; public \$matched_rule = '^wp-json/(.*)?'; public \$matched_query = 'rest_route=/xptox%2Fv1%2Fdashboard'; public \$did_permalink = TRUE }) /var/www/testing-site/wp-includes/class-wp-hook.php:324",
			"[09-Jun-2024 12:09:03 UTC] PHP  10. WP_REST_Server->serve_request(\$path = '/xptox/v1/dashboard') /var/www/testing-site/wp-includes/rest-api.php:428",
			"[09-Jun-2024 12:09:03 UTC] PHP  11. WP_REST_Server->dispatch(\$request = class WP_REST_Request { protected \$method = 'GET'; protected \$params = ['URL' => [...], 'GET' => [...], 'POST' => [...], 'FILES' => [...], 'JSON' => NULL, 'defaults' => [...]]; protected \$headers = ['cookie' => [...], 'accept_language' => [...], 'accept_encoding' => [...], 'referer' => [...], 'user_agent' => [...], 'x_wp_nonce' => [...], 'accept' => [...], 'connection' => [...], 'x_forwarded_proto' => [...], 'x_forwarded_host' => [...], 'x_forwarded_for' => [...], 'x_real_ip' => [...], 'host' => [...], 'content_length' => [...], 'content_type' => [...]]; protected \$body = ''; protected \$route = '/xptox/v1/dashboard'; protected \$attributes = ['methods' => [...], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [...], 'callback' => [...], 'permission_callback' => [...]]; protected \$parsed_json = TRUE; protected \$parsed_body = FALSE }) /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php:439",
			"[09-Jun-2024 12:09:03 UTC] PHP  12. WP_REST_Server->respond_to_request(\$request = class WP_REST_Request { protected \$method = 'GET'; protected \$params = ['URL' => [...], 'GET' => [...], 'POST' => [...], 'FILES' => [...], 'JSON' => NULL, 'defaults' => [...]]; protected \$headers = ['cookie' => [...], 'accept_language' => [...], 'accept_encoding' => [...], 'referer' => [...], 'user_agent' => [...], 'x_wp_nonce' => [...], 'accept' => [...], 'connection' => [...], 'x_forwarded_proto' => [...], 'x_forwarded_host' => [...], 'x_forwarded_for' => [...], 'x_real_ip' => [...], 'host' => [...], 'content_length' => [...], 'content_type' => [...]]; protected \$body = ''; protected \$route = '/xptox/v1/dashboard'; protected \$attributes = ['methods' => [...], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [...], 'callback' => [...], 'permission_callback' => [...]]; protected \$parsed_json = TRUE; protected \$parsed_body = FALSE }, \$route = '/xptox/v1/dashboard', \$handler = ['methods' => ['GET' => TRUE], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [], 'callback' => [0 => class SamplePlugin\Plugin\RestApi\RestApi { ... }, 1 => 'fallback'], 'permission_callback' => [0 => class SamplePlugin\Plugin\RestApi\RestApi { ... }, 1 => 'logged_in_and_can_access_route']], \$response = NULL) /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php:1063",
			"[09-Jun-2024 12:09:03 UTC] PHP  13. apply_filters(\$hook_name = 'rest_dispatch_request', \$value = NULL, ...\$args = variadic(class WP_REST_Request { protected \$method = 'GET'; protected \$params = ['URL' => [...], 'GET' => [...], 'POST' => [...], 'FILES' => [...], 'JSON' => NULL, 'defaults' => [...]]; protected \$headers = ['cookie' => [...], 'accept_language' => [...], 'accept_encoding' => [...], 'referer' => [...], 'user_agent' => [...], 'x_wp_nonce' => [...], 'accept' => [...], 'connection' => [...], 'x_forwarded_proto' => [...], 'x_forwarded_host' => [...], 'x_forwarded_for' => [...], 'x_real_ip' => [...], 'host' => [...], 'content_length' => [...], 'content_type' => [...]]; protected \$body = ''; protected \$route = '/xptox/v1/dashboard'; protected \$attributes = ['methods' => [...], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [...], 'callback' => [...], 'permission_callback' => [...]]; protected \$parsed_json = TRUE; protected \$parsed_body = FALSE }, '/xptox/v1/dashboard', ['methods' => ['GET' => TRUE], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [], 'callback' => [0 => class SamplePlugin\Plugin\RestApi\RestApi { ... }, 1 => 'fallback'], 'permission_callback' => [0 => class SamplePlugin\Plugin\RestApi\RestApi { ... }, 1 => 'logged_in_and_can_access_route']])) /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php:1224",
			"[09-Jun-2024 12:09:03 UTC] PHP  14. WP_Hook->apply_filters(\$value = NULL, \$args = [0 => NULL, 1 => class WP_REST_Request { protected \$method = 'GET'; protected \$params = [...]; protected \$headers = [...]; protected \$body = ''; protected \$route = '/xptox/v1/dashboard'; protected \$attributes = [...]; protected \$parsed_json = TRUE; protected \$parsed_body = FALSE }, 2 => '/xptox/v1/dashboard', 3 => ['methods' => [...], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [...], 'callback' => [...], 'permission_callback' => [...]]]) /var/www/testing-site/wp-includes/plugin.php:205",
			"[09-Jun-2024 12:09:03 UTC] PHP  15. SamplePlugin\Plugin\RestApi\RestApi->resolve_route(\$result = NULL, \$request = class WP_REST_Request { protected \$method = 'GET'; protected \$params = ['URL' => [...], 'GET' => [...], 'POST' => [...], 'FILES' => [...], 'JSON' => NULL, 'defaults' => [...]]; protected \$headers = ['cookie' => [...], 'accept_language' => [...], 'accept_encoding' => [...], 'referer' => [...], 'user_agent' => [...], 'x_wp_nonce' => [...], 'accept' => [...], 'connection' => [...], 'x_forwarded_proto' => [...], 'x_forwarded_host' => [...], 'x_forwarded_for' => [...], 'x_real_ip' => [...], 'host' => [...], 'content_length' => [...], 'content_type' => [...]]; protected \$body = ''; protected \$route = '/xptox/v1/dashboard'; protected \$attributes = ['methods' => [...], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [...], 'callback' => [...], 'permission_callback' => [...]]; protected \$parsed_json = TRUE; protected \$parsed_body = FALSE }, \$route = '/xptox/v1/dashboard', \$handler = ['methods' => ['GET' => TRUE], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [], 'callback' => [0 => class SamplePlugin\Plugin\RestApi\RestApi { ... }, 1 => 'fallback'], 'permission_callback' => [0 => class SamplePlugin\Plugin\RestApi\RestApi { ... }, 1 => 'logged_in_and_can_access_route']]) /var/www/testing-site/wp-includes/class-wp-hook.php:324",
			"[09-Jun-2024 12:09:03 UTC] PHP  16. SamplePlugin\Plugin\RestApi\Controllers\SampleController->get(class WP_REST_Request { protected \$method = 'GET'; protected \$params = ['URL' => [...], 'GET' => [...], 'POST' => [...], 'FILES' => [...], 'JSON' => NULL, 'defaults' => [...]]; protected \$headers = ['cookie' => [...], 'accept_language' => [...], 'accept_encoding' => [...], 'referer' => [...], 'user_agent' => [...], 'x_wp_nonce' => [...], 'accept' => [...], 'connection' => [...], 'x_forwarded_proto' => [...], 'x_forwarded_host' => [...], 'x_forwarded_for' => [...], 'x_real_ip' => [...], 'host' => [...], 'content_length' => [...], 'content_type' => [...]]; protected \$body = ''; protected \$route = '/xptox/v1/dashboard'; protected \$attributes = ['methods' => [...], 'accept_json' => FALSE, 'accept_raw' => FALSE, 'show_in_index' => TRUE, 'args' => [...], 'callback' => [...], 'permission_callback' => [...]]; protected \$parsed_json = TRUE; protected \$parsed_body = FALSE }) /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php:300",
			'[09-Jun-2024 12:09:03 UTC] PHP Fatal error:  Uncaught Error: Call to a member function get_entries_count() on null in /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/Controllers/SampleController.php:87',
			'Stack trace:',
			'#0 /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php(300): SamplePlugin\Plugin\RestApi\Controllers\SampleController->get(Object(WP_REST_Request))',
			'#1 /var/www/testing-site/wp-includes/class-wp-hook.php(324): SamplePlugin\Plugin\RestApi\RestApi->resolve_route(NULL, Object(WP_REST_Request), Array, Array)',
			'#2 /var/www/testing-site/wp-includes/plugin.php(205): WP_Hook->apply_filters(NULL, Array)',
			"#3 /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php(1224): apply_filters('rest_dispatch_r...', NULL, Object(WP_REST_Request), '/xptox/v1/dashb...', Array)",
			"#4 /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php(1063): WP_REST_Server->respond_to_request(Object(WP_REST_Request), '/xptox/v1/dashb...', Array, NULL)",
			'#5 /var/www/testing-site/wp-includes/rest-api/class-wp-rest-server.php(439): WP_REST_Server->dispatch(Object(WP_REST_Request))',
			"#6 /var/www/testing-site/wp-includes/rest-api.php(428): WP_REST_Server->serve_request('/xptox/v1/dashb...')",
			'#7 /var/www/testing-site/wp-includes/class-wp-hook.php(324): rest_api_loaded(Object(WP))',
			"#8 /var/www/testing-site/wp-includes/class-wp-hook.php(348): WP_Hook->apply_filters('', Array)",
			'#9 /var/www/testing-site/wp-includes/plugin.php(565): WP_Hook->do_action(Array)',
			"#10 /var/www/testing-site/wp-includes/class-wp.php(418): do_action_ref_array('parse_request', Array)",
			"#11 /var/www/testing-site/wp-includes/class-wp.php(813): WP->parse_request('')",
			"#12 /var/www/testing-site/wp-includes/functions.php(1336): WP->main('')",
			'#13 /var/www/testing-site/wp-blog-header.php(16): wp()',
			"#14 /var/www/testing-site/index.php(17): require('/var/www...')",
			'#15 {main}',
			'  thrown in /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/Controllers/SampleController.php on line 87',
			'[09-Jun-2024 12:09:03 UTC] Xdebug: [Step Debug] Could not connect to debugging client. Tried: ::1:9003 (from HTTP_X_FORWARDED_FOR HTTP header), localhost:9003 (fallback through xdebug.client_host/xdebug.client_port) :-(',
		);

		$parsed = $this->logParser->parse( $lines );

		$this->assertCount( 6, $parsed );
		$this->assertEquals( 'Xdebug: [Step Debug] Could not connect to debugging client. Tried: ::1:9003 (from HTTP_X_FORWARDED_FOR HTTP header), localhost:9003 (fallback through xdebug.client_host/xdebug.client_port) :-(', $parsed[0]->get_message() );
		$this->assertEquals( 'Uncaught Error: Call to a member function get_entries_count() on null in /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/Controllers/SampleController.php:87', $parsed[4]->get_message() );
		$this->assertFalse( $parsed[0]->has_children() );
		$this->assertCount( 15, $parsed[1]->get_children() );
		$this->assertEquals( '/var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php:300 - get()', $parsed[1]->get_children()[0]->get_message() );
		$this->assertEquals( 'Stack trace:', $parsed[4]->get_children()[0]->get_message() );
	}

	public function testParseDateWithValidDate() {
		$line = '[01-Jan-2022 00:00:00 UTC] Some log message';

		$log_line = new LogLine( $line );
		$log_line = $this->logParser->parse_date( $log_line );

		$this->assertEquals( 'Some log message', $log_line->get_message() );
		$this->assertEquals( '01-Jan-2022 00:00:00 UTC', $log_line->get_date() );
		$this->assertEquals( '1640995200', $log_line->get_timestamp() );
	}

	public function testParseDateWithInvalidDate() {
		$line = 'Invalid date format';

		$log_line = new LogLine( $line );
		$log_line = $this->logParser->parse_date( $log_line );

		$this->assertEquals( 'Invalid date format', $log_line->get_message() );
		$this->assertFalse( $log_line->has_date() );
	}

	#[DataProvider( 'providerTestParseTypeWithKnownType' )]
	public function testParseTypeWithKnownType( $line, $expectedRemainingLine, $expectedTypeLabel, $expectedType ) {
		$log_line = new LogLine( $line );
		$log_line = $this->logParser->parse_type( $log_line );

		$this->assertEquals( $expectedRemainingLine, $log_line->get_message() );
		$this->assertEquals( $expectedTypeLabel, $log_line->get_type_label() );
		$this->assertEquals( $expectedType, $log_line->get_type() );
	}

	public static function providerTestParseTypeWithKnownType() {
		return array(
			array( 'PHP Notice: Some log message', 'Some log message', 'PHP Notice', 'notice' ),
			array( 'PHP Warning: Some log message', 'Some log message', 'PHP Warning', 'warning' ),
			array( 'PHP Fatal error: Some log message', 'Some log message', 'PHP Fatal error', 'error' ),
			// Add more known types as needed
		);
	}

	public function testParseTypeWithStackTraceOrder(): void {
		$log_line = ( new LogLine( 'PHP 1. Some stack trace line' ) )
			->set_message( 'PHP 1. Some stack trace line' )
			->set_date( '01-Jan-2022 00:00:00 UTC', 0 )
			->set_type( 'trace' );

		$log_line = $this->logParser->parse_type( $log_line );

		$this->assertEquals( 'trace', $log_line->get_type() );
		$this->assertEquals( 1, $log_line->get_trace_order() );
	}

	public function testParseTypeWithUnknownType() {
		$line = 'Unknown type: Some log message';

		$log_line = new LogLine( $line );
		$log_line = $this->logParser->parse_type( $log_line );

		$this->assertEquals( 'Unknown type: Some log message', $log_line->get_message() );
		$this->assertNull( $log_line->get_type_label() );
		$this->assertEquals( 'log', $log_line->get_type() );
	}

	public function testParseChildrenWithDatetime(): void {
		$line        = '[01-Jan-2022 00:00:00 UTC] PHP Notice: Some log message';
		$prev_parsed = null;

		$log_line = ( new LogLine( $line ) )
			->set_message( 'Some log message' )
			->set_date( '01-Jan-2022 00:00:00 UTC', 0 )
			->set_type( 'notice' );

		$log_line = $this->logParser->parse_children( $log_line, $prev_parsed );

		$this->assertFalse( $log_line->is_children() );
	}

	public function testParseChildrenWithoutDatetime(): void {
		$line        = 'Child log message';
		$prev_parsed = null;

		$log_line = ( new LogLine( $line ) )
			->set_message( 'Child log message' )
			->set_type( 'log' );

		$log_line = $this->logParser->parse_children( $log_line, $prev_parsed );

		$this->assertTrue( $log_line->is_children() );
	}

	public function testParseChildrenWithStackTrace(): void {
		$log_line = ( new LogLine( 'PHP Stack trace:' ) )
			->set_message( 'PHP Stack trace:' )
			->set_date( '01-Jan-2022 00:00:00 UTC', 0 )
			->set_type( 'trace' );

		$prev_line = ( new LogLine( 'Some error message' ) )
			->set_message( 'Some error message' )
			->set_date( '01-Jan-2022 00:00:00 UTC', 0 )
			->set_type( 'error' );

		$log_line = $this->logParser->parse_children( $log_line, $prev_line );

		$this->assertTrue( $log_line->is_children() );
	}
}
