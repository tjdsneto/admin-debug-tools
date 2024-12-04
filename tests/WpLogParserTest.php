<?php

use AdminDebugTools\Plugin\Core\WpLogParser;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class WpLogParserTest extends TestCase {



	private $file_mock;
	private $wpLogParser;
	private $wp_filesystem_mock;

	protected function setUp(): void {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( file_get_contents( ADBTL_PLUGIN_DIR . '/tests/data/debug.log' ) )
			->at( $root );

		$this->file_mock = $this->getMockBuilder( \SplFileObject::class )
			->setConstructorArgs( array( $file->url() ) ) // Pass constructor arguments if needed
			->getMock();

		// Mock WP_Filesystem_Base
		$this->wp_filesystem_mock = $this->getMockBuilder( stdClass::class )
			->disableOriginalConstructor()
			->addMethods( array( 'put_contents' ) )
			->getMock();

		// Define the global wp_filesystem
		global $wp_filesystem;
		$wp_filesystem = $this->wp_filesystem_mock;

		$this->wpLogParser = new WpLogParser( $this->file_mock );

		$this->updateExpectedDebugJson();
	}

	public static function setUpBeforeClass(): void {
		// This code runs once before all test methods in the class
		self::updateExpectedDebugJson();
	}

	public static function updateExpectedDebugJson() {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( file_get_contents( ADBTL_PLUGIN_DIR . '/tests/data/debug.log' ) )
			->at( $root );

		// Create a new SplFileObject for the virtual file
		$splFileObject = new SplFileObject( $file->url() );

		// Now you can pass the SplFileObject to your class
		$wp_log = new WpLogParser( $splFileObject );

		$content_set = $wp_log->get_from_line( 0 );

		$jsonContent = json_encode( $content_set, JSON_PRETTY_PRINT );
		file_put_contents( ADBTL_PLUGIN_DIR . '/tests/data/debug_expected.json', $jsonContent );
	}

	public function testClearWithoutBackup() {
		$this->file_mock->expects( $this->once() )
			->method( 'getPathname' )
			->willReturn( '/tmp/test_logfile.log' );

		// Set up expectations for put_contents
		$this->wp_filesystem_mock->expects( $this->once() )
			->method( 'put_contents' )
			->with(
				$this->equalTo( '/tmp/test_logfile.log' ),
				$this->equalTo( '' )
			)
			->willReturn( true );

		$this->wpLogParser->clear( false );

		$this->assertFileExists( '/tmp/test_logfile.log' );
		$this->assertEquals( 0, filesize( '/tmp/test_logfile.log' ) );
	}

	public function testClearWithBackup() {
		$this->file_mock->expects( $this->exactly( 2 ) )
			->method( 'getPathname' )
			->willReturn( '/tmp/test_logfile.log' );

		// Set up expectations for put_contents
		$this->wp_filesystem_mock->expects( $this->once() )
			->method( 'put_contents' )
			->with(
				$this->equalTo( '/tmp/test_logfile.log' ),
				$this->equalTo( '' )
			)
			->willReturn( true );

		$this->wpLogParser->clear( true );

		$backupPath = '/tmp/test_logfile_' . ( new \DateTime() )->format( 'Ymd_His' ) . '.log';
		$this->assertFileExists( $backupPath );
		$this->assertFileExists( '/tmp/test_logfile.log' );
		$this->assertEquals( 0, filesize( '/tmp/test_logfile.log' ) );
	}

	public function testCreateBackupThrowsException() {
		$this->file_mock->expects( $this->once() )
			->method( 'getPathname' )
			->willReturn( 'invalid/path/to/logfile.log' );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Unable to create backup file.' );

		$this->wpLogParser->clear( true );
	}

	public function testGetFromLine() {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( file_get_contents( ADBTL_PLUGIN_DIR . '/tests/data/debug.log' ) )
			->at( $root );

		// Create a new SplFileObject for the virtual file
		$splFileObject = new SplFileObject( $file->url() );

		// Now you can pass the SplFileObject to your class
		$wp_log = new WpLogParser( $splFileObject );

		$line_collection = $wp_log->get_from_line( 0 );

		$error_log = $line_collection->get( 1 );

		$this->assertTrue( $error_log->has_children() );
		$this->assertEquals( 15, count( $error_log->get_children() ) );
		$this->assertEquals( 'log', $error_log->get_children()[0]->get_type() );
		$this->assertEquals( '{{fileLink}}get()', $error_log->get_children()[0]->get_message() );

		$error_log = $line_collection->get( 2 );

		$this->assertTrue( $error_log->has_children() );
		$this->assertEquals( 16, count( $error_log->get_children() ) );
		$this->assertEquals( 'trace', $error_log->get_children()[0]->get_type() );
		$this->assertEquals( 'SamplePlugin\Plugin\RestApi\Controllers\SampleController->get(Object(WP_REST_Request))', $error_log->get_children()[0]->get_message() );
	}
}
