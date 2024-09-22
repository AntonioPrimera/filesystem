<?php
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;

beforeEach(function () {
	$this->sandboxPath = setupSandboxFolder();
	$this->sandbox = new Folder($this->sandboxPath);
	setupFileTestContext($this->sandboxPath);
});

it('can create a file instance', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    $file2 = File::instance($this->sandboxPath . '/test.txt');

    expect($file->is($file2))->toBeTrue();
});

it('can determine the name', function () {
    $file = new File($this->sandboxPath . '/test.ext1.ext2.txt');
    expect($file->name)->toEqual('test.ext1.ext2.txt')
		->and($file->nameWithoutExtension)->toEqual('test.ext1.ext2')
		->and($file->extension)->toEqual('txt')
		->and($file->getNameWithoutExtension(2))->toEqual('test.ext1')
		->and($file->getNameWithoutExtension(3))->toEqual('test')
		->and($file->getNameWithoutExtension(4))->toEqual('test')
		->and($file->getExtension(1))->toEqual('txt')
		->and($file->getExtension(2))->toEqual('ext2.txt')
		->and($file->getExtension(3))->toEqual('ext1.ext2.txt')
		->and($file->getExtension(4))->toEqual('ext1.ext2.txt');
	
});

it('can determine the folder', function () {
    $file = new File($this->sandboxPath . '/testFolder1/test1.txt');
    expect($file->folderPath)->toEqual($this->sandboxPath . '/testFolder1')
		->and($file->folder->path)->toEqual($this->sandboxPath . '/testFolder1');
});

it('can determine the relative path to a given base path', function () {
    $file = new File($this->sandboxPath . '/testFolder1/testFolder1_1/test1_1.txt');
    expect($file->relativePath($this->sandboxPath))->toEqual('testFolder1/testFolder1_1/test1_1.txt')
		->and($file->relativePath($this->sandboxPath . '/testFolder1'))->toEqual('testFolder1_1/test1_1.txt')
		->and($file->relativePath($this->sandboxPath . '/testFolder1/testFolder1_1'))->toEqual('test1_1.txt');
});

it('can determine the relative folder path to a given base path', function () {
    $file = new File($this->sandboxPath . '/testFolder1/testFolder1_1/test1_1.txt');
    expect($file->relativeFolderPath($this->sandboxPath))->toEqual('testFolder1/testFolder1_1')
		->and($file->relativeFolderPath($this->sandboxPath . '/testFolder1'))->toEqual('testFolder1_1');
});

it('can determine if it exists', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect($file->exists)->toBeTrue();

    $file = new File($this->sandboxPath . '/non-existing-file.txt');
    expect($file->exists)->toBeFalse();
});

it('can determine create and change time', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect($file->createTime)->toBeInt()
		->and($file->modifiedTime)->toBeInt()
		->and($file->createTime)->toEqual(filectime($this->sandboxPath . '/test.txt'))
		->and($file->modifiedTime)->toEqual(filemtime($this->sandboxPath . '/test.txt'));
	
});

it('can check if name matches regex', function () {
    $file = new File($this->sandboxPath . '/test.ext1.ext2.txt');

    expect($file->nameMatches('/(test)(.*)/'))->toBeTrue()
		->and($file->nameMatches('/^test/'))->toBeTrue()
		->and($file->nameMatches('/txt$/'))->toBeTrue()
		->and($file->nameMatches('/^test\.ext1\.ext2\.txt$/'))->toBeTrue();
});

it('can return the matches of a regex match check', function () {
    $file = new File($this->sandboxPath . '/test.ext1.ext2.txt');
    expect($file->nameMatchParts('/(test)(.*)/'))->toEqual(['test.ext1.ext2.txt', 'test', '.ext1.ext2.txt'])
		->and($file->nameMatchParts('/(^test)(.*)/'))->toEqual(['test.ext1.ext2.txt', 'test', '.ext1.ext2.txt'])
		->and($file->nameMatchParts('/(.*)(txt$)/'))->toEqual(['test.ext1.ext2.txt', 'test.ext1.ext2.', 'txt'])
		->and($file->nameMatchParts('/(^test)(.*)(\.txt$)/'))->toEqual(['test.ext1.ext2.txt', 'test', '.ext1.ext2', '.txt']);
});

