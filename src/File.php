<?php

namespace AntonioPrimera\FileSystem;

/**
 * Represents a file in the file system
 *
 * @property-read string $folderPath
 * @property-read Folder $folder
 * @property-read string $contents
 * @property-read int $fileSize
 * @property-read string $humanReadableFileSize
 * @property-read string $hash
 */
class File extends FileSystemItem
{
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	/**
	 * Containing folder instance
	 */
	public function getFolder(): Folder
	{
		return new Folder($this->folderPath);
	}
	
	
	public function getContents(): string
	{
		$contents = file_get_contents($this->path);
		if ($contents === false)
			throw new FileSystemException("Failed to read file '{$this->path}'!");
		
		return $contents;
	}
	
	public function getFileSize(): int
	{
		return filesize($this->path);
	}
	
	public function getHash(): string
	{
		return hash_file('sha256', $this->path);
	}
	
	public function getHumanReadableFileSize(): string
	{
		$size = $this->getFileSize();
		
		if ($size < 1024)
			return "{$size} B";
		
		$size /= 1024;
		
		if ($size < 1024)
			return round($size, 2) . ' KB';
		
		$size /= 1024;
		
		if ($size < 1024)
			return round($size, 2) . ' MB';
		
		$size /= 1024;
		
		if ($size < 1024)
			return round($size, 2) . ' GB';
		
		$size /= 1024;
		
		return round($size, 2) . ' TB';
	}
	
	//--- File operations ---------------------------------------------------------------------------------------------
	
	/**
	 * Renames the file
	 */
	public function rename(
		string $newFileName,
		bool $preserveExtension = false,
		int $maxExtensionParts = 1,
		bool $dryRun = false
	): static
	{
		if (!$this->exists())
			throw new FileSystemException("Rename: The file '{$this->path}' can not be renamed, because it doesn't exist!");
		
		$newFilePath = "{$this->folderPath}/{$newFileName}" . ($preserveExtension ? ".{$this->getExtension($maxExtensionParts)}" : '');
		
		if ($this->path === $newFilePath)
			return $this;
		
		if (file_exists($newFilePath))
			throw new FileSystemException("Rename: The file '{$this->path}' can not be renamed to '{$newFilePath}', because the destination file already exists!");
		
		if (!$dryRun)
			rename($this->path, $newFilePath);
		
		$this->path = $newFilePath;
		
		return $this;
	}
	
	/**
	 * Moves the file to the given folder, keeping the same file name
	 */
	public function moveTo(string|Folder $targetFolder, bool $overwrite = false, bool $dryRun = false): static
	{
		$newFilePath = "{$targetFolder}/{$this->name}";
		
		if ($this->path === $newFilePath)
			return $this;
		
		if (!is_dir((string) $targetFolder))
			throw new FileSystemException("MoveTo: The file '{$this->path}' can not be moved to '{$newFilePath}', because the destination folder '{$targetFolder}' doesn't exist!");
		
		if (file_exists($newFilePath) && !$overwrite)
			throw new FileSystemException("MoveTo: The file '{$this->path}' can not be moved to '{$newFilePath}', because the destination file already exists!");
		
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
	
	public function copyContentsFromFile(File|string $sourceFile, bool $dryRun = false): static
	{
		return File::instance($sourceFile)->copyContentsToFile($this, $dryRun);
	}
	
	public function copyContentsToFile(File|string $destinationFile, bool $dryRun = false): static
	{
		if (!$this->exists)
			throw new FileSystemException("CopyContentsToFile: The source file '{$this->path}' doesn't exist!");
		
		File::instance($destinationFile)->putContents($this->contents, $dryRun);
		return $this;
	}
	
	/**
	 * Replace the given [search => replace] string pairs in the file contents
	 */
	public function replaceInFile(array $replace, bool $dryRun = false): static
	{
		if ($dryRun)
			return $this;
		
		if (!$this->exists())
			throw new FileSystemException("ReplaceInFile: File {$this->path} does not exist.");
		
		$contents = $this->getContents();
		
		foreach ($replace as $search => $replaceWith)
			$contents = str_replace($search, $replaceWith, $contents);
		
		$this->putContents($contents);
		return $this;
	}
	
	//--- Checks ------------------------------------------------------------------------------------------------------
	
	/**
	 * Checks if the file exists
	 */
	public function exists(): bool
	{
		return file_exists($this->path);
	}
}