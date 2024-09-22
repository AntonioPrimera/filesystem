<?php
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;

beforeEach(function () {
	$this->sandboxPath = setupSandboxFolder(__DIR__ . '/../Sandbox');
	setupFolderTestContext($this->sandboxPath);
});

it('can provide the name and the path', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->path)->toEqual($this->sandboxPath)
		->and($folder->name)->toEqual('Sandbox')
		->and($folder->getName())->toEqual('Sandbox');
	
});

it('can determine the parent folder', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
	expect($folder->getFolderPath())->toEqual($this->sandboxPath)
		->and($folder->folderPath)->toEqual($this->sandboxPath)
		->and($folder->getParentFolderPath())->toEqual($this->sandboxPath)
		->and($folder->parentFolder)->toEqual($this->sandboxPath)
		->and($folder->getContainingFolder())->toEqual($this->sandboxPath)
		->and($folder->containingFolder)->toEqual($this->sandboxPath);
});

it('can provide create time and modified time', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->createTime)->toEqual(filectime($this->sandboxPath))
		->and($folder->modifiedTime)->toEqual(filemtime($this->sandboxPath));
});

it('can check that it is the same as another folder instance or a string path', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->is($this->sandboxPath))->toBeTrue()
		->and($folder->is(new Folder($this->sandboxPath)))->toBeTrue()
		->and($folder->is($this->sandboxPath . '/testFolder1'))->toBeFalse();
});

it('can check if it exists', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->exists)->toBeTrue();

    $folder = new Folder($this->sandboxPath . '/nonExistingFolder');
    expect($folder->exists)->toBeFalse();
});

it('will not be determined as existing if a file name is given instead of a folder name', function () {
    $folder = new Folder($this->sandboxPath . '/test.txt');
    expect($folder->exists)->toBeFalse();
});

it('can create a folder instance', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect(Folder::instance($folder))->toBeInstanceOf(Folder::class)
		->and(Folder::instance($this->sandboxPath . '/testFolder1'))->toBeInstanceOf(Folder::class);
});

it('can create a file instance for a file inside the folder', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->file('test1.txt'))->toBeInstanceOf(File::class)
		->and($folder->file('test1.txt')->path)->toEqual($this->sandboxPath . '/testFolder1/test1.txt');
});

it('can create a file instance for a file inside a subfolder given its relative path', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->file('testFolder1_1/test1_1.txt'))->toBeInstanceOf(File::class)
		->and($folder->file('testFolder1_1/test1_1.txt')->path)->toEqual($this->sandboxPath . '/testFolder1/testFolder1_1/test1_1.txt');
});

it('can create a sub folder instance', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->subFolder('testFolder1_1'))->toBeInstanceOf(Folder::class)
		->and($folder->subFolder('testFolder1_1')->path)->toEqual($this->sandboxPath . '/testFolder1/testFolder1_1');
});

it('can return a list of all files in a folder searching recursively in its subfolders', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->getAllFiles())->toHaveCount(5)
		->and(array_diff([
			$this->sandboxPath . '/test.txt',
			$this->sandboxPath . '/testFolder1/test1.txt',
			$this->sandboxPath . '/testFolder1/test2.txt',
			$this->sandboxPath . '/testFolder1/testFolder1_1/test1_1.txt',
			$this->sandboxPath . '/testFolder1/testFolder1_1/test1_2.txt',
		], array_map(fn(File $file) => $file->path, $folder->getAllFiles())))->toBeEmpty();
});

it('can determine the relative path to a given base path', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1/testFolder1_1');
    expect($folder->relativePath($this->sandboxPath))->toEqual('testFolder1/testFolder1_1')
		->and($folder->relativePath($this->sandboxPath . '/testFolder1'))->toEqual('testFolder1_1');
});

it('can create a folder recursively', function () {
    $folder = new Folder($this->sandboxPath . '/testFolderX/testFolderX_1_1/testFolderX_1_1_1');
    expect($folder->exists)->toBeFalse();
    $folder->create();
    expect($folder->exists)->toBeTrue();
});

it('will not throw an exception if required to create an existing folder', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->exists)->toBeTrue();
    $folder->create();
    expect($folder->exists)->toBeTrue();
});

it('can rename an existing folder', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->exists)->toBeTrue()
		->and($folder->name)->toEqual('testFolder1');
	
	$folder->rename('newName');
    expect($folder->name)->toEqual('newName')
		->and($folder->path)->toEqual($this->sandboxPath . '/newName')
		->and($folder->exists)->toBeTrue();
});

it('can move the folder to a different parent folder', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->exists)->toBeTrue()
		->and($folder->name)->toEqual('testFolder1');
	
	$folder->move($this->sandboxPath . '/testFolder2');
    expect($folder->path)->toEqual($this->sandboxPath . '/testFolder2/testFolder1')
		->and($folder->exists)->toBeTrue();
});

