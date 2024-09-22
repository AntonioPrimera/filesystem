<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

trait ZipsFilesAndFolders
{
	use UsesZipExtension;
	
	public function zip(): File
	{
		$zipFileName = $this->getName() . '.zip';
		return $this->zipTo($this->getParentFolder()->file($zipFileName));
	}
	
	public function zipTo(File|string $destination): File
	{
		if ($this instanceof Folder)
			return $this->zipFolder($this, $destination);
		
		if ($this instanceof File)
			return $this->zipFile($this, $destination);
		
		throw new FileSystemException("The current object is neither a file nor a folder!");
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	/**
	 * Archives the contents of a folder into a zip file, preserving
	 * the folder structure but not the root folder itself
	 */
	protected function zipFolder(string|Folder $folder, string|File $zipFile): File
	{
		// Get the absolute path for the folder to be archived (if it exists)
		$rootPath = Folder::instance($folder)->realPath;
		
		// Throw an exception if the folder does not exist
		if($rootPath === false)
			throw new FileSystemException("The folder to be archived '{$folder}' does not exist!");
		
		// Initialize the archive object
		$zip = $this->openZipArchive($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		
		foreach ($files as $file) {
			// Skip directories (they would be added automatically)
			if ($file->isDir())
				continue;
			
			// Get absolute and relative path for current file
			$absolutePath = $file->getRealPath();
			$relativePath = substr($absolutePath, strlen($rootPath) + 1);
			
			// Add current file to archive
			$zip->addFile($absolutePath, $relativePath);
		}
		
		// Zip archive will be created only after closing object
		$zip->close();
		
		return File::instance($zipFile);
	}
	
	protected function zipFile(string|File $file, string|File $zipFile): File
	{
		// Get the absolute path for the file to be archived (if it exists)
		$filePath = File::instance($file)->realPath;
		
		// Throw an exception if the file does not exist
		if($filePath === false)
			throw new FileSystemException("The file to be archived '{$file}' does not exist!");
		
		// Initialize the archive object
		$zip = $this->openZipArchive($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		
		// Add the file to the archive
		$zip->addFile($filePath, basename($filePath));
		
		// Zip archive will be created only after closing object
		$zip->close();
		
		return File::instance($zipFile);
	}
}