<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemException;
use ZipArchive;

trait UsesZipExtension
{
	protected function checkZipExtension(): void
	{
		if (!extension_loaded('zip'))
			throw new FileSystemException("The 'zip' extension is not loaded! You need to install it in order to work with zip files!");
	}
	
	protected function openZipArchive(File|string $file, int $flags = 0): ZipArchive
	{
		$this->checkZipExtension();
		
		$zipFile = File::instance($file);
		
		$zip = new ZipArchive();
		if(!($zip->open($zipFile->path, $flags) === true))
			//todo: include also the error reason (e.g. 28 - when trying to open a folder - not documented)
			throw new FileSystemException("The file '$zipFile' is not a valid zip archive!");
		
		return $zip;
	}
}