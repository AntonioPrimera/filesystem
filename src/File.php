<?php

namespace Antonioprimera\FileSystem;

use Carbon\Carbon;
use Stringable;

/**
 * Represents a file in the file system
 *
 * @property-read string $folderPath
 * @property-read Folder $folder
 * @property-read string $baseName
 * @property-read string $fileName
 * @property-read string $extension
 * @property-read string $complexExtension
 * @property-read Carbon $createTime
 * @property-read Carbon $modifiedTime
 * @property-read string $contents
 * @property-read bool $exists
 */
class File implements Stringable
{
	public readonly string $originalPath;
	
	public function __construct(public string $path)
	{
		$this->originalPath = $path;
	}
	
	public static function instance(string|File $path): static
	{
		return $path instanceof File ? $path : new static($path);
	}
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	/**
	 * Absolute path to the containing folder
	 */
	public function getFolderPath(): string
	{
		return pathinfo($this->path, PATHINFO_DIRNAME);
	}
	
	/**
	 * Containing folder instance
	 */
	public function getFolder(): Folder
	{
		return new Folder($this->folderPath);
	}
	
	/**
	 * File name with extension
	 */
	public function getBaseName(): string
	{
		return pathinfo($this->path, PATHINFO_BASENAME);
	}
	
	/**
	 * File name without extension
	 */
	public function getFileName(): string
	{
		return pathinfo($this->path, PATHINFO_FILENAME);
	}
	
	/**
	 * File extension (without the leading dot)
	 */
	public function getExtension(): string
	{
		return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
	}
	
	/**
	 * Get the full extension of the file for files which
	 * have a complex extension (e.g: .tar.gz or .blade.php)
	 */
	public function getComplexExtension(): string
	{
		return explode('.', $this->getBaseName(), 2)[1] ?? '';
	}
	
	/**
	 * Get the shortest file name without the complex extension
	 */
	public function getMinFilename(): string
	{
		return explode('.', $this->getBaseName(), 2)[0];
	}
	
	public function getCreateTime(): Carbon
	{
		return new Carbon(filectime($this->path));
	}
	
	public function getModifiedTime(): Carbon
	{
		return new Carbon(filemtime($this->path));
	}
	
	public function getContents(): string
	{
		$contents = file_get_contents($this->path);
		if ($contents === false)
			throw new \Exception("Failed to read file '{$this->path}'!");
		
		return $contents;
	}
	
	//--- File operations ---------------------------------------------------------------------------------------------
	
	/**
	 * Renames the file
	 */
	public function rename(string $newFileName, bool $preserveExtension = true, bool $dryRun = false): static
	{
		if (!$this->exists())
			throw new FileSystemException("Rename: The file '{$this->path}' can not be renamed, because it doesn't exist!");
		
		$newFilePath = "{$this->folderPath}/{$newFileName}" . ($preserveExtension ? ".{$this->complexExtension}" : '');
		
		if (!$dryRun)
			rename($this->path, $newFilePath);
		
		$this->path = $newFilePath;
		
		return $this;
	}
	
	/**
	 * Moves the file to the given folder, keeping the same file name
	 */
	public function moveTo(string|Folder $targetFolder, bool $dryRun = false): static
	{
		$newFilePath = "{$targetFolder}/{$this->baseName}";
		
		if (!$dryRun)
			rename($this->path, $newFilePath);
		
		$this->path = $newFilePath;
		return $this;
	}
	
	public function delete(bool $dryRun = false): static
	{
		if ($this->exists() && !$dryRun)
			unlink($this->path);
		
		return $this;
	}
	
	//--- File contents management ------------------------------------------------------------------------------------
	
	/**
	 * Add the given contents to the file, overwriting the file if
	 * it already exists or creating it if it doesn't exist.
	 * It creates the containing folder if necessary
	 */
	public function putContents(string $contents, bool $dryRun = false): static
	{
		if ($dryRun)
			return $this;
		
		$this->folder->create();
		file_put_contents($this->path, $contents);
		return $this;
	}
	
	public function copyContentsFromFile(File $sourceFile, bool $dryRun = false): static
	{
		return $sourceFile->copyContentsToFile($this, $dryRun);
	}
	
	public function copyContentsToFile(File $destinationFile, bool $dryRun = false): static
	{
		if (!$this->exists)
			throw new FileSystemException("CopyContentsToFile: The source file '{$this->path}' doesn't exist!");
		
		$destinationFile->putContents($this->contents, $dryRun);
		return $this;
	}
	
	/**
	 * Replace the given [search => replace] string pairs in the file contents
	 */
	public function replaceInFile(array $replace): static
	{
		if (!$this->exists())
			throw new FileSystemException("ReplaceInFile: File {$this->path} does not exist.");
		
		$contents = $this->getContents();
		
		foreach ($replace as $search => $replaceWith)
			$contents = str_replace($search, $replaceWith, $contents);
		
		$this->putContents($contents);
		return $this;
	}
	
	//--- Checks ------------------------------------------------------------------------------------------------------
	
	public function nameMatches(string $pattern): bool
	{
		return preg_match($pattern, $this->fileName);
	}
	
	public function baseNameMatches(string $pattern): bool
	{
		return preg_match($pattern, $this->baseName);
	}
	
	public function pregMatchName(string $pattern): array|false
	{
		$matches = [];
		preg_match($pattern, $this->fileName, $matches);
		return $matches;
	}
	
	public function pregMatchBaseName(string $pattern): array|false
	{
		$matches = [];
		preg_match($pattern, $this->baseName, $matches);
		return $matches;
	}
	
	/**
	 * Checks if the file exists
	 */
	public function exists(): string|bool
	{
		return file_exists($this->path);
	}
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __toString(): string
	{
		return $this->path;
	}
	
	public function __get(string $name): mixed
	{
		if (is_callable([$this, 'get' . ucfirst($name)]))
			return call_user_func([$this, 'get' . ucfirst($name)]);
		
		if ($name === 'exists')
			return $this->exists();
		
		return null;
	}
}