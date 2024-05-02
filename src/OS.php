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
}