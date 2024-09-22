<?php
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\Tests\FileSystemItemTester;

beforeEach(function () {
	$this->sandbox = Folder::instance(setupSandboxFolder());
});

it ('can zip a file', function () {
	$zipFile = $this->sandbox->file('test1.zip');
	expect($zipFile->exists())->toBeFalse();
	
	$fileToZip = $this->sandbox->file('test.txt')->putContents('I will be zipped 1');
	$zipFile = $fileToZip->zipTo($zipFile);
	
	expect($zipFile)->toBeInstanceOf(File::class)
		->and($zipFile->exists())->toBeTrue();
});

it ('can zip a file to a zip file with the same name and the zip extension', function () {
	$fileToZip = $this->sandbox->file('test3.txt')->putContents('I will be zipped 3');
	$zipFile = $fileToZip->zip();
	
	expect($zipFile)->toBeInstanceOf(File::class)
		->and($zipFile->exists())->toBeTrue()
		->and($zipFile->name)->toBe('test3.txt.zip')
		->and($zipFile->parentFolder->path)->toBe($fileToZip->parentFolder->path);
});

it ('can unzip a file', function () {
	$fileToZip = $this->sandbox->file('test.txt')->putContents('I will be zipped 2');
	$zipFile = $fileToZip->zipTo($this->sandbox->file('test2.zip'));
	
	$unzipFolder = $zipFile->unzipTo($this->sandbox->subFolder('unzipped-2'));
	
	expect($unzipFolder)->toBeInstanceOf(Folder::class)
		->and($unzipFolder->exists())->toBeTrue()
		->and($unzipFolder->file('test.txt')->exists())->toBeTrue()
		->and($unzipFolder->file('test.txt')->contents)->toBe('I will be zipped 2');
});

it ('can unzip a file to the same folder as the zip file', function () {
	$fileToZip = $this->sandbox->file('test10.txt')->putContents('I will be zipped 10');
	$zipFile = $fileToZip->zipTo($this->sandbox->file('test11.zip'));
	$fileToZip->delete();
	
	expect($fileToZip->exists())->toBeFalse();
	
	$unzipFolder = $zipFile->unzip();
	
	expect($unzipFolder)->toBeInstanceOf(Folder::class)
		->and($unzipFolder->exists())->toBeTrue()
		->and($unzipFolder->path)->toBe($zipFile->parentFolder->path)
		->and($unzipFolder->file('test10.txt')->exists)->toBeTrue()
		->and($unzipFolder->file('test10.txt')->contents)->toBe('I will be zipped 10');
});

it ('can zip and unzip a folder', function () {
	//create the folder structure to zip
	$folderToZip = $this->sandbox->subFolder('folder-to-zip');
	$folderToZip->file('test4.txt')->putContents('I will be zipped 4');
	$folderToZip->file('test5.txt')->putContents('I will be zipped 5');
	$folderToZip->file('/sub-folder/test6.txt')->putContents('I will be zipped 6');
	
	//zip the folder with the root folder
	$zipFile = $folderToZip->zipTo($this->sandbox->file('zipped-folder-1.zip'));
	expect($zipFile)->toBeInstanceOf(File::class)
		->and($zipFile->exists())->toBeTrue();
	
	//unzip the archive with the root folder
	$unzipFolder = $zipFile->unzipTo($this->sandbox->subFolder('unzip-parent-folder-1'));
	expect($unzipFolder)->toBeInstanceOf(Folder::class)
		->and($unzipFolder->exists())->toBeTrue()
		->and($unzipFolder->subFolder('folder-to-zip')->exists())->toBeTrue()
		->and($unzipFolder->subFolder('folder-to-zip')->file('test4.txt')->exists())->toBeTrue()
		->and($unzipFolder->subFolder('folder-to-zip')->file('test4.txt')->contents)->toBe('I will be zipped 4')
		->and($unzipFolder->subFolder('folder-to-zip')->file('test5.txt')->exists())->toBeTrue()
		->and($unzipFolder->subFolder('folder-to-zip')->file('test5.txt')->contents)->toBe('I will be zipped 5')
		->and($unzipFolder->subFolder('folder-to-zip')->subFolder('sub-folder')->file('test6.txt')->exists())->toBeTrue()
		->and($unzipFolder->subFolder('folder-to-zip')->subFolder('sub-folder')->file('test6.txt')->contents)->toBe('I will be zipped 6');
	
	//zip the folder without the root folder in the archive
	$zipFile = $folderToZip->zipTo($this->sandbox->file('zipped-folder-2.zip'), false);
	expect($zipFile)->toBeInstanceOf(File::class)
		->and($zipFile->exists())->toBeTrue();
	
	//unzip the archive including the root folder
	$unzipFolder = $zipFile->unzipTo($this->sandbox->subFolder('unzip-parent-folder-2'));
	expect($unzipFolder)->toBeInstanceOf(Folder::class)
		->and($unzipFolder->exists)->toBeTrue()
		->and($unzipFolder->file('test4.txt')->exists())->toBeTrue()
		->and($unzipFolder->file('test4.txt')->contents)->toBe('I will be zipped 4')
		->and($unzipFolder->file('test5.txt')->exists())->toBeTrue()
		->and($unzipFolder->file('test5.txt')->contents)->toBe('I will be zipped 5')
		->and($unzipFolder->subFolder('sub-folder')->file('test6.txt')->exists())->toBeTrue()
		->and($unzipFolder->subFolder('sub-folder')->file('test6.txt')->contents)->toBe('I will be zipped 6');
});

it ('can zip a folder to a zip file with the same name and the zip extension', function () {
	$folderToZip = $this->sandbox->subFolder('folder-to-zip-2');
	$folderToZip->file('test7.txt')->putContents('I will be zipped 7');
	
	$zipFile = $folderToZip->zip();
	expect($zipFile)->toBeInstanceOf(File::class)
		->and($zipFile->exists())->toBeTrue()
		->and($zipFile->name)->toBe('folder-to-zip-2.zip')
		->and($zipFile->parentFolder->path)->toBe($folderToZip->parentFolder->path);
});

it ('can check whether a file is a valid zip file', function () {
	$fileToZip = $this->sandbox->file('test.txt')->putContents('I will be zipped 8');
	$zipFile = $fileToZip->zipTo($this->sandbox->file('test8.zip'));
	
	expect($zipFile->isZipArchive())->toBeTrue();
	
	//a folder is not a zip archive
	$folder = File::instance($fileToZip->parentFolder);
	expect($folder->isZipArchive())->toBeFalse();
	
	//a text file is not a zip archive
	$textFile = $this->sandbox->file('test9.txt')->putContents('I will not be zipped 9');
	expect($textFile->isZipArchive())->toBeFalse();
	
	//a non-existing file is not a zip archive
	$nonExistingFile = $this->sandbox->file('non-existing-file.txt');
	expect($nonExistingFile->isZipArchive())->toBeFalse();
});

it ('throws an exception when calling zip on an instance which is not a file or folder', function () {
	$rawFileSystemItem = new FileSystemItemTester('path/to/file');
	expect(fn () => $rawFileSystemItem->zip())->toThrow(FileSystemException::class);
});

it ('throws an exception if a non-existing folder is zipped', function () {
	$nonExistingFolder = $this->sandbox->subFolder('non-existing-folder');
	expect(fn () => $nonExistingFolder->zip())->toThrow(FileSystemException::class);
});

it ('throws an exception if a non-existing file is zipped', function () {
	$nonExistingFile = $this->sandbox->file('non-existing-file.txt');
	expect(fn () => $nonExistingFile->zip())->toThrow(FileSystemException::class);
});