it('will create the parent folder if it does not exist', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->exists)->toBeTrue();

    $folder->move($this->sandboxPath . '/testFolder2/testFolderX');
    expect($folder->path)->toEqual($this->sandboxPath . '/testFolder2/testFolderX/testFolder1')
		->and($folder->exists)->toBeTrue();
});

it('can overwrite an already existing folder with the same name when moving it', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->exists)->toBeTrue()
		->and($folder->name)->toEqual('testFolder1');
	
	$folder->move($this->sandboxPath . '/testFolder2', overwrite: true);
    expect($folder->path)->toEqual($this->sandboxPath . '/testFolder2/testFolder1')
		->and($folder->exists)->toBeTrue();
});

it('will throw an exception when trying to rename a non existing folder', function () {
    $folder = new Folder($this->sandboxPath . '/nonExistingFolder');
    expect($folder->exists)->toBeFalse();

    $this->expectException(FileSystemException::class);
    $folder->rename('newName');
});

it('will throw an exception when moving to a location having another folder with the same name', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->exists)->toBeTrue();
    mkdir($this->sandboxPath . '/testFolder2/testFolder1');

    $this->expectException(FileSystemException::class);
    $folder->move($this->sandboxPath . '/testFolder2');
});

it('can check if it has a file by its name', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->hasFile('test1.txt'))->toBeTrue()
		->and($folder->hasFile('nonExistingFile.txt'))->toBeFalse()
		->and($folder->hasFile('testFolder1_1/test1_1.txt'))->toBeTrue();
	
});

it('can check if it has a folder by its name', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->hasSubFolder('testFolder1'))->toBeTrue()
		->and($folder->hasSubFolder('nonExistingFolder'))->toBeFalse()
		->and($folder->hasSubFolder('testFolder1/testFolder1_1'))->toBeTrue();
	
});

it('can determine the list of file names', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->fileNames)->toHaveCount(2)
		->and(array_diff(['test1.txt', 'test2.txt'], $folder->fileNames))->toBeEmpty();
});

it('can determine the list of folder names', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->folderNames)->toHaveCount(2)
		->and(array_diff(['testFolder1', 'testFolder2'], $folder->folderNames))->toBeEmpty();
});

it('can retrieve a list of file instances for its files', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->files)->toHaveCount(2)
		->and(array_diff(['test1.txt', 'test2.txt'], array_map(fn(File $file) => $file->name, $folder->files)))->toBeEmpty();
});

it('can retrieve a list of folder instances for its folders', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->folders)->toHaveCount(2)
		->and(array_diff(['testFolder1', 'testFolder2'], array_map(fn(Folder $folder) => $folder->name, $folder->folders)))->toBeEmpty();
});

it('can check if it has a list of files', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');
    expect($folder->hasFiles(['test1.txt', 'test2.txt']))->toBeTrue()
		->and($folder->hasFiles(['test1.txt', 'test2.txt', 'nonExistingFile.txt']))->toBeFalse();
});

it('can check if it has a list of sub folders', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->hasSubFolders(['testFolder1', 'testFolder2']))->toBeTrue()
		->and($folder->hasSubFolders(['testFolder1', 'testFolder2', 'nonExistingFolder']))->toBeFalse();
});

it('can move an array of files or file instances to itself', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->files)->toHaveCount(1);

    $folder->moveFilesToSelf([
  			$this->sandboxPath . '/testFolder1/test1.txt',
  			$this->sandboxPath . '/testFolder1/test2.txt',
  			File::instance($this->sandboxPath . '/testFolder1/testFolder1_1/test1_1.txt'),
  			File::instance($this->sandboxPath . '/testFolder1/testFolder1_1/test1_2.txt'),
  		]);

    expect($folder->getFiles(fromCache: false))->toHaveCount(5)
		->and($folder->subFolder('testFolder1')->files)->toHaveCount(0)
		->and($folder->subFolder('testFolder1/testFolder1_1')->files)->toHaveCount(0)
		->and($folder->hasFiles([
			'test.txt',
			'test1.txt',
			'test2.txt',
			'test1_1.txt',
			'test1_2.txt',
		]))->toBeTrue();
	
});

it('can say if it is empty or not', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->isEmpty())->toBeFalse()
		->and($folder->isNotEmpty())->toBeTrue();
	
	$folder = new Folder($this->sandboxPath . '/testFolder2');
    expect($folder->isEmpty())->toBeTrue()
		->and($folder->isNotEmpty())->toBeFalse();
});

it('can delete an empty folder without the deep flag', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder2');
    expect($folder->exists)->toBeTrue()
		->and($folder->isEmpty())->toBeTrue();
	
	$folder->delete();
    expect($folder->exists)->toBeFalse();
});

