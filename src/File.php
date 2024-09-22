<?php

namespace AntonioPrimera\FileSystem;

use AntonioPrimera\FileSystem\Traits\CommonApiMethods;
use AntonioPrimera\FileSystem\Traits\HandlesZipFiles;
use AntonioPrimera\FileSystem\Traits\ZipsFilesAndFolders;

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
	use CommonApiMethods, HandlesZipFiles, ZipsFilesAndFolders;
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	/**
	 * Containing folder instance.
	 * Same as getParentFolder() or $parentFolder (from the CommonApiMethods trait)
	 */
	public function getFolder(): Folder
	{
		return new Folder($this->folderPath);
	}
	
	public function getContents(): string
	{
		if (!$this->exists())
			throw new FileSystemException("The file '{$this->path}' does not exist!");
		
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
		
		$newFilePath = $this->folderPath
			. DIRECTORY_SEPARATOR
			. $newFileName
			. ($preserveExtension ? ".{$this->getExtension($maxExtensionParts)}" : '');
		
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
		if (!$this->exists())
			throw new FileSystemException("MoveTo: The file '{$this->path}' can not be moved, because it doesn't exist!");
		
		$newFilePath = Folder::instance($targetFolder)->file($this->name);
		
		if ($this->path === $newFilePath->path)
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
	
	/**
	 * Backup the file by copying it to a new file with the same name and the '.backup' extension
	 * If the backup file already exists, a counter is added to the file name (e.g. original-file-name.001.backup)
	 *
	 * This method returns the file instance of the backup file.
	 * Use $backupFile = $file->clone()->backup() to preserve the original file instance
	 */
	public function backup(bool $dryRun = false): static
	{
		if (!$this->exists())
			throw new FileSystemException("Backup: The file '{$this->path}' can not be backed up, because it doesn't exist!");
		
		$backupPath = $this->path . '.backup';
		
		//make sure the backup file doesn't already exist (add a number to the file name if necessary)
		$backupIndex = 0;
		while (file_exists($backupPath))
			$backupPath = $this->path
				. '.' . str_pad(++$backupIndex, 3, '0', STR_PAD_LEFT)
				. '.backup';
		
		//create the backup and copy the contents
		$this->copy($backupPath, false, $dryRun);
		
		return new static($backupPath);
	}
	
	public function touch(bool $dryRun = false): static
	{
		if ($dryRun)
			return $this;
		
		if ($this->exists())
			touch($this->path);
		else
			$this->create();
		
		return $this;
	}
	
	public function create(bool $dryRun = false): static
	{
		if ($this->exists() || $dryRun)
			return $this;
		
		$this->folder->create();
		$this->putContents('', $dryRun);
		return $this;
	}
	
	/**
	 * Copy a file to a new location and return the newly copied file instance
	 * Use $fileCopy = $file->clone()->copy($newPath) to preserve the original file instance
	 */
	public function copy(string|self $targetFile, bool $overwrite = false, bool $dryRun = false): static
	{
		if (!$this->exists())
			throw new FileSystemException("Copy: The file '{$this->path}' can not be copied, because it doesn't exist!");
		
		$targetFile = File::instance($targetFile);
		
		if ($this->path === $targetFile->path)
			throw new FileSystemException("Copy: The file '{$this->path}' can not be copied to '{$targetFile->path}', because it's the same file!");
		
		if ($targetFile->exists && !$overwrite)
			throw new FileSystemException("Copy: The file '{$this->path}' can not be copied to '{$targetFile->path}', because the destination file already exists!");
		
		if ($dryRun)
			return $this;
		
		$targetFile->folder->create();
		copy($this->path, $targetFile->path);
		
		return $targetFile;
	}
	
	//--- File contents management ------------------------------------------------------------------------------------
	
	/**
	 * Add the given contents to the file, overwriting the file if
	 * it already exists or creating it if it doesn't exist.
	 * It creates the containing folder if necessary
	 *
	 * todo: file_put_contents might fail if the file is locked or if the script does not have the necessary permissions
	 * 		check the return value of file_put_contents and handle any errors appropriately
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
		if (!$this->exists())
			throw new FileSystemException("ReplaceInFile: File {$this->path} does not exist.");
		
		if ($dryRun)
			return $this;
		
		$contents = $this->getContents();
		
		foreach ($replace as $search => $replaceWith)
			$contents = str_replace($search, $replaceWith, $contents);
		
		$this->putContents($contents);
		return $this;
	}
	
	public function contains(string $search): bool
	{
		return str_contains($this->contents, $search);
	}
	
	//--- Checks ------------------------------------------------------------------------------------------------------
	
	/**
	 * Checks if the file exists
	 */
	public function exists(): bool
	{
		return is_file($this->path);
	}
}