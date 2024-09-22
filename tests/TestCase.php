<?php
namespace AntonioPrimera\FileSystem\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected string $sandboxPath = __DIR__ . '/Sandbox';
	
	protected function setUp(): void
	{
		parent::setUp();
		$this->cleanupContextFolder();
		$this->createContextFolder();
	}
	
	protected function tearDown(): void
	{
		parent::tearDown();
		$this->cleanupContextFolder();
	}
	
	//--- Context setup -----------------------------------------------------------------------------------------------
	
	protected function createContextFolder(): void
	{
		if (!is_dir($this->sandboxPath))
			mkdir($this->sandboxPath);
		
		$this->assertDirectoryExists($this->sandboxPath);
	}
	
	//--- Context cleanup ---------------------------------------------------------------------------------------------
	
	protected function cleanupContextFolder(): void
	{
		//delete all files and folders in the context folder
		$this->deleteFolder($this->sandboxPath);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function deleteFolder($folder): void
	{
		if (!is_dir($folder))
			return;
		
		$files = glob($folder . '/*');
		foreach ($files as $file) {
			if (is_file($file))
				unlink($file);
			else
				$this->deleteFolder($file);
		}
		
		rmdir($folder);
	}
}