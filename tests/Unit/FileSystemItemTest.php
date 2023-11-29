<?php

namespace AntonioPrimera\FileSystem\Tests\Unit;

use AntonioPrimera\FileSystem\Tests\FileSystemItemTester;
use AntonioPrimera\FileSystem\Tests\TestCase;

class FileSystemItemTest extends TestCase
{
	
	/** @test */
	public function it_can_correctly_merge_path_parts_by_removing_bad_slashes()
	{
		$instance = new FileSystemItemTester('');
		
		$this->assertEquals('path/to/file', $instance->_mergePathParts('path', '/to/', 'file'));
		$this->assertEquals('path/to/file', $instance->_mergePathParts('path/', '/to/', '/file'));
		$this->assertEquals('path/to/file', $instance->_mergePathParts('path/', '/to/', '/file/'));
		$this->assertEquals('path/to/file', $instance->_mergePathParts('path\\', '/to\\', '/file\\'));
		
		$this->assertEquals('/path/to/file', $instance->_mergePathParts('/path', '\\to\\', '\\file'));
		$this->assertEquals('\\path/to/file', $instance->_mergePathParts('\\path/', '\\to\\', '\\file'));
		
		$this->assertEquals('path/to/file', $instance->_mergePathParts('path', 'to', 'file'));
		$this->assertEquals('path/to/file', $instance->_mergePathParts('path/', 'to/', '', '/', '\\', '/\\', '\\/', 'file'));
	}
}