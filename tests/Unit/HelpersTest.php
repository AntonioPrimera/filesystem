<?php

use AntonioPrimera\FileSystem\Folder;

it('can create a folder instance using the folder helper', function () {
	$folder = folder(__DIR__);
	expect($folder)->toBeInstanceOf(Folder::class)
		->path->toBe(__DIR__);
});