it('can return its contents', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect($file->contents)->toEqual('test')
		->and($file->getContents())->toEqual('test');
});

it('can rename a file', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    $file->rename('new-name.abc');

    expect($file->name)->toEqual('new-name.abc');
});

it('can rename a file preserving its simple extension', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    $file->rename('new-name', true);

    expect($file->name)->toEqual('new-name.txt');
});

it('can rename a file preserving its complex extension', function () {
    $file = new File($this->sandboxPath . '/test.ext1.ext2.txt');
    $file->rename('new-name', true, 2);

    expect($file->name)->toEqual('new-name.ext2.txt');

    $file->rename('new-name-2', true, 10);

    expect($file->name)->toEqual('new-name-2.ext2.txt');
});

it('can move the file to a different folder path preserving its name', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_exists($this->sandboxPath . '/test.txt'))->toBeTrue()
		->and(file_exists($this->sandboxPath . '/testFolder1/test.txt'))->toBeFalse();
	
	$file->moveTo($this->sandboxPath . '/testFolder1');

    expect($file->path)->toEqual($this->sandboxPath . '/testFolder1/test.txt')
		->and(file_exists($this->sandboxPath . '/test.txt'))->toBeFalse();
});

it('can move the file to a different folder given the folder instance', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_exists($this->sandboxPath . '/test.txt'))->toBeTrue()
		->and(file_exists($this->sandboxPath . '/testFolder1/test.txt'))->toBeFalse();
	
	$file->moveTo(new Folder($this->sandboxPath . '/testFolder1'));

    expect($file->path)->toEqual($this->sandboxPath . '/testFolder1/test.txt')
		->and(file_exists($this->sandboxPath . '/test.txt'))->toBeFalse();
});

it('can delete a file', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_exists($this->sandboxPath . '/test.txt'))->toBeTrue();

    $file->delete();

    expect(file_exists($this->sandboxPath . '/test.txt'))->toBeFalse();
});

it('can put contents into a file', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test');

    $file->putContents('new contents');

    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('new contents');
});

it('can copy the contents from another file instance', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test');

    $source = new File($this->sandboxPath . '/testFolder1/test1.txt');
    expect(file_get_contents($this->sandboxPath . '/testFolder1/test1.txt'))->toEqual('test1');

    $file->copyContentsFromFile($source);

    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test1');
});

it('can copy the contents from another file given its string path', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test');

    $file->copyContentsFromFile($this->sandboxPath . '/testFolder1/test1.txt');

    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test1');
});

it('can copy its contents to another file instance', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test');

    $destination = new File($this->sandboxPath . '/testFolder1/test1.txt');
    expect(file_get_contents($this->sandboxPath . '/testFolder1/test1.txt'))->toEqual('test1');

    $file->copyContentsToFile($destination);

    expect(file_get_contents($this->sandboxPath . '/testFolder1/test1.txt'))->toEqual('test');
});

it('can copy its contents to another file given its string path', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    expect(file_get_contents($this->sandboxPath . '/test.txt'))->toEqual('test');

    $file->copyContentsToFile($this->sandboxPath . '/testFolder1/test1.txt');

    expect(file_get_contents($this->sandboxPath . '/testFolder1/test1.txt'))->toEqual('test');
});

it('can replace string parts in its contents', function () {
    $file = new File($this->sandboxPath . '/replace-test.txt');
    expect(file_get_contents($this->sandboxPath . '/replace-test.txt'))->toEqual('test1 test2 test3');

    $file->replaceInFile(['test1' => 'new1', 'test2' => 'new2']);

    expect(file_get_contents($this->sandboxPath . '/replace-test.txt'))->toEqual('new1 new2 test3');
});

