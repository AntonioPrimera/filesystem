<?php
namespace AntonioPrimera\FileSystem;

/**
 * @property-read bool $exists
 *
 * @property-read string $name
 * @property-read string $nameWithoutExtension
 * @property-read string $extension
 * @property-read string $folderPath
 * @property-read false|int $createTime
 * @property-read false|int $modifiedTime
 */
abstract class FileSystemItem implements \Stringable
{
	public readonly string $originalPath;
	
	public function __construct(public string $path)
	{
		$this->originalPath = $path;
	}
	
	public static function instance(string|self $path): static
	{
		return $path instanceof static ? $path : new static($path);
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
		return pathinfo($this->path, PATHINFO_DIRNAME);
	}
	
	public function getCreateTime(): false|int
	{
		return filectime($this->path);
	}
	
	public function getModifiedTime(): false|int
	{
		return filemtime($this->path);
	}
	
	//--- Path operations ---------------------------------------------------------------------------------------------
	
	public function relativePath(string $basePath): string
	{
		return ltrim(str_replace($basePath, '', $this->path), DIRECTORY_SEPARATOR);
	}
	
	public function relativeFolderPath(string $basePath): string
	{
		return dirname($this->relativePath($basePath));
	}
	
	//--- Name checks -------------------------------------------------------------------------------------------------
	
	public function is(string|self $path): bool
	{
		return $this->path === (string) $path;
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
	
	//--- Abstract methods --------------------------------------------------------------------------------------------
	
	public abstract function exists(): bool;
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __get(string $name)
	{
		if (is_callable([$this, 'get' . ucfirst($name)]))
			return call_user_func([$this, 'get' . ucfirst($name)]);
		
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