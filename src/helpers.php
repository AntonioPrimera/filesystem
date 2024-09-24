<?php
use AntonioPrimera\FileSystem\Folder;

/**
 * Helper function to create a Folder instance
 */
function folder(Folder|string $path): Folder
{
	return Folder::instance($path);
}