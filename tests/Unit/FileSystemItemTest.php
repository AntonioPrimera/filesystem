<?php

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Tests\FileSystemItemTester;

it('can correctly merge path parts by removing bad slashes', function () {
    $instance = new FileSystemItemTester('');

    expect($instance->_mergePathParts('path', '/to/', 'file'))->toEqual('path/to/file')
		->and($instance->_mergePathParts('path/', '/to/', '/file'))->toEqual('path/to/file')
		->and($instance->_mergePathParts('path/', '/to/', '/file/'))->toEqual('path/to/file')
		->and($instance->_mergePathParts('path\\', '/to\\', '/file\\'))->toEqual('path/to/file')
		->and($instance->_mergePathParts('/path', '\\to\\', '\\file'))->toEqual('/path/to/file')
		->and($instance->_mergePathParts('\\path/', '\\to\\', '\\file'))->toEqual('\\path/to/file')
		->and($instance->_mergePathParts('path', 'to', 'file'))->toEqual('path/to/file')
		->and($instance->_mergePathParts('path/', 'to/', '', '/', '\\', '/\\', '\\/', 'file'))->toEqual('path/to/file');
});

it('will magically get a property value if it has a getter method', function () {
	$instance = new FileSystemItemTester('path/to/file');

	expect($instance->path)->toEqual('path/to/file')
		->and($instance->getName())->toEqual($instance->name)
		->and($instance->getParentFolderPath())->toEqual($instance->parentFolderPath);
	
	//calling a non-existing property on a file instance (implementing FileSystemItem) will return null
	$fileInstance = File::instance('path/to/file');
	expect($fileInstance->nonExistingProperty)->toBeNull();
});
