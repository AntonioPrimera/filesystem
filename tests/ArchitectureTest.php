<?php

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\FileSystemItem;
use AntonioPrimera\FileSystem\Folder;

arch()->expect('AntonioPrimera\FileSystem')
	->not->toUse(['die', 'dd', 'dump', 'ray', 'rd'])
	->and(FileSystemItem::class)->toBeAbstract()
		->toOnlyBeUsedIn([File::class, Folder::class]);
