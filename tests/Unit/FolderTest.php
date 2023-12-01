<?php

namespace AntonioPrimera\FileSystem\Tests\Unit;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\Tests\TestCase;

class FolderTest extends TestCase
{
	/** @test */
	public function it_can_provide_the_name_and_the_path()
	{
		$folder = new Folder($this->contextPath);
		$this->assertEquals($this->contextPath, $folder->path);
		
		$this->assertEquals('Context', $folder->name);
		$this->assertEquals('Context', $folder->getName());
	}
	
	/** @test */
	public function it_can_determine_the_parent_folder()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertEquals($this->contextPath, $folder->folderPath);
	}
	
	/** @test */
	public function it_can_provide_create_time_and_modified_time()
	{
		$folder = new Folder($this->contextPath);
		$this->assertEquals(filectime($this->contextPath), $folder->createTime);
		$this->assertEquals(filemtime($this->contextPath), $folder->modifiedTime);
	}
	
	/** @test */
	public function it_can_check_that_it_is_the_same_as_another_folder_instance_or_a_string_path()
	{
		$folder = new Folder($this->contextPath);
		$this->assertTrue($folder->is($this->contextPath));
		$this->assertTrue($folder->is(new Folder($this->contextPath)));
		$this->assertFalse($folder->is($this->contextPath . '/testFolder1'));
	}
	
	/** @test */
	public function it_can_check_if_it_exists()
	{
		$folder = new Folder($this->contextPath);
		$this->assertTrue($folder->exists);
		
		$folder = new Folder($this->contextPath . '/nonExistingFolder');
		$this->assertFalse($folder->exists);
	}
	
	/** @test */
	public function it_will_not_be_determined_as_existing_if_a_file_name_is_given_instead_of_a_folder_name()
	{
		$folder = new Folder($this->contextPath . '/test.txt');
		$this->assertFalse($folder->exists);
	}
	
	/** @test */
	public function it_can_create_a_folder_instance()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertInstanceOf(Folder::class, Folder::instance($folder));
		$this->assertInstanceOf(Folder::class, Folder::instance($this->contextPath . '/testFolder1'));
	}
	
	/** @test */
	public function it_can_create_a_file_instance_for_a_file_inside_the_folder()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertInstanceOf(File::class, $folder->file('test1.txt'));
		$this->assertEquals($this->contextPath . '/testFolder1/test1.txt', $folder->file('test1.txt')->path);
	}
	
	/** @test */
	public function it_can_create_a_file_instance_for_a_file_inside_a_subfolder_given_its_relative_path()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertInstanceOf(File::class, $folder->file('testFolder1_1/test1_1.txt'));
		$this->assertEquals($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt', $folder->file('testFolder1_1/test1_1.txt')->path);
	}
	
	/** @test */
	public function it_can_create_a_sub_folder_instance()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertInstanceOf(Folder::class, $folder->subFolder('testFolder1_1'));
		$this->assertEquals($this->contextPath . '/testFolder1/testFolder1_1', $folder->subFolder('testFolder1_1')->path);
	}
	
	/** @test */
	public function it_can_return_a_list_of_all_files_in_a_folder_searching_recursively_in_its_subfolders()
	{
		$folder = new Folder($this->contextPath);
		$this->assertCount(5, $folder->allFiles());
		$this->assertEmpty(array_diff([
			$this->contextPath . '/test.txt',
			$this->contextPath . '/testFolder1/test1.txt',
			$this->contextPath . '/testFolder1/test2.txt',
			$this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt',
			$this->contextPath . '/testFolder1/testFolder1_1/test1_2.txt',
		], array_map(fn (File $file) => $file->path, $folder->allFiles())));
	}
	
	/** @test */
	public function it_can_determine_the_relative_path_to_a_given_base_path()
	{
		$folder = new Folder($this->contextPath . '/testFolder1/testFolder1_1');
		$this->assertEquals('testFolder1/testFolder1_1', $folder->relativePath($this->contextPath));
		$this->assertEquals('testFolder1_1', $folder->relativePath($this->contextPath . '/testFolder1'));
	}
	
	//--- Folder operations -------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_create_a_folder_recursively()
	{
		$folder = new Folder($this->contextPath . '/testFolderX/testFolderX_1_1/testFolderX_1_1_1');
		$this->assertFalse($folder->exists);
		$folder->create();
		$this->assertTrue($folder->exists);
	}
	
	/** @test */
	public function it_will_not_throw_an_exception_if_required_to_create_an_existing_folder()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->exists);
		$folder->create();
		$this->assertTrue($folder->exists);
	}
	
	/** @test */
	public function it_can_rename_an_existing_folder()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->exists);
		$this->assertEquals('testFolder1', $folder->name);
		
		$folder->rename('newName');
		$this->assertEquals('newName', $folder->name);
		$this->assertEquals($this->contextPath . '/newName', $folder->path);
		$this->assertTrue($folder->exists);
	}
	
	/** @test */
	public function it_can_move_the_folder_to_a_different_parent_folder()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->exists);
		$this->assertEquals('testFolder1', $folder->name);
		
		$folder->move($this->contextPath . '/testFolder2');
		$this->assertEquals($this->contextPath . '/testFolder2/testFolder1', $folder->path);
		$this->assertTrue($folder->exists);
	}
	
	/** @test */
	public function it_will_create_the_parent_folder_if_it_does_not_exist()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->exists);
		
		$folder->move($this->contextPath . '/testFolder2/testFolderX');
		$this->assertEquals($this->contextPath . '/testFolder2/testFolderX/testFolder1', $folder->path);
		$this->assertTrue($folder->exists);
	}
	
	/** @test */
	public function it_can_overwrite_an_already_existing_folder_with_the_same_name_when_moving_it()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->exists);
		$this->assertEquals('testFolder1', $folder->name);
		
		$folder->move($this->contextPath . '/testFolder2', overwrite: true);
		$this->assertEquals($this->contextPath . '/testFolder2/testFolder1', $folder->path);
		$this->assertTrue($folder->exists);
	}
	
	//--- Exceptions --------------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_will_throw_an_exception_when_trying_to_rename_a_non_existing_folder()
	{
		$folder = new Folder($this->contextPath . '/nonExistingFolder');
		$this->assertFalse($folder->exists);
		
		$this->expectException(FileSystemException::class);
		$folder->rename('newName');
	}
	
	/** @test */
	public function it_will_throw_an_exception_when_moving_to_a_location_having_another_folder_with_the_same_name()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->exists);
		mkdir($this->contextPath . '/testFolder2/testFolder1');
		
		$this->expectException(FileSystemException::class);
		$folder->move($this->contextPath . '/testFolder2');
	}
	
	//--- Folder contents ---------------------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_check_if_it_has_a_file_by_its_name()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->hasFile('test1.txt'));
		$this->assertFalse($folder->hasFile('nonExistingFile.txt'));
		
		$this->assertTrue($folder->hasFile('testFolder1_1/test1_1.txt'));
	}
	
	/** @test */
	public function it_can_check_if_it_has_a_folder_by_its_name()
	{
		$folder = new Folder($this->contextPath);
		$this->assertTrue($folder->hasSubFolder('testFolder1'));
		$this->assertFalse($folder->hasSubFolder('nonExistingFolder'));
		
		$this->assertTrue($folder->hasSubFolder('testFolder1/testFolder1_1'));
	}
	
	/** @test */
	public function it_can_determine_the_list_of_file_names()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertCount(2, $folder->fileNames);
		$this->assertEmpty(array_diff(['test1.txt', 'test2.txt'], $folder->fileNames));
	}
	
	/** @test */
	public function it_can_determine_the_list_of_folder_names()
	{
		$folder = new Folder($this->contextPath);
		$this->assertCount(2, $folder->folderNames);
		$this->assertEmpty(array_diff(['testFolder1', 'testFolder2'], $folder->folderNames));
	}
	
	/** @test */
	public function it_can_retrieve_a_list_of_file_instances_for_its_files()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertCount(2, $folder->files);
		$this->assertEmpty(array_diff(['test1.txt', 'test2.txt'], array_map(fn (File $file) => $file->name, $folder->files)));
	}
	
	/** @test */
	public function it_can_retrieve_a_list_of_folder_instances_for_its_folders()
	{
		$folder = new Folder($this->contextPath);
		$this->assertCount(2, $folder->folders);
		$this->assertEmpty(array_diff(['testFolder1', 'testFolder2'], array_map(fn (Folder $folder) => $folder->name, $folder->folders)));
	}
	
	/** @test */
	public function it_can_check_if_it_has_a_list_of_files()
	{
		$folder = new Folder($this->contextPath . '/testFolder1');
		$this->assertTrue($folder->hasFiles(['test1.txt', 'test2.txt']));
		$this->assertFalse($folder->hasFiles(['test1.txt', 'test2.txt', 'nonExistingFile.txt']));
	}
	
	/** @test */
	public function it_can_check_if_it_has_a_list_of_sub_folders()
	{
		$folder = new Folder($this->contextPath);
		$this->assertTrue($folder->hasSubFolders(['testFolder1', 'testFolder2']));
		$this->assertFalse($folder->hasSubFolders(['testFolder1', 'testFolder2', 'nonExistingFolder']));
	}
	
	/** @test */
	public function it_can_move_an_array_of_files_or_file_instances_to_itself()
	{
		$folder = new Folder($this->contextPath);
		$this->assertCount(1, $folder->files);
		
		$folder->moveFilesToSelf([
			$this->contextPath . '/testFolder1/test1.txt',
			$this->contextPath . '/testFolder1/test2.txt',
			File::instance($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt'),
			File::instance($this->contextPath . '/testFolder1/testFolder1_1/test1_2.txt'),
		]);
		
		$this->assertCount(5, $folder->getFiles(fromCache: false));
		$this->assertCount(0, $folder->subFolder('testFolder1')->files);
		$this->assertCount(0, $folder->subFolder('testFolder1/testFolder1_1')->files);
		
		$this->assertTrue($folder->hasFiles([
			'test.txt',
			'test1.txt',
			'test2.txt',
			'test1_1.txt',
			'test1_2.txt',
		]));
	}
	
	//--- Folder contents and deletion --------------------------------------------------------------------------------
	
	/** @test */
	public function it_can_say_if_it_is_empty_or_not()
	{
		$folder = new Folder($this->contextPath);
		$this->assertFalse($folder->isEmpty());
		$this->assertTrue($folder->isNotEmpty());
		
		$folder = new Folder($this->contextPath . '/testFolder2');
		$this->assertTrue($folder->isEmpty());
		$this->assertFalse($folder->isNotEmpty());
	}
	
	/** @test */
	public function it_can_delete_an_empty_folder_without_the_deep_flag()
	{
		$folder = new Folder($this->contextPath . '/testFolder2');
		$this->assertTrue($folder->exists);
		$this->assertTrue($folder->isEmpty());
		
		$folder->delete();
		$this->assertFalse($folder->exists);
	}
	
	/** @test */
	public function it_can_recursively_delete_a_folder_and_its_contents_with_the_deep_flag()
	{
		$folder = new Folder($this->contextPath);
		$this->assertTrue($folder->exists);
		$this->assertFalse($folder->isEmpty());
		
		$folder->delete(deep: true);
		$this->assertFalse($folder->exists);
	}
	
	//--- Retrieving filtered files and folders -----------------------------------------------------------------------
	
	/** @test */
	public function it_can_retrieve_files_based_on_a_given_filter()
	{
		file_put_contents($this->contextPath . '/testFolder1/test3.json', '{}');
		$folder = new Folder($this->contextPath . '/testFolder1');
		
		//no filter
		$this->assertCount(3, $folder->getFiles());
		
		//filter is given as a closure
		$this->assertCount(1, $folder->getFiles(fn ($fileName) => str_ends_with($fileName, '.json')));
		$this->assertCount(2, $folder->getFiles(fn ($fileName) => str_ends_with($fileName, '.txt')));
		
		//filter is given as a regex
		$this->assertCount(1, $folder->getFiles('/\.json$/'));
		$this->assertCount(2, $folder->getFiles('/\.txt$/'));
	}
	
	/** @test */
	public function it_can_retrieve_folders_based_on_a_given_filter()
	{
		mkdir($this->contextPath . '/testFolder1/testFolder1_1_x');
		mkdir($this->contextPath . '/testFolder1/testFolder1_3');
		$folder = new Folder($this->contextPath . '/testFolder1');
		
		//no filter
		$this->assertCount(3, $folder->getFolders());
		
		//filter is given as a closure
		$this->assertCount(2, $folder->getFolders(fn ($folderName) => str_contains($folderName, '1_1')));
		$this->assertCount(1, $folder->getFolders(fn ($folderName) => str_ends_with($folderName, '_x')));
		
		//filter is given as a regex
		$this->assertCount(2, $folder->getFolders('/1_1/'));
		$this->assertCount(1, $folder->getFolders('/_x$/'));
	}
	
	//--- Test context ------------------------------------------------------------------------------------------------
	
	protected function setupTestContext(): void
	{
		file_put_contents($this->contextPath . '/test.txt', 'test');
		mkdir($this->contextPath . '/testFolder1');
		mkdir($this->contextPath . '/testFolder2');
		
		file_put_contents($this->contextPath . '/testFolder1/test1.txt', 'test1');
		file_put_contents($this->contextPath . '/testFolder1/test2.txt', 'test2');
		
		mkdir($this->contextPath . '/testFolder1/testFolder1_1');
		file_put_contents($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt', 'test1_1');
		file_put_contents($this->contextPath . '/testFolder1/testFolder1_1/test1_2.txt', 'test1_2');
	}
	
	/** @test */
	public function the_context_setup_is_created_successfully()
	{
		$this->assertDirectoryExists($this->contextPath);
		$this->assertFileExists($this->contextPath . '/test.txt');
		$this->assertDirectoryExists($this->contextPath . '/testFolder1');
		$this->assertDirectoryExists($this->contextPath . '/testFolder2');
		
		$this->assertFileExists($this->contextPath . '/testFolder1/test1.txt');
		$this->assertFileExists($this->contextPath . '/testFolder1/test2.txt');
		
		$this->assertDirectoryExists($this->contextPath . '/testFolder1/testFolder1_1');
		$this->assertFileExists($this->contextPath . '/testFolder1/testFolder1_1/test1_1.txt');
		$this->assertFileExists($this->contextPath . '/testFolder1/testFolder1_1/test1_2.txt');
	}
}