<?php

namespace AntonioPrimera\FileSystem\Tests;

use AntonioPrimera\FileSystem\FileSystemItem;

/**
 * @method string _mergePathParts(...$parts)
 */
class FileSystemItemTester extends FileSystemItem
{
	
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