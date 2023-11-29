<?php

namespace Antonioprimera\FileSystem;

/**
 * Represents a folder in the file system
 *
 * @property-read string $baseName
 * @property-read string $originalPath
 * @property-read bool $exists
 * @property-read File[] $files
 * @property-read Folder[] $folders
 * @property-read string[] $fileNames
 * @property-read string[] $folderNames
 */
class Folder implements \Stringable
{
	public readonly string $originalPath;
	
	protected array|null $cachedFileNames = null;
	protected array|null $cachedFolderNames = null;
	
	public function __construct(public string $path)
	{
		$this->originalPath = $path;
	}
	
	//--- Factories ---------------------------------------------------------------------------------------------------
	
	public static function instance(string|Folder $path): static
	{
		return $path instanceof Folder ? $path : new static($path);
	}
	
	public function subFolder(string $subFolderName): static
	{
		return new static($this->path . DIRECTORY_SEPARATOR . $subFolderName);
	}
	
	public function file(string $fileName): File
	{
		return new File($this->path . DIRECTORY_SEPARATOR . $fileName);
	}
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	public function getBaseName(): string
	{
		return pathinfo($this->path, PATHINFO_BASENAME);
	}
	
	public function getFiles(): array
	{
		return array_map(fn ($fileName) => $this->file($fileName), $this->getFileNames());
	}
	
	public function getFolders(): array
	{
		return array_map(fn ($folderName) => $this->subFolder($folderName), $this->getFolderNames());
	}
	
	public function getFileNames(bool $forceRefresh = false): array
	{
		if ($forceRefresh)
			$this->cachedFileNames = null;
		
		return $this->cachedFileNames ??= $this->_getFileNames();
	}
	
	public function getFolderNames(bool $forceRefresh = false): array
	{
		if ($forceRefresh)
			$this->cachedFolderNames = null;
		
		return $this->cachedFolderNames ??= $this->_getFolderNames();
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
		$newPath = dirname($this->path) . DIRECTORY_SEPARATOR . $newName;
		
		if (!$dryRun)
			rename($this->path, $newPath);
		
		$this->path = $newPath;
		
		return $this;
	}
	
	/**
	 * Move this folder to the given path.
	 */
	public function move(string|Folder $newParentFolder, bool $dryRun = false): static
	{
		$newParentFolderPath = (string) $newParentFolder;
		
		//create the parent folder if it doesn't exist
		if (!$dryRun && !is_dir($newParentFolderPath))
			mkdir($newParentFolderPath, recursive: true);
		
		$newPath = $newParentFolderPath . DIRECTORY_SEPARATOR . $this->getBaseName();
		
		if (!$dryRun)
			rename($this->path, $newPath);
		
		$this->path = $newPath;
		
		return $this;
	}
	
	public function exists(): bool
	{
		return is_dir($this->path);
	}
	
	public function hasFile(string $fileName, bool $exactMatch = true): bool
	{
		if ($exactMatch)
			return file_exists($this->path . DIRECTORY_SEPARATOR . $fileName);
		
		//check if a file with the same name exists, regardless of the extension
		$nameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
		return count(
			array_filter(
				$this->getFileNames(),
				fn ($name) => pathinfo($name, PATHINFO_FILENAME) === $nameWithoutExtension
			)
		) > 0;
	}
	
	public function hasSubFolder(string $folderName): bool
	{
		return is_dir($this->path . DIRECTORY_SEPARATOR . $folderName);
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
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function _getFileNames(): array
	{
		return array_filter(
			scandir($this->path),
			fn ($fileName) => !is_dir($this->path . DIRECTORY_SEPARATOR . $fileName) && !is_link($this->path . DIRECTORY_SEPARATOR . $fileName)
		);
	}
	
	protected function _getFolderNames(): array
	{
		return array_filter(
			scandir($this->path),
			fn ($fileName) => is_dir($this->path . DIRECTORY_SEPARATOR . $fileName) && !in_array($fileName, ['.', '..'])
		);
	}
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __get(string $name)
	{
		if (is_callable([$this, 'get' . ucfirst($name)]))
			return call_user_func([$this, 'get' . ucfirst($name)]);
		
		if ($name === 'exists')
			return $this->exists();
		
		return null;
	}
	
	//--- Interface implementation ------------------------------------------------------------------------------------
	
	//todo: add methods: create()
	public function __toString()
	{
		return $this->path;
	}
}