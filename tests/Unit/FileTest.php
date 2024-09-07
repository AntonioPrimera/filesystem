<?php
namespace AntonioPrimera\FileSystem\Tests\Unit;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FileTest extends TestCase
{
	
	#[Test]
	public function it_can_create_a_file_instance()
	{
		$file = new File($this->contextPath . '/test.txt');
		$file2 = File::instance($this->contextPath . '/test.txt');
		
		$this->assertTrue($file->is($file2));
	}
	
	#[Test]
	public function it_can_determine_the_name()
	{
		$file = new File($this->contextPath . '/test.ext1.ext2.txt');
		$this->assertEquals('test.ext1.ext2.txt', $file->name);
		$this->assertEquals('test.ext1.ext2', $file->nameWithoutExtension);
		$this->assertEquals('txt', $file->extension);
		
		$this->assertEquals('test.ext1', $file->getNameWithoutExtension(2));
		$this->assertEquals('test', $file->getNameWithoutExtension(3));
		$this->assertEquals('test', $file->getNameWithoutExtension(4));
		
		$this->assertEquals('txt', $file->getExtension(1));
		$this->assertEquals('ext2.txt', $file->getExtension(2));
		$this->assertEquals('ext1.ext2.txt', $file->getExtension(3));
		$this->assertEquals('ext1.ext2.txt', $file->getExtension(4));
	}
	
	#[Test]
	public function it_can_determine_the_folder()
	{
		$file = new File($this->contextPath . '/testFolder1/test1.txt');
		$this->assertEquals($this->contextPath . '/testFolder1', $file->folderPath);
		$this->assertEquals($this->contextPath . '/testFolder1', $file->folder->path);
	}
	
	#[Test]
	public function it_can_determine_the_relative_path_to_a_given_base_path()
	{
		$file = new File($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt');
		$this->assertEquals('testFolder1/testFolder1_1/test1_1.txt', $file->relativePath($this->contextPath));
		$this->assertEquals('testFolder1_1/test1_1.txt', $file->relativePath($this->contextPath . '/testFolder1'));
		$this->assertEquals('test1_1.txt', $file->relativePath($this->contextPath . '/testFolder1/testFolder1_1'));
	}
	
	#[Test]
	public function it_can_determine_the_relative_folder_path_to_a_given_base_path()
	{
		$file = new File($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt');
		$this->assertEquals('testFolder1/testFolder1_1', $file->relativeFolderPath($this->contextPath));
		$this->assertEquals('testFolder1_1', $file->relativeFolderPath($this->contextPath . '/testFolder1'));
	}
	
	#[Test]
	public function it_can_determine_if_it_exists()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertTrue($file->exists);
		
		$file = new File($this->contextPath . '/non-existing-file.txt');
		$this->assertFalse($file->exists);
	}
	
	#[Test]
	public function it_can_determine_create_and_change_time()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertIsInt($file->createTime);
		$this->assertIsInt($file->modifiedTime);
		
		$this->assertEquals(filectime($this->contextPath . '/test.txt'), $file->createTime);
		$this->assertEquals(filemtime($this->contextPath . '/test.txt'), $file->modifiedTime);
	}
	
	#[Test]
	public function it_can_check_if_name_matches_regex()
	{
		$file = new File($this->contextPath . '/test.ext1.ext2.txt');
		
		$this->assertTrue($file->nameMatches('/(test)(.*)/'));
		$this->assertTrue($file->nameMatches('/^test/'));
		$this->assertTrue($file->nameMatches('/txt$/'));
		$this->assertTrue($file->nameMatches('/^test\.ext1\.ext2\.txt$/'));
	}
	
	#[Test]
	public function it_can_return_the_matches_of_a_regex_match_check()
	{
		$file = new File($this->contextPath . '/test.ext1.ext2.txt');
		$this->assertEquals(['test.ext1.ext2.txt', 'test', '.ext1.ext2.txt'], $file->nameMatchParts('/(test)(.*)/'));
		$this->assertEquals(['test.ext1.ext2.txt', 'test', '.ext1.ext2.txt'], $file->nameMatchParts('/(^test)(.*)/'));
		$this->assertEquals(['test.ext1.ext2.txt', 'test.ext1.ext2.', 'txt'], $file->nameMatchParts('/(.*)(txt$)/'));
		$this->assertEquals(['test.ext1.ext2.txt', 'test', '.ext1.ext2', '.txt'], $file->nameMatchParts('/(^test)(.*)(\.txt$)/'));
	}
	
