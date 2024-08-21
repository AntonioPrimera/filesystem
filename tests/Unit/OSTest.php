<?php
namespace AntonioPrimera\FileSystem\Tests\Unit;

//use AntonioPrimera\FileSystem\File;
//use AntonioPrimera\FileSystem\FileSystemException;
//use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\OS;
use AntonioPrimera\FileSystem\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OSTest extends TestCase
{
	#[Test]
	public function it_can_correctly_determine_if_a_path_is_absolute_regardless_of_the_operating_system()
	{
		$this->assertTrue(OS::isAbsolutePath('/home/user'));
		$this->assertTrue(OS::isAbsolutePath('C:\\Users\\user'));
		$this->assertTrue(OS::isAbsolutePath('f:\\Users\\user'));
		
		$this->assertFalse(OS::isAbsolutePath('home/user'));
		$this->assertFalse(OS::isAbsolutePath('Users\\user'));
		$this->assertFalse(OS::isAbsolutePath('\\Users\\user'));
	}
	
	#[Test]
	public function it_can_correctly_determine_if_a_path_is_relative_regardless_of_the_operating_system()
	{
		$this->assertTrue(OS::isRelativePath('home/user'));
		$this->assertTrue(OS::isRelativePath('Users\\user'));
		$this->assertTrue(OS::isRelativePath('\\Users\\user'));	//this is a bit weird, but it's not absolute
		
		$this->assertFalse(OS::isRelativePath('/home/user'));
		$this->assertFalse(OS::isRelativePath('C:\\Users\\user'));
		$this->assertFalse(OS::isRelativePath('f:\\Users\\user'));
	}
	
	#[Test]
	public function it_can_correctly_normalize_path_separators_in_a_path_string()
	{
		$expectedPath = implode(DIRECTORY_SEPARATOR, ['relative', 'path', 'to', 'fileOrFolder']);
		
		$this->assertEquals($expectedPath, OS::normalizePathSeparators('relative/path/to/fileOrFolder'));
		$this->assertEquals($expectedPath, OS::normalizePathSeparators('relative\\path\\to\\fileOrFolder'));
		$this->assertEquals($expectedPath, OS::normalizePathSeparators('relative/path\\to/fileOrFolder'));
		$this->assertEquals($expectedPath, OS::normalizePathSeparators('relative\\path/to\\fileOrFolder'));
	}
	
	#[Test]
	public function it_can_correctly_normalize_and_clean_up_a_single_relative_path()
	{
		$expectedPath = implode(DIRECTORY_SEPARATOR, ['relative', 'path', 'to', 'fileOrFolder']);
		
		$this->assertEquals($expectedPath, OS::path('relative/path/to/fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('relative\\path\\to\\fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('relative/path\\to/fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('relative\\path/to\\fileOrFolder'));
	}
	
	#[Test]
	public function it_can_correctly_normalize_and_clean_up_multiple_relative_path_parts()
	{
		$expectedPath = implode(DIRECTORY_SEPARATOR, ['relative', 'path', 'to', 'fileOrFolder']);
		
		$this->assertEquals($expectedPath, OS::path('relative', 'path', 'to', 'fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('relative', 'path', 'to', 'fileOrFolder', ''));
		$this->assertEquals($expectedPath, OS::path('relative', 'path', 'to', 'fileOrFolder', '/'));
		$this->assertEquals($expectedPath, OS::path('relative', 'path', 'to', 'fileOrFolder', '\\'));
		$this->assertEquals($expectedPath, OS::path('relative', 'path', 'to', 'fileOrFolder', '\\', "\t \\ "));
	}
	
	#[Test]
	public function it_can_correctly_normalize_and_clean_up_a_single_absolute_path()
	{
		$expectedPath = '/' . implode(DIRECTORY_SEPARATOR, ['absolute', 'path', 'to', 'fileOrFolder']);
		
		$this->assertEquals($expectedPath, OS::path('/absolute/path/to/fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('\\absolute\\path\\to\\fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('/absolute/path\\to/fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('\\absolute\\path/to\\fileOrFolder'));
	}
	
	#[Test]
	public function it_can_correctly_normalize_and_clean_up_multiple_absolute_path_parts()
	{
		$expectedPath = '/' . implode(DIRECTORY_SEPARATOR, ['absolute', 'path', 'to', 'fileOrFolder']);
		
		$this->assertEquals($expectedPath, OS::path('/absolute', 'path', 'to', 'fileOrFolder'));
		$this->assertEquals($expectedPath, OS::path('\\absolute', 'path', 'to', 'fileOrFolder', ''));
		$this->assertEquals($expectedPath, OS::path('/absolute', 'path', 'to', 'fileOrFolder', '/'));
		$this->assertEquals($expectedPath, OS::path('\\absolute', 'path', 'to', 'fileOrFolder', '\\'));
		$this->assertEquals($expectedPath, OS::path('/absolute', 'path', 'to', 'fileOrFolder', '\\', "\t \\ "));
	}
	
	#[Test]
	public function it_can_split_a_path_into_an_array_of_path_parts()
	{
		$expectedParts = ['absolute', 'path', 'to', 'fileOrFolder'];
		
		//works with forward slashes
		$this->assertEquals($expectedParts, OS::pathParts('/absolute/path/to/fileOrFolder'));
		
		//works with backslashes
		$this->assertEquals($expectedParts, OS::pathParts('\\absolute\\path\\to\\fileOrFolder'));
		
		//works with mixed separators
		$this->assertEquals($expectedParts, OS::pathParts('/absolute/path\\to/fileOrFolder'));
		$this->assertEquals($expectedParts, OS::pathParts('\\absolute\\path/to\\fileOrFolder'));
		
		//works with several path parts
		$this->assertEquals($expectedParts, OS::pathParts('\\absolute', '\\path', 'to\\fileOrFolder'));
		
		//works with empty parts
		$this->assertEquals($expectedParts, OS::pathParts('\\absolute', '\\path', '/', '\\', '', ' ', null, 'to\\fileOrFolder'));
	}
	
	#[Test]
	public function it_can_handle_an_empty_path()
	{
		$this->assertEquals('', OS::path());
		$this->assertEquals('', OS::path(''));
		$this->assertEquals('', OS::path('', '', ''));
		$this->assertEquals('', OS::path(null));
		$this->assertEquals('', OS::path('/'));
		$this->assertEquals('', OS::path('\\'));
		
		$this->assertEquals([], OS::pathParts());
		$this->assertEquals([], OS::pathParts(''));
		$this->assertEquals([], OS::pathParts('', '', ''));
		$this->assertEquals([], OS::pathParts(null));
		$this->assertEquals([], OS::pathParts('/'));
		$this->assertEquals([], OS::pathParts('\\'));
	}
}