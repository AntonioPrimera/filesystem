<?php
namespace AntonioPrimera\FileSystem;

/**
 * @property-read bool $exists
 *
 * @property-read string $name
 * @property-read string $nameWithoutExtension
 * @property-read string $extension
 * @property-read string $folderPath
 * @property-read string $parentFolderPath
 * @property-read false|int $createTime
 * @property-read false|int $modifiedTime
 * @property-read string|false $realPath
 */
abstract class FileSystemItem implements \Stringable
{
	public readonly string $originalPath;
	
	public function __construct(public string $path)
	{
		$this->originalPath = $this->normalizePath($path);
	}
	
	public static function instance(string|self $path): static
	{
		return $path instanceof static ? $path : new static($path);
	}
	
	public function clone(): static
	{
		return new static($this->path);
	}
	
	//--- Getters -----------------------------------------------------------------------------------------------------
	
	public function getName(): string
	{
		return pathinfo($this->path, PATHINFO_BASENAME);
	}
	
	/**
	 * File name without extension, considering the extension has a maximum
	 * given number of parts (e.g: .tar.gz has 2 parts). If the file has
	 * less than the given number of extension parts, the part of
	 * the file name before the first dot is returned.
	 */
	public function getNameWithoutExtension(int $maxExtensionParts = 1): string
	{
		$extension = $this->getExtension($maxExtensionParts);
		return $extension ? substr($this->name, 0, -strlen($extension) - 1) : $this->name;
	}
	
	/**
	 * File extension, without the leading dot, considering the extension has a maximum
	 * given number of parts (e.g: .tar.gz has 2 parts). If the file has less than
	 * the given number of extension parts, the full extension is returned.
	 *
	 * e.g. for a file named 'file.tar.gz' and $maxExtensionParts = 10,
	 * 		the returned extension will be 'tar.gz'
	 */
	public function getExtension(int $maxExtensionParts = 1): string
	{
		$nameParts = explode('.', $this->name);
		$realExtensionPartCount = min($maxExtensionParts, count($nameParts) - 1);
		return $realExtensionPartCount ? implode('.', array_slice($nameParts, -$realExtensionPartCount)) : '';
	}
	
	/**
	 * Absolute path to the containing folder
	 */
	public function getFolderPath(): string
	{
		return dirname($this->path);
	}
	
	/**
	 * Absolute path to the parent folder (same as getFolderPath(), but with a more descriptive name)
	 */
	public function getParentFolderPath(): string
	{
		return dirname($this->path);
	}
	
	public function getCreateTime(): false|int
	{
		return filectime($this->path);
	}
	
	public function getModifiedTime(): false|int
	{
		return filemtime($this->path);
	}
	
	public function getRealPath(): string|false
	{
		return realpath($this->path);
	}
	
	//--- Path operations ---------------------------------------------------------------------------------------------
	
	public function relativePath(string $basePath): string
	{
		return ltrim(str_replace($this->normalizePath($basePath), '', $this->path), DIRECTORY_SEPARATOR);
	}
	
	public function relativeFolderPath(string $basePath): string
	{
		return dirname($this->relativePath($basePath));
	}

	//--- Name checks -------------------------------------------------------------------------------------------------
	
	public function is(string|self $path): bool
	{
		return $this->path === $this->normalizePath((string) $path);
	}
	
	public function nameMatches(string $pattern): bool
	{
		return preg_match($pattern, $this->name);
	}
	
	public function nameMatchParts(string $pattern): array|false
	{
		$matches = [];
		preg_match($pattern, $this->name, $matches);
		return $matches;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function mergePathParts(...$pathParts): string
	{
		return implode(
			DIRECTORY_SEPARATOR,
			array_filter(
				array_map(
					fn ($part, $index) => $index === 0
						? rtrim($part, '/\\')
						: trim($part, '/\\'),
					$pathParts,
					array_keys($pathParts)
				)
			)
		);
	}
	
	protected function normalizePath(string $path): string
	{
		$cleanPath = str_replace('\\', '/', $path);
		return str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);
	}
	
	//--- Abstract methods --------------------------------------------------------------------------------------------
	
	public abstract function exists(): bool;
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __get(string $name)
	{
		$getter = [$this, 'get' . ucfirst($name)];
		if (is_callable($getter))
			return call_user_func($getter);
		
		if ($name === 'exists')
			return $this->exists();
		
		return null;
	}
	
	//--- Interface implementation ------------------------------------------------------------------------------------
	
	public function __toString(): string
	{
		return $this->path;
	}
}