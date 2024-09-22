<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\FileSystemException;
use AntonioPrimera\FileSystem\Folder;

trait UnzipsFiles
{
	use UsesZipExtension;
	
	/**
	 * Checks if the current file is a valid zip archive.
	 */
	public function isZipArchive(): bool
	{
		try {
			$this->openZipArchive($this);
			return true;
		} catch (FileSystemException) {
			return false;
		}
	}
	
	/**
	 * Extracts the contents of the zip file to the same folder the zip file is located in.
	 * It returns the folder where the contents were extracted.
	 */
	public function unzip(): Folder
	{
		return $this->unzipTo($this->getParentFolder());
	}
	
	/**
	 * Extracts the contents of the zip file to the specified folder.
	 * It returns the folder where the contents were extracted.
	 */
	public function unzipTo(Folder|string $destinationFolder): Folder
	{
		$zip = $this->openZipArchive($this);
		$destination = Folder::instance($destinationFolder)->create();
		$zip->extractTo($destination->path);
		$zip->close();
		
		return $destination;
	}
}