	#[Test]
	public function it_can_return_its_contents()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertEquals('test', $file->contents);
		$this->assertEquals('test', $file->getContents());
	}
	
	#[Test]
	public function it_can_rename_a_file()
	{
		$file = new File($this->contextPath . '/test.txt');
		$file->rename('new-name.abc');
		
		$this->assertEquals('new-name.abc', $file->name);
	}
	
	#[Test]
	public function it_can_rename_a_file_preserving_its_simple_extension()
	{
		$file = new File($this->contextPath . '/test.txt');
		$file->rename('new-name', true);
		
		$this->assertEquals('new-name.txt', $file->name);
	}
	
	#[Test]
	public function it_can_rename_a_file_preserving_its_complex_extension()
	{
		$file = new File($this->contextPath . '/test.ext1.ext2.txt');
		$file->rename('new-name', true, 2);
		
		$this->assertEquals('new-name.ext2.txt', $file->name);
		
		$file->rename('new-name-2', true, 10);
		
		$this->assertEquals('new-name-2.ext2.txt', $file->name);
	}
	
	#[Test]
	public function it_can_move_the_file_to_a_different_folder_path_preserving_its_name()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertTrue(file_exists($this->contextPath . '/test.txt'));
		$this->assertFalse(file_exists($this->contextPath . '/testFolder1/test.txt'));
		
		$file->moveTo($this->contextPath . '/testFolder1');
		
		$this->assertEquals($this->contextPath . '/testFolder1/test.txt', $file->path);
		$this->assertFalse(file_exists($this->contextPath . '/test.txt'));
	}
	
	#[Test]
	public function it_can_move_the_file_to_a_different_folder_given_the_folder_instance()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertTrue(file_exists($this->contextPath . '/test.txt'));
		$this->assertFalse(file_exists($this->contextPath . '/testFolder1/test.txt'));
		
		$file->moveTo(new Folder($this->contextPath . '/testFolder1'));
		
		$this->assertEquals($this->contextPath . '/testFolder1/test.txt', $file->path);
		$this->assertFalse(file_exists($this->contextPath . '/test.txt'));
	}
	
	#[Test]
	public function it_can_delete_a_file()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertTrue(file_exists($this->contextPath . '/test.txt'));
		
		$file->delete();
		
		$this->assertFalse(file_exists($this->contextPath . '/test.txt'));
	}
	
	#[Test]
	public function it_can_put_contents_into_a_file()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertEquals('test', file_get_contents($this->contextPath . '/test.txt'));
		
		$file->putContents('new contents');
		
		$this->assertEquals('new contents', file_get_contents($this->contextPath . '/test.txt'));
	}
	
	#[Test]
	public function it_can_copy_the_contents_from_another_file_instance()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertEquals('test', file_get_contents($this->contextPath . '/test.txt'));
		
		$source = new File($this->contextPath . '/testFolder1/test1.txt');
		$this->assertEquals('test1', file_get_contents($this->contextPath . '/testFolder1/test1.txt'));
		
		$file->copyContentsFromFile($source);
		
		$this->assertEquals('test1', file_get_contents($this->contextPath . '/test.txt'));
	}
	
	#[Test]
	public function it_can_copy_the_contents_from_another_file_given_its_string_path()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertEquals('test', file_get_contents($this->contextPath . '/test.txt'));
		
		$file->copyContentsFromFile($this->contextPath . '/testFolder1/test1.txt');
		
		$this->assertEquals('test1', file_get_contents($this->contextPath . '/test.txt'));
	}
	
	#[Test]
	public function it_can_copy_its_contents_to_another_file_instance()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertEquals('test', file_get_contents($this->contextPath . '/test.txt'));
		
		$destination = new File($this->contextPath . '/testFolder1/test1.txt');
		$this->assertEquals('test1', file_get_contents($this->contextPath . '/testFolder1/test1.txt'));
		
		$file->copyContentsToFile($destination);
		
		$this->assertEquals('test', file_get_contents($this->contextPath . '/testFolder1/test1.txt'));
	}
	
	#[Test]
	public function it_can_copy_its_contents_to_another_file_given_its_string_path()
	{
		$file = new File($this->contextPath . '/test.txt');
		$this->assertEquals('test', file_get_contents($this->contextPath . '/test.txt'));
		
		$file->copyContentsToFile($this->contextPath . '/testFolder1/test1.txt');
		
		$this->assertEquals('test', file_get_contents($this->contextPath . '/testFolder1/test1.txt'));
	}
	
	#[Test]
	public function it_can_replace_string_parts_in_its_contents()
	{
		$file = new File($this->contextPath . '/replace-test.txt');
		$this->assertEquals('test1 test2 test3', file_get_contents($this->contextPath . '/replace-test.txt'));
		
		$file->replaceInFile(['test1' => 'new1', 'test2' => 'new2']);
		
		$this->assertEquals('new1 new2 test3', file_get_contents($this->contextPath . '/replace-test.txt'));
	}
	
	//--- Exception tests ---------------------------------------------------------------------------------------------
	
	#[Test]
	public function creating_a_file_instance_for_a_non_existent_file_does_not_throw_an_exception()
	{
		$file = new File($this->contextPath . '/non-existent-file.txt');
		$this->assertFalse($file->exists);
	}
	
	#[Test]
	public function reading_contents_of_a_non_readable_file_throws_an_exception()
	{
		$file = new File($this->contextPath . '/non-readable-file.txt');
		$this->expectException(FileSystemException::class);
		$file->getContents();
	}
	
	#[Test]
	public function renaming_a_non_existing_file_throws_an_exception()
	{
		$file = new File($this->contextPath . '/non-existent-file.txt');
		$this->expectException(FileSystemException::class);
		$file->rename('new-name.txt');
	}
	
	#[Test]
	public function renaming_a_file_to_an_existing_file_name_throws_an_exception()
	{
		file_put_contents($this->contextPath . '/test2.txt', 'test');
		$file = new File($this->contextPath . '/test.txt');
		$this->expectException(FileSystemException::class);
		$file->rename('test2.txt');
	}
	
	#[Test]
	public function moving_a_file_to_a_non_existent_directory_throws_an_exception()
	{
		$this->expectException(\Exception::class);
		$file = new File($this->contextPath . '/test.txt');
		$file->moveTo($this->contextPath . '/non-existent-directory');
	}
	
	#[Test]
	public function it_throws_an_exception_if_moving_to_a_folder_where_a_file_with_the_same_name_already_exists()
	{
		file_put_contents($this->contextPath . '/testFolder1/test.txt', 'test');
		$file = new File($this->contextPath . '/test.txt');
		
		$this->expectException(FileSystemException::class);
		$file->moveTo($this->contextPath . '/testFolder1');
	}
	
	#[Test]
	public function it_overwrites_an_existing_file_by_moving_it_if_requested()
	{
		file_put_contents($this->contextPath . '/testFolder1/test.txt', 'will be overwritten');
		$file = new File($this->contextPath . '/test.txt');
		
		$file->moveTo($this->contextPath . '/testFolder1', true);
		
		$this->assertEquals('test', file_get_contents($this->contextPath . '/testFolder1/test.txt'));
	}
	
	#[Test]
	public function it_can_copy_a_file_to_another_file()
	{
		$file = File::instance($this->contextPath . '/test.txt')->putContents('abc');
		$targetFilePath = $this->contextPath . '/new-sub-folder/test-copy.txt';
		$copiedFile = $file->clone()->copy($targetFilePath);
		
		//$this->assertTrue(file_exists($this->contextPath . '/test.txt'));
		$this->assertEquals($targetFilePath, $copiedFile->path);
		$this->assertEquals('abc', $file->contents);
		$this->assertTrue($copiedFile->exists);
		$this->assertEquals('abc', $copiedFile->contents);
	}
	
	#[Test]
	public function it_creates_a_backup_of_an_existing_file()
	{
		$file = File::instance($this->contextPath . '/test.txt')->putContents('abc');
		$backupFile = $file->clone()->backup();
		
		$this->assertTrue($file->path === $this->contextPath . '/test.txt');
		$this->assertTrue($file->exists);
		$this->assertTrue($file->contains('abc'));
		
		$this->assertEquals($this->contextPath . '/test.txt.backup', $backupFile->path);
		$this->assertTrue($backupFile->exists);
		$this->assertTrue($backupFile->contains('abc'));
	}
	
	#[Test]
	public function it_generates_a_unique_backup_file_name_if_a_backup_file_already_exists()
	{
		$file = File::instance($this->contextPath . '/test.txt')->putContents('abc');
		$backupFile = $file->clone()->backup();
		
		$this->assertTrue($backupFile->path === $this->contextPath . '/test.txt.backup');
		$this->assertTrue($backupFile->exists && $backupFile->contains('abc'));
		
		$file->putContents('def');
		$backupFile001 = $file->clone()->backup();
		
		$this->assertTrue($backupFile->exists && $backupFile->contains('abc'));
		
		$this->assertEquals($this->contextPath . '/test.txt.001.backup', $backupFile001->path);
		$this->assertTrue($backupFile001->exists && $backupFile001->contains('def'));
		
		$file->putContents('ghi');
		$backupFile002 = $file->clone()->backup();
		
		$this->assertTrue($backupFile->exists && $backupFile->contains('abc'));
		$this->assertTrue($backupFile001->exists && $backupFile001->contains('def'));
		
		$this->assertEquals($this->contextPath . '/test.txt.002.backup', $backupFile002->path);
		$this->assertTrue($backupFile002->exists && $backupFile002->contains('ghi'));
	}
	
	#[Test]
	public function it_can_create_a_new_empty_file()
	{
		$file = new File($this->contextPath . '/new-file.txt');
		$this->assertFalse($file->exists);
		
		$file->create();
		
		$this->assertTrue($file->exists);
		$this->assertEquals('', file_get_contents($this->contextPath . '/new-file.txt'));
	}
	
	#[Test]
	public function it_can_create_a_new_file_using_touch()
	{
		$file = new File($this->contextPath . '/new-file.txt');
		$this->assertFalse($file->exists);
		
		$file->touch();
		
		$this->assertTrue($file->exists);
		$this->assertEquals('', file_get_contents($this->contextPath . '/new-file.txt'));
	}
	
	#[Test]
	public function it_can_clone_a_file_instance_using_the_clone_function()
	{
		$file = new File($this->contextPath . '/test.txt');
		$clone = $file->clone();
		
		$this->assertTrue($file->path === $clone->path);
		$this->assertFalse($file === $clone);
	}
	
	//--- Test context ------------------------------------------------------------------------------------------------
	
	protected function setupTestContext(): void
	{
		file_put_contents($this->contextPath . '/test.txt', 'test');
		file_put_contents($this->contextPath . '/test.ext1.ext2.txt', 'test with multiple extensions');
		mkdir($this->contextPath . '/testFolder1');
		mkdir($this->contextPath . '/testFolder2');
		
		file_put_contents($this->contextPath . '/testFolder1/test1.txt', 'test1');
		file_put_contents($this->contextPath . '/testFolder1/test2.txt', 'test2');
		
		mkdir($this->contextPath . '/testFolder1/testFolder1_1');
		file_put_contents($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt', 'test1_1');
		
		file_put_contents($this->contextPath . '/replace-test.txt', 'test1 test2 test3');
	}
	
	#[Test]
	public function the_context_setup_is_created_successfully()
	{
		$this->assertDirectoryExists($this->contextPath);
		$this->assertDirectoryExists($this->contextPath . '/testFolder1');
		$this->assertDirectoryExists($this->contextPath . '/testFolder2');
		
		$this->assertFileExists($this->contextPath . '/test.txt');
		$this->assertFileExists($this->contextPath . '/test.ext1.ext2.txt');
		$this->assertFileExists($this->contextPath . '/testFolder1/test1.txt');
		$this->assertFileExists($this->contextPath . '/testFolder1/test2.txt');
		$this->assertFileExists($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt');
		
		$this->assertEquals('test', file_get_contents($this->contextPath . '/test.txt'));
		$this->assertEquals('test with multiple extensions', file_get_contents($this->contextPath . '/test.ext1.ext2.txt'));
		$this->assertEquals('test1', file_get_contents($this->contextPath . '/testFolder1/test1.txt'));
		$this->assertEquals('test2', file_get_contents($this->contextPath . '/testFolder1/test2.txt'));
		$this->assertEquals('test1_1', file_get_contents($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt'));
		
		$this->assertEquals('test1 test2 test3', file_get_contents($this->contextPath . '/replace-test.txt'));
	}
}