test('creating a file instance for a non existent file does not throw an exception', function () {
    $file = new File($this->sandboxPath . '/non-existent-file.txt');
    expect($file->exists)->toBeFalse();
});

test('reading contents of a non readable file throws an exception', function () {
    $file = new File($this->sandboxPath . '/non-readable-file.txt');
	expect(fn() => $file->getContents())->toThrow(\Exception::class);
});

test('renaming a non existing file throws an exception', function () {
    $file = new File($this->sandboxPath . '/non-existent-file.txt');
    $this->expectException(FileSystemException::class);
    $file->rename('new-name.txt');
});

test('renaming a file to an existing file name throws an exception', function () {
    file_put_contents($this->sandboxPath . '/test2.txt', 'test');
    $file = new File($this->sandboxPath . '/test.txt');
    $this->expectException(FileSystemException::class);
    $file->rename('test2.txt');
});

test('moving a file to a non existent directory throws an exception', function () {
    $this->expectException(\Exception::class);
    $file = new File($this->sandboxPath . '/test.txt');
    $file->moveTo($this->sandboxPath . '/non-existent-directory');
});

it('throws an exception if moving to a folder where a file with the same name already exists', function () {
    file_put_contents($this->sandboxPath . '/testFolder1/test.txt', 'test');
    $file = new File($this->sandboxPath . '/test.txt');

    $this->expectException(FileSystemException::class);
    $file->moveTo($this->sandboxPath . '/testFolder1');
});

it('overwrites an existing file by moving it if requested', function () {
    file_put_contents($this->sandboxPath . '/testFolder1/test.txt', 'will be overwritten');
    $file = new File($this->sandboxPath . '/test.txt');

    $file->moveTo($this->sandboxPath . '/testFolder1', true);

    expect(file_get_contents($this->sandboxPath . '/testFolder1/test.txt'))->toEqual('test');
});

it('can copy a file to another file', function () {
    $file = File::instance($this->sandboxPath . '/test.txt')->putContents('abc');
    $targetFilePath = $this->sandboxPath . '/new-sub-folder/test-copy.txt';
    $copiedFile = $file->clone()->copy($targetFilePath);

    //$this->assertTrue(file_exists($this->contextPath . '/test.txt'));
    expect($copiedFile->path)->toEqual($targetFilePath)
		->and($file->contents)->toEqual('abc')
		->and($copiedFile->exists)->toBeTrue()
		->and($copiedFile->contents)->toEqual('abc');
});

it('creates a backup of an existing file', function () {
    $file = File::instance($this->sandboxPath . '/test.txt')->putContents('abc');
    $backupFile = $file->clone()->backup();

    expect($file->path === $this->sandboxPath . '/test.txt')->toBeTrue()
		->and($file->exists)->toBeTrue()
		->and($file->contains('abc'))->toBeTrue()
		->and($backupFile->path)->toEqual($this->sandboxPath . '/test.txt.backup')
		->and($backupFile->exists)->toBeTrue()
		->and($backupFile->contains('abc'))->toBeTrue();
	
});

it('generates a unique backup file name if a backup file already exists', function () {
    $file = File::instance($this->sandboxPath . '/test.txt')->putContents('abc');
    $backupFile = $file->clone()->backup();

    expect($backupFile->path === $this->sandboxPath . '/test.txt.backup')->toBeTrue()
		->and($backupFile->exists && $backupFile->contains('abc'))->toBeTrue();
	
	$file->putContents('def');
    $backupFile001 = $file->clone()->backup();

    expect($backupFile->exists && $backupFile->contains('abc'))->toBeTrue()
		->and($backupFile001->path)->toEqual($this->sandboxPath . '/test.txt.001.backup')
		->and($backupFile001->exists && $backupFile001->contains('def'))->toBeTrue();
	
	$file->putContents('ghi');
    $backupFile002 = $file->clone()->backup();

    expect($backupFile->exists && $backupFile->contains('abc'))->toBeTrue()
		->and($backupFile001->exists && $backupFile001->contains('def'))->toBeTrue()
		->and($backupFile002->path)->toEqual($this->sandboxPath . '/test.txt.002.backup')
		->and($backupFile002->exists && $backupFile002->contains('ghi'))->toBeTrue();
	
});

