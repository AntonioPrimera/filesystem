<?php
namespace AntonioPrimera\FileSystem;

class OS
{
	
	/**
	 * Returns true if the current operating system is Windows
	 */
	public static function isWindows(): bool
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
	
	/**
	 * Returns true if the current operating system is Linux
	 */
	public static function isLinux(): bool
	{
		return strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX';
	}
	
	/**
	 * Returns true if the current operating system is OSX
	 */
	public static function isOsx(): bool
	{
		return strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
	}
	
	/**
	 * Returns true if the current operating system is Unix based (Linux, OSX, etc.)
	 */
	public static function isUnix(): bool
	{
		return strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN';
	}
	
	public static function isRelativePath(string $path): bool
	{
		return !self::isAbsolutePath($path);
	}
	
	public static function isAbsolutePath(string $path): bool
	{
		$cleanPath = ltrim($path);
		return $cleanPath[0] === '/' || preg_match('/^[a-z]:/i', $cleanPath);
	}
	
	public static function path(...$pathParts): string
	{
		$rawParts = $pathParts;			//store the raw parts for later use (never modify the function arguments)
		$cleaner = " \n\r\t\v\0\\/";	//empty spaces and slashes
		
		//get the first part (being the root part, it should only be right trimmed)
		$firstPart = array_shift($rawParts);
		$cleanFirstPart = rtrim(self::normalizePathSeparators($firstPart), $cleaner);
		
		//if there are no more parts, return the cleaned first part
		if (empty($rawParts))
			return $cleanFirstPart;
		
		//clean the rest of the parts and join them together
		$cleanParts = [$cleanFirstPart];
		foreach ($rawParts as $part)
			$cleanParts[] = $part ? trim(self::normalizePathSeparators($part), $cleaner) : null;
		
		return implode(DIRECTORY_SEPARATOR, array_filter($cleanParts, fn($part) => $part));
	}
	
	public static function normalizePathSeparators(string $path): string
	{
		return DIRECTORY_SEPARATOR === '/'
			? str_replace('\\', DIRECTORY_SEPARATOR, $path)
			: str_replace('/', DIRECTORY_SEPARATOR, $path);
	}
	
	/**
	 * Returns the parts of a path (or several path parts) as an array
	 * It normalizes the path separators and merges all given
	 * parts into a single path before splitting it
	 */
	public static function pathParts(...$pathParts): array
	{
		return explode(DIRECTORY_SEPARATOR, trim(self::path(...$pathParts), DIRECTORY_SEPARATOR));
	}
}