<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\File;

trait ZipsFolders
{
	use UsesZipExtension;
	
	public function zip(bool $includeRoot = true): File
	{
		$zipFileName = $this->getName() . '.zip';
		return $this->zipTo($this->getParentFolder()->file($zipFileName), $includeRoot);
	}
	
	public function zipTo(File|string $destination, bool $includeRoot = true): File
	{
		return File::instance($this->zipFolder($this, $destination, $includeRoot));
	}
}