<?php
use AntonioPrimera\FileSystem\OS;

it('can correctly determine if a path is absolute regardless of the operating system', function () {
    expect(OS::isAbsolutePath('/home/user'))->toBeTrue()
		->and(OS::isAbsolutePath('C:\\Users\\user'))->toBeTrue()
		->and(OS::isAbsolutePath('f:\\Users\\user'))->toBeTrue()
		->and(OS::isAbsolutePath('home/user'))->toBeFalse()
		->and(OS::isAbsolutePath('Users\\user'))->toBeFalse()
		->and(OS::isAbsolutePath('\\Users\\user'))->toBeFalse();
	
});

it('can correctly determine if a path is relative regardless of the operating system', function () {
    expect(OS::isRelativePath('home/user'))->toBeTrue()
		->and(OS::isRelativePath('Users\\user'))->toBeTrue()
		->and(OS::isRelativePath('\\Users\\user'))->toBeTrue()
		
		->and(OS::isRelativePath('/home/user'))->toBeFalse()
		->and(OS::isRelativePath('C:\\Users\\user'))->toBeFalse()
		->and(OS::isRelativePath('f:\\Users\\user'))->toBeFalse();
});

it('can correctly normalize path separators in a path string', function () {
    $expectedPath = implode(DIRECTORY_SEPARATOR, ['relative', 'path', 'to', 'fileOrFolder']);

    expect(OS::normalizePathSeparators('relative/path/to/fileOrFolder'))->toEqual($expectedPath)
		->and(OS::normalizePathSeparators('relative\\path\\to\\fileOrFolder'))->toEqual($expectedPath)
		->and(OS::normalizePathSeparators('relative/path\\to/fileOrFolder'))->toEqual($expectedPath)
		->and(OS::normalizePathSeparators('relative\\path/to\\fileOrFolder'))->toEqual($expectedPath);
});

it('can correctly normalize and clean up a single relative path', function () {
    $expectedPath = implode(DIRECTORY_SEPARATOR, ['relative', 'path', 'to', 'fileOrFolder']);

    expect(OS::path('relative/path/to/fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('relative\\path\\to\\fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('relative/path\\to/fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('relative\\path/to\\fileOrFolder'))->toEqual($expectedPath);
});

it('can correctly normalize and clean up multiple relative path parts', function () {
    $expectedPath = implode(DIRECTORY_SEPARATOR, ['relative', 'path', 'to', 'fileOrFolder']);

    expect(OS::path('relative', 'path', 'to', 'fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('relative', 'path', 'to', 'fileOrFolder', ''))->toEqual($expectedPath)
		->and(OS::path('relative', 'path', 'to', 'fileOrFolder', '/'))->toEqual($expectedPath)
		->and(OS::path('relative', 'path', 'to', 'fileOrFolder', '\\'))->toEqual($expectedPath)
		->and(OS::path('relative', 'path', 'to', 'fileOrFolder', '\\', "\t \\ "))->toEqual($expectedPath);
});

it('can correctly normalize and clean up a single absolute path', function () {
    $expectedPath = '/' . implode(DIRECTORY_SEPARATOR, ['absolute', 'path', 'to', 'fileOrFolder']);

    expect(OS::path('/absolute/path/to/fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('\\absolute\\path\\to\\fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('/absolute/path\\to/fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('\\absolute\\path/to\\fileOrFolder'))->toEqual($expectedPath);
});

it('can correctly normalize and clean up multiple absolute path parts', function () {
    $expectedPath = '/' . implode(DIRECTORY_SEPARATOR, ['absolute', 'path', 'to', 'fileOrFolder']);

    expect(OS::path('/absolute', 'path', 'to', 'fileOrFolder'))->toEqual($expectedPath)
		->and(OS::path('\\absolute', 'path', 'to', 'fileOrFolder', ''))->toEqual($expectedPath)
		->and(OS::path('/absolute', 'path', 'to', 'fileOrFolder', '/'))->toEqual($expectedPath)
		->and(OS::path('\\absolute', 'path', 'to', 'fileOrFolder', '\\'))->toEqual($expectedPath)
		->and(OS::path('/absolute', 'path', 'to', 'fileOrFolder', '\\', "\t \\ "))->toEqual($expectedPath);
});

it('can split a path into an array of path parts', function () {
    $expectedParts = ['absolute', 'path', 'to', 'fileOrFolder'];

    //works with forward slashes
    expect(OS::pathParts('/absolute/path/to/fileOrFolder'))->toEqual($expectedParts)
		//works with backslashes
		->and(OS::pathParts('\\absolute\\path\\to\\fileOrFolder'))->toEqual($expectedParts)
		
		//works with mixed separators
		->and(OS::pathParts('/absolute/path\\to/fileOrFolder'))->toEqual($expectedParts)
		->and(OS::pathParts('\\absolute\\path/to\\fileOrFolder'))->toEqual($expectedParts)
		
		//works with several path parts
		->and(OS::pathParts('\\absolute', '\\path', 'to\\fileOrFolder'))->toEqual($expectedParts)
		
		//works with empty parts
		->and(OS::pathParts('\\absolute', '\\path', '/', '\\', '', ' ', null, 'to\\fileOrFolder'))->toEqual($expectedParts);
});

it('can handle an empty path', function () {
    expect(OS::path())->toEqual('')
		->and(OS::path(''))->toEqual('')
		->and(OS::path('', '', ''))->toEqual('')
		->and(OS::path(null))->toEqual('')
		->and(OS::path('/'))->toEqual('')
		->and(OS::path('\\'))->toEqual('')
		->and(OS::pathParts())->toEqual([])
		->and(OS::pathParts(''))->toEqual([])
		->and(OS::pathParts('', '', ''))->toEqual([])
		->and(OS::pathParts(null))->toEqual([])
		->and(OS::pathParts('/'))->toEqual([])
		->and(OS::pathParts('\\'))->toEqual([]);
	
});

it('can determine the current operating system', function () {
	expect(OS::isUnix())->toEqual(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		->and(OS::isUnix())->toBeBool()
		->and(OS::isWindows())->toBeBool()
		->and(OS::isWindows())->toEqual(!OS::isUnix())
		->and(OS::isMac())->toEqual(OS::isOsx())
		->and(OS::isMac())->toBeBool()
		->and(OS::isMac())->toBe(strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN')
		->and(OS::isLinux())->toBeBool()
		->and(OS::isLinux())->toBe(strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX');
});
