<?php
namespace AntonioPrimera\FileSystem\Traits;

use AntonioPrimera\FileSystem\File;

trait ZipsFiles
{
	use UsesZipExtension;
	
	public function zip(): File
	{
		$zipFileName = $this->getName() . '.zip';
		return $this->zipTo($this->getParentFolder()->file($zipFileName));
	}
	
	public function zipTo(File|string $destination): File
	{
		return File::instance($this->zipFile($this, $destination));
	}
}