it('can recursively delete a folder and its contents with the deep flag', function () {
    $folder = new Folder($this->sandboxPath);
    expect($folder->exists)->toBeTrue()
		->and($folder->isEmpty())->toBeFalse();
	
	$folder->delete(deep: true);
    expect($folder->exists)->toBeFalse();
});

it('will not do anything when trying to delete a non-existing folder', function () {
	$folder = new Folder($this->sandboxPath . '/nonExistingFolder');
	expect($folder->exists)->toBeFalse()
		->and($folder->delete())->toBeInstanceOf(Folder::class);
});

it('can retrieve files based on a given filter', function () {
    file_put_contents($this->sandboxPath . '/testFolder1/test3.json', '{}');
    $folder = new Folder($this->sandboxPath . '/testFolder1');

    //no filter
    expect($folder->getFiles())->toHaveCount(3)
		
		//filter is given as a closure
		->and($folder->getFiles(fn($fileName) => str_ends_with($fileName, '.json')))->toHaveCount(1)
		->and($folder->getFiles(fn($fileName) => str_ends_with($fileName, '.txt')))->toHaveCount(2)
		
		//filter is given as a regex
		->and($folder->getFiles('/\.json$/'))->toHaveCount(1)
		->and($folder->getFiles('/\.txt$/'))->toHaveCount(2);
});

it('can retrieve folders based on a given filter', function () {
    mkdir($this->sandboxPath . '/testFolder1/testFolder1_1_x');
    mkdir($this->sandboxPath . '/testFolder1/testFolder1_3');
    $folder = new Folder($this->sandboxPath . '/testFolder1');

    //no filter
    expect($folder->getFolders())->toHaveCount(3)
		
		//filter is given as a closure
		->and($folder->getFolders(fn($folderName) => str_contains($folderName, '1_1')))->toHaveCount(2)
		->and($folder->getFolders(fn($folderName) => str_ends_with($folderName, '_x')))->toHaveCount(1)
		
		//filter is given as a regex
		->and($folder->getFolders('/1_1/'))->toHaveCount(2)
		->and($folder->getFolders('/_x$/'))->toHaveCount(1);
});

it('can retrieve files from cache', function () {
    $folder = new Folder($this->sandboxPath . '/testFolder1');

    //cache is empty, so it will read the files from disk
    expect($folder->getFiles(fromCache: true))->toHaveCount(2);

    //new file is added, but the cache is not updated
    touch($this->sandboxPath . '/testFolder1/test3.txt');
    expect($folder->getFiles(fromCache: true))->toHaveCount(2)
		
		//when reading files from disk, the cache is updated
		->and($folder->getFiles(fromCache: false))->toHaveCount(3)
		->and($folder->getFiles(fromCache: true))->toHaveCount(3);
});

it('can retrieve folders from cache', function () {
    $folder = new Folder($this->sandboxPath);

    //cache is empty, so it will read the folders from disk
    expect($folder->getFolders(fromCache: true))->toHaveCount(2);

    //new folder is added, but the cache is not updated
    mkdir($this->sandboxPath . '/testFolder3');
    expect($folder->getFolders(fromCache: true))->toHaveCount(2)
		
		//when reading folders from disk, the cache is updated
		->and($folder->getFolders(fromCache: false))->toHaveCount(3)
		->and($folder->getFolders(fromCache: true))->toHaveCount(3);
});

//--- Test context ------------------------------------------------------------------------------------------------

function setupFolderTestContext(string $contextPath) : void
{
    file_put_contents($contextPath . '/test.txt', 'test');
    mkdir($contextPath . '/testFolder1');
    mkdir($contextPath . '/testFolder2');

    file_put_contents($contextPath . '/testFolder1/test1.txt', 'test1');
    file_put_contents($contextPath . '/testFolder1/test2.txt', 'test2');

    mkdir($contextPath . '/testFolder1/testFolder1_1');
    file_put_contents($contextPath . '/testFolder1/testFolder1_1/test1_1.txt', 'test1_1');
    file_put_contents($contextPath . '/testFolder1/testFolder1_1/test1_2.txt', 'test1_2');
	
	expect($contextPath)->toBeDirectory()
		->and($contextPath . '/test.txt')->toBeFile()
		->and($contextPath . '/testFolder1')->toBeDirectory()
		->and($contextPath . '/testFolder2')->toBeDirectory()
		->and($contextPath . '/testFolder1/test1.txt')->toBeFile()
		->and($contextPath . '/testFolder1/test2.txt')->toBeFile()
		->and($contextPath . '/testFolder1/testFolder1_1')->toBeDirectory()
		->and($contextPath . '/testFolder1/testFolder1_1/test1_1.txt')->toBeFile()
		->and($contextPath . '/testFolder1/testFolder1_1/test1_2.txt')->toBeFile();
	
}