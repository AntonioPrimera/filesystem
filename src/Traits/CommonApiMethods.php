<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\Folder;

/**
 * Common API methods for files and folders
 *
 * @property-read Folder $parentFolder
 * @property-read Folder $containingFolder
 */
trait CommonApiMethods
{
	/**
	 * Returns the parent folder instance of the current file or folder
	 */
	public function getParentFolder(): Folder
	{
		return new Folder($this->getParentFolderPath());
	}
	
	/**
	 * Syntactic sugar for getParentFolder()
	 */
	public function getContainingFolder(): Folder
	{
		return $this->getParentFolder();
	}
}