it('can create a new empty file', function () {
    $file = new File($this->sandboxPath . '/new-file.txt');
    expect($file->exists)->toBeFalse();

    $file->create();

    expect($file->exists)->toBeTrue()
		->and(file_get_contents($this->sandboxPath . '/new-file.txt'))->toEqual('');
});

it('can create a new file using touch', function () {
    $file = new File($this->sandboxPath . '/new-file.txt');
    expect($file->exists)->toBeFalse();

    $file->touch();

    expect($file->exists)->toBeTrue()
		->and(file_get_contents($this->sandboxPath . '/new-file.txt'))->toEqual('');
});

it('can clone a file instance using the clone function', function () {
    $file = new File($this->sandboxPath . '/test.txt');
    $clone = $file->clone();

    expect($file->path === $clone->path)->toBeTrue()
		->and($file === $clone)->toBeFalse();
});

it('can return the file size', function () {
	$file = File::instance($this->sandboxPath . '/test.txt')->putContents('abcd');
	expect($file->getFileSize())->toEqual(4)
		->and($file->humanReadableFileSize)->toEqual('4 B');
});

it('can get the hash of a file', function() {
	$file = File::instance($this->sandboxPath . '/test.txt')->putContents('abcd');
	expect($file->getHash())->toEqual(hash('sha256', 'abcd'));
});

it('will do nothing when trying to rename a file to the same name it already has', function() {
	$file = File::instance($this->sandboxPath . '/test.txt');
	expect($file->rename('test.txt') === $file)->toBeTrue()
		->and($file->name)->toEqual('test.txt');
});

it('will throw an exception when trying to move a non existent file', function() {
	$file = File::instance($this->sandboxPath . '/non-existent-file.txt');
	expect(fn() => $file->moveTo($this->sandbox->subFolder('move-target-01')->create()))
		->toThrow(\Exception::class);
});

it('will do nothing when trying to move a file to the same folder it is already in', function() {
	$file = $this->sandbox->file('test.txt');
	expect($file->moveTo($this->sandbox->subFolder('move-target-02')->create()) === $file)
		->toBeTrue();
});

it('can not replace content in a non-existing file', function() {
	$file = File::instance($this->sandboxPath . '/non-existent-file-03.txt');
	expect(fn() => $file->replaceInFile(['test' => 'new']))->toThrow(FileSystemException::class);
});

it('will not replace anything in a file on a dry-run', function() {
	$file = File::instance($this->sandboxPath . '/replace-test.txt');
	$file->replaceInFile(['test1' => 'new1', 'test2' => 'new2'], true);
	expect($file->contents)->toEqual('test1 test2 test3');
});

it('will throw an exception when trying to copy a non-existing file', function() {
	$file = File::instance($this->sandboxPath . '/non-existent-file-04.txt');
	expect(fn() => $file->copyContentsToFile($this->sandboxPath . '/testFolder1/test1.txt'))
		->toThrow(FileSystemException::class);
});

it('will not put any file contents on a dry-run', function() {
	$file = File::instance($this->sandboxPath . '/test.txt');
	$file->putContents('new contents', true);
	expect($file->contents)->toEqual('test');
});

it('will not copy a file on a dry-run', function() {
	$file = File::instance($this->sandboxPath . '/test.txt');
	$target = $this->sandbox->file('copy-folder-01/copy-test-01.txt');
	expect($file->copy($target, true, true))->toBeInstanceOf(File::class)
		->and($target->exists)->toBeFalse()
		->and($this->sandbox->subFolder('copy-folder-01')->exists)->toBeFalse();
});

