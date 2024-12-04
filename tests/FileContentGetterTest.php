<?php

use AdminDebugTools\Plugin\Core\FileContentGetter;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FileContentGetterTest extends TestCase {

	public function testGetLastLines() {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\n" )
			->at( $root );

		// Create a new SplFileObject for the virtual file
		$splFileObject = new SplFileObject( $file->url() );

		// Now you can pass the SplFileObject to your class
		$fileContentGetter = new FileContentGetter( $splFileObject );

		// Perform your tests...
		$lastLines = $fileContentGetter->get_last_lines( 3 );
		$this->assertEquals(
			array(
				3 => "Line 3\n",
				4 => "Line 4\n",
				5 => "Line 5\n",
			),
			$lastLines->get_lines()
		);
	}

	public function testGetLastLinesFromEndline() {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\n" )
			->at( $root );

		// Create a new SplFileObject for the virtual file
		$splFileObject = new SplFileObject( $file->url() );

		// Now you can pass the SplFileObject to your class
		$fileContentGetter = new FileContentGetter( $splFileObject );

		// Perform your tests...
		$lastLines = $fileContentGetter->get_last_lines( 3, 4 );
		$this->assertEquals(
			array(
				2 => "Line 2\n",
				3 => "Line 3\n",
				4 => "Line 4\n",
			),
			$lastLines->get_lines()
		);
	}

	public function testGetFromLine() {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\n" )
			->at( $root );

		// Create a new SplFileObject for the virtual file
		$splFileObject = new SplFileObject( $file->url() );

		// Now you can pass the SplFileObject to your class
		$fileContentGetter = new FileContentGetter( $splFileObject );

		// Perform your tests...
		$lastLines = $fileContentGetter->get_from_line( 3 );
		$this->assertEquals(
			array(
				4 => "Line 4\n",
				5 => "Line 5\n",
			),
			$lastLines->get_lines()
		);
	}

	public function testGetFromLineWithEndLine() {
		// Create a virtual file system
		$root = vfsStream::setup();

		// Create a virtual file with some content
		$file = vfsStream::newFile( 'test.txt' )
			->withContent( "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\n" )
			->at( $root );

		// Create a new SplFileObject for the virtual file
		$splFileObject = new SplFileObject( $file->url() );

		// Now you can pass the SplFileObject to your class
		$fileContentGetter = new FileContentGetter( $splFileObject );

		// Perform your tests...
		$lastLines = $fileContentGetter->get_from_line( 3, 4 );
		$this->assertEquals(
			array(
				4 => "Line 4\n",
			),
			$lastLines->get_lines()
		);
	}
}
