<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\FileSystemException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

/**
 * This trait provides methods for working with zip archives.
 * The methods in this trait require the 'zip' extension to be loaded and are package agnostic.
 */
trait UsesZipExtension
{
	protected function checkZipExtension(): void
	{
		if (!extension_loaded('zip'))
			throw new FileSystemException("The 'zip' extension is not loaded! You need to install it in order to work with zip files!");
	}
	
	/**
	 * Opens a zip archive, given its path and returns the ZipArchive object
	 */
	protected function openZipArchive(string $zipFile, int $flags = 0): ZipArchive
	{
		$this->checkZipExtension();
		
		$zip = new ZipArchive();
		if(!($zip->open($zipFile, $flags) === true))
			//todo: include also the error reason (e.g. 28 - when trying to open a folder - not documented)
			throw new FileSystemException("The file '$zipFile' is not a valid zip archive!");
		
		return $zip;
	}
	
	/**
	 * Archives the contents of a folder into a zip file, returning the path to the zip file.
	 */
	protected function zipFolder(string $folder, string $zipFile, bool $includeRoot = true): string
	{
		// Get the absolute path for the folder to be archived (if it exists)
		$rootPath = realpath($folder);
		
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
		
		$zipRoot = $includeRoot ? dirname($rootPath) : $rootPath;
		
		foreach ($files as $file) {
			// Skip directories (they would be added automatically)
			if ($file->isDir())
				continue;
			
			// Get absolute and relative path for current file
			$absolutePath = $file->getRealPath();
			$relativePath = substr($absolutePath, strlen($zipRoot) + 1);
			
			// Add current file to archive
			$zip->addFile($absolutePath, $relativePath);
		}
		
		// Zip archive will be created only after closing object
		$zip->close();
		
		return $zipFile;
	}
	
	protected function zipFile(string $file, string $zipFile): string
	{
		// Get the absolute path for the file to be archived (if it exists)
		$filePath = realpath($file);
		
		// Throw an exception if the file does not exist
		if($filePath === false)
			throw new FileSystemException("The file to be archived '{$file}' does not exist!");
		
		// Initialize the archive object
		$zip = $this->openZipArchive($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		
		// Add the file to the archive
		$zip->addFile($filePath, basename($filePath));
		
		// Zip archive will be created only after closing object
		$zip->close();
		
		return $zipFile;
	}
}