it('will throw an exception if copying to an existing file without overwrite', function() {
	$file = File::instance($this->sandboxPath . '/test.txt');
	$target = $this->sandbox->file('copy-test-02.txt')->putContents('existing file');
	expect(fn() => $file->copy($target))->toThrow(FileSystemException::class);
});

it('will throw an exception if copying to the same file', function() {
	$file = File::instance($this->sandboxPath . '/test.txt');
	expect(fn() => $file->copy($file))->toThrow(FileSystemException::class);
});

it('will throw an exception if trying to copy a non-existing file', function() {
	$file = File::instance($this->sandboxPath . '/non-existent-file-05.txt');
	$target = $this->sandbox->file('copy-test-03.txt');
	expect(fn() => $file->copy($target))->toThrow(FileSystemException::class);
});

it('will not create a new file on a dry-run', function() {
	$file = File::instance($this->sandboxPath . '/create-file-01.txt');
	expect($file->create(true))->toBeInstanceOf(File::class)
		->and($file->exists)->toBeFalse();
});

it('will create the file when using touch', function() {
	$file = File::instance($this->sandboxPath . '/create-file-02.txt');
	expect($file->exists)->toBeFalse();
	$file->touch();
	expect($file->exists)->toBeTrue()
		->and($file->contents)->toEqual('');
});

it('will not create a file with touch on a dry-run', function() {
	$file = File::instance($this->sandboxPath . '/create-file-03.txt');
	expect($file->touch(true))->toBeInstanceOf(File::class)
		->and($file->exists)->toBeFalse();
});

it('will not backup a file if it does not exist', function() {
	$file = File::instance($this->sandboxPath . '/non-existent-file-06.txt');
	expect(fn() => $file->backup())->toThrow(FileSystemException::class);
});

it('will do nothing when moving a file to another fi', function() {
	$file = File::instance($this->sandboxPath . '/test.txt');
	expect($file->moveTo($this->sandboxPath) === $file)->toBeTrue();
});

//--- Test context ------------------------------------------------------------------------------------------------
function setupFileTestContext(string $contextPath) : void
{
    file_put_contents($contextPath . '/test.txt', 'test');
    file_put_contents($contextPath . '/test.ext1.ext2.txt', 'test with multiple extensions');
    mkdir($contextPath . '/testFolder1');
    mkdir($contextPath . '/testFolder2');

    file_put_contents($contextPath . '/testFolder1/test1.txt', 'test1');
    file_put_contents($contextPath . '/testFolder1/test2.txt', 'test2');

    mkdir($contextPath . '/testFolder1/testFolder1_1');
    file_put_contents($contextPath . '/testFolder1/testFolder1_1/test1_1.txt', 'test1_1');

    file_put_contents($contextPath . '/replace-test.txt', 'test1 test2 test3');
	
	expect($contextPath)->toBeDirectory()
		->and($contextPath . '/testFolder1')->toBeDirectory()
		->and($contextPath . '/testFolder2')->toBeDirectory()
		->and($contextPath . '/test.txt')->toBeFile()
		->and($contextPath . '/test.ext1.ext2.txt')->toBeFile()
		->and($contextPath . '/testFolder1/test1.txt')->toBeFile()
		->and($contextPath . '/testFolder1/test2.txt')->toBeFile()
		->and($contextPath . '/testFolder1/testFolder1_1/test1_1.txt')->toBeFile()
		->and(file_get_contents($contextPath . '/test.txt'))->toEqual('test')
		->and(file_get_contents($contextPath . '/test.ext1.ext2.txt'))->toEqual('test with multiple extensions')
		->and(file_get_contents($contextPath . '/testFolder1/test1.txt'))->toEqual('test1')
		->and(file_get_contents($contextPath . '/testFolder1/test2.txt'))->toEqual('test2')
		->and(file_get_contents($contextPath . '/testFolder1/testFolder1_1/test1_1.txt'))->toEqual('test1_1')
		->and(file_get_contents($contextPath . '/replace-test.txt'))->toEqual('test1 test2 test3');
}