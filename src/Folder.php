<?php

namespace AntonioPrimera\FileSystem;

use AntonioPrimera\FileSystem\Traits\CommonApiMethods;
use AntonioPrimera\FileSystem\Traits\ZipsFilesAndFolders;

/**
 * Represents a folder in the file system
 *
 * @property-read File[] $files
 * @property-read Folder[] $folders
 * @property-read string[] $fileNames
 * @property-read string[] $folderNames
 */
class Folder extends FileSystemItem
{
	use CommonApiMethods, ZipsFilesAndFolders;
	
	protected array|null $cachedFileNames = null;
	protected array|null $cachedFolderNames = null;
	
	//--- Factories ---------------------------------------------------------------------------------------------------
	
	public function subFolder(string $subFolderName): static
	{
		return new static($this->mergePathParts($this->path, $subFolderName));
	}
	
	public function file(string $fileName): File
	{
		return new File($this->mergePathParts($this->path, $fileName));
	}
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	public function getFiles(mixed $filter = null, bool $fromCache = true): array
	{
		return array_map(fn ($fileName) => $this->file($fileName), $this->getFileNames($filter, $fromCache));
	}
	
	public function getFolders(mixed $filter = null, bool $fromCache = true): array
	{
		return array_map(fn ($folderName) => $this->subFolder($folderName), $this->getFolderNames($filter, $fromCache));
	}
	
	public function getFileNames(mixed $filter = null, bool $fromCache = true): array
	{
		$fileNames = $fromCache
			? $this->cachedFileNames ??= $this->_getFileNames()	//use the cache if we have it
			: $this->cachedFileNames = $this->_getFileNames();	//update the cache if we read the file names anyway
		
		return $this->_filterItems($fileNames, $filter);
	}
	
	public function getFolderNames(mixed $filter = null, bool $fromCache = true): array
	{
		$folderNames = $fromCache
			? $this->cachedFolderNames ??= $this->_getFolderNames()	//use the cache if we have it
			: $this->cachedFolderNames = $this->_getFolderNames();	//update the cache if we read the folder names anyway
		
		return $this->_filterItems($folderNames, $filter);
	}
	
	/**
	 * Return a flat list of all files, by searching recursively through all sub-folders.
	 */
	public function getAllFiles(mixed $filter = null): array
	{
		$files = $this->getFiles(filter: $filter, fromCache: false);
		
		//recursively get all files from all sub-folders (folders are not filtered and not cached)
		foreach ($this->getFolders(fromCache: false) as $folder)
			$files = array_merge($files, $folder->getAllFiles($filter));
		
		return $files;
	}
	
	//--- Folder operations -------------------------------------------------------------------------------------------
	
	public function create(bool $dryRun = false): static
	{
		if (!$this->exists() && !$dryRun)
			mkdir($this->path, recursive: true);
		
		return $this;
	}
	
	public function rename(string $newName, bool $dryRun = false): static
	{
		if (!$this->exists())
			throw new FileSystemException("Rename: Folder '{$this->path}' doesn't exist!");
		
		$newPath = $this->mergePathParts(dirname($this->path), $newName);
		
		if (!$dryRun)
			rename($this->path, $newPath);
		
		$this->path = $newPath;
		
		return $this;
	}
	
	/**
	 * Move this folder to the given path.
	 */
	public function move(string|Folder $newParentFolder, bool $overwrite = false, bool $dryRun = false): static
	{
		$newParentFolderPath = (string) $newParentFolder;
		
		//create the parent folder if it doesn't exist
		if (!$dryRun && !is_dir($newParentFolderPath))
			mkdir($newParentFolderPath, recursive: true);
		
		$newPath = $this->mergePathParts($newParentFolderPath, $this->getName());
		
		if (is_dir($newPath) && !$overwrite)
			throw new FileSystemException("Move: Folder '{$this->path}' already exists in '{$newParentFolderPath}'!");
		
		if (!$dryRun)
			rename($this->path, $newPath);
		
		$this->path = $newPath;
		
		return $this;
	}
	
	/**
	 * Move the given files to this folder.
	 */
	public function moveFilesToSelf(array $files, bool $dryRun = false): static
	{
		foreach ($files as $file)
			File::instance($file)->moveTo($this->path, $dryRun);
		
		return $this;
	}
	
	public function delete(bool $deep = false, bool $dryRun = false): static
	{
		if (!$this->exists() || $dryRun)
			return $this;
		
		if ($deep) {
			foreach ($this->getFiles() as $file)
				$file->delete($dryRun);
			
			foreach ($this->getFolders() as $folder)
				$folder->delete($deep, $dryRun);
		}
		
		rmdir($this->path);
		
		return $this;
	}
	
	//--- Checks ------------------------------------------------------------------------------------------------------
	
	public function exists(): bool
	{
		return is_dir($this->path);
	}
	
	public function hasFile(string $fileName): bool
	{
		return file_exists($this->mergePathParts($this->path, $fileName));
	}
	
	public function hasSubFolder(string $folderName): bool
	{
		return is_dir($this->mergePathParts($this->path, $folderName));
	}
	
	public function hasFiles(array $fileNames): bool
	{
		foreach ($fileNames as $fileName)
			if (!$this->hasFile($fileName))
				return false;
		
		return true;
	}
	
	public function hasSubFolders(array $folderNames): bool
	{
		foreach ($folderNames as $folderName)
			if (!$this->hasSubFolder($folderName))
				return false;
		
		return true;
	}
	
	public function isEmpty(bool $forceRefresh = false): bool
	{
		return empty($this->getFileNames($forceRefresh)) && empty($this->getFolderNames($forceRefresh));
	}
	
	public function isNotEmpty(bool $forceRefresh = false): bool
	{
		return !$this->isEmpty($forceRefresh);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function _getFileNames(): array
	{
		return array_filter(
			scandir($this->path),
			fn ($fileName) => !is_dir($filePath = $this->mergePathParts($this->path, $fileName)) && !is_link($filePath)
		);
	}
	
	protected function _getFolderNames(): array
	{
		return array_filter(
			scandir($this->path),
			fn ($fileName) => !in_array($fileName, ['.', '..']) && is_dir($this->mergePathParts($this->path, $fileName))
		);
	}
	
	protected function _filterItems(array $items, mixed $filter): array
	{
		if (!$filter)
			return $items;
		
		$filterFunction = is_callable($filter)
			? $filter
			: fn ($item) => preg_match($filter, $item);
		
		return array_filter($items, $filterFunction);
	}
}