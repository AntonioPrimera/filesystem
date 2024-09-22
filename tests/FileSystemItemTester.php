<?php
namespace AntonioPrimera\FileSystem\Tests;

use AntonioPrimera\FileSystem\FileSystemItem;
use AntonioPrimera\FileSystem\Traits\CommonApiMethods;
use AntonioPrimera\FileSystem\Traits\HandlesZipFiles;
use AntonioPrimera\FileSystem\Traits\ZipsFilesAndFolders;

/**
 * @method string _mergePathParts(...$parts)
 */
class FileSystemItemTester extends FileSystemItem
{
	use CommonApiMethods, HandlesZipFiles, ZipsFilesAndFolders;
	
	public function exists(): bool
	{
		return true;
	}
	
	/**
	 * Expose protected methods for testing
	 */
	public function __call(string $name, array $arguments)
	{
		$methodName = ltrim($name, '_');
		return $this->$methodName(...$arguments);
	}
}