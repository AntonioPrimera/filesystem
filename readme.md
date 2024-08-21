# FileSystem Package

The FileSystem package is a PHP library that provides a set of utilities for working with files and directories
as objects. It is designed to make file and directory manipulation easier and more intuitive.

## Features

- File and directory manipulation: move, delete, and create files and directories.
- File content management: read, write, and replace content in files.
- Path operations: get relative and absolute paths.
- Name checks: check if a file or directory matches a specific pattern.

## Installation

Use composer to install the FileSystem package:

```bash
composer require antonioprimera/filesystem
```

## Usage

The package provides two main classes `File` and `Directory` and another helper class `OS`.

The `File` class represents a file and provides methods for reading, writing, and manipulating file contents,
while the `Folder` class represents a directory and provides methods for working with directories.

The `OS` class provides static methods for handling cross-platform path string operations, like joining paths,
splitting paths, and normalizing paths (e.g. converting path separators to the correct format for the current OS).

### File

Here are some examples of how to use the `File` class:

```php
use AntonioPrimera\FileSystem\File;

// --- Generic methods, available for both File and Folder classes ---

// Create a new file instance
$file = new File('/path/to/file.txt');
//or
$file = File::instance('/path/to/file.txt');

// Get the file name
echo $file->name;

// Get the file name without the extension
echo $file->nameWithoutExtension;

// Get the file extension
echo $file->extension;

// Get the file's containing folder path
echo $file->folderPath;

// Get the file's creation time
echo $file->createTime;

// Get the file's last modification time
echo $file->modifiedTime;

// Get the file's relative path, relative to a specific directory
echo $file->relativePath('/path/to/directory');

// Get the file's containing folder path, relative to a specific directory
echo $file->relativeFolderPath('/path/to/directory');

// Check if the file instance points to a given file path
$isFile = $file->is('/path/to/file.txt');

// Check if the file name matches a specific pattern
$matches = $file->nameMatches('/IMG_[0-9]{4}\.jpg/');

// Get the matches from the file name, for a specific pattern
$matches = $file->getMatches('/IMG_([0-9]{4})\.jpg/');  //see: preg_match for the return value

// --- Methods specific to the File class ---

// Check if the file exists
$exists = $file->exists;

// Get the containing folder as a Folder instance
$folder = $file->folder;

// Get the file contents
$contents = $file->contents;

// Get the file size in bytes
$size = $file->size;

// Get the human-readable file size (e.g. 1.2 MB)
$size = $file->humanReadableFileSize;

// Get the sha256 hash of the file contents
$hash = $file->hash;

// Rename the file
$file->rename(newFileName: 'new-name-without-extension', preserveExtension: true);

// Move the file to a new directory
$file->moveTo(targetFolder: '/path/to/new/directory', overwrite: true);

// Delete the file
$file->delete();

// Write contents to the file
$file->putContents('Hello, World!');

// Copy the file contents of a given file to the current file (overwriting the current file)
$file->copyContentsFromFile(sourceFile: '/path/to/other/file.txt');

// Copy the file contents of the current file to a given file (overwriting the target file)
$file->copyContentsToFile(destinationFile: '/path/to/other/file.txt');

// Replace any placeholders in the file contents with the given values
$file->replaceInFile(['{name}' => 'John Doe', '__DATE__' => date('d.M.Y')]);
```

### Directory

Below are some examples of how to use the `Directory` class. The generic methods are the same as for the `File` class,
because both classes extend the same FileSystemItem class.

```php
use AntonioPrimera\FileSystem\Directory;

// --- Methods specific to the Folder class ---

// Get a Folder instance for a subfolder
$subFolderInstance = $folder->subFolder('subfolder-name');

// Get a File instance for a file in the folder
$fileInstance = $folder->file('file-name.txt');

// Get the files in the folder, as an array of File instances
$files = $folder->files;

// Get the subfolders in the folder, as an array of Folder instances
$subFolders = $folder->folders;

// Get a list of all file names in the folder (and optionally filter by a pattern or using a callable)
$fileNames = $folder->getFileNames('/IMG_[0-9]{4}\.jpg/');
// OR
$fileNames = $folder->getFileNames(fn($name) => str_starts_with($name, 'IMG_'));
// OR without any filter
$fileNames = $folder->fileNames;

// Get a list of all subfolder names in the folder (and optionally filter by a pattern or using a callable)
$subFolderNames = $folder->getFolderNames('/[0-9]{4}/');
// OR
$subFolderNames = $folder->getFolderNames(fn($name) => is_numeric($name));
// OR without any filter
$subFolderNames = $folder->folderNames;

// Get a flat list of all files in the folder and its subfolders recursively, as an array of File instances
$allFiles = $folder->getAllFiles(filter: '/IMG_[0-9]{4}\.jpg/');    //filter by a pattern or using a callable
// OR without any filter
$allFiles = $folder->allFiles;

// Create the current folder (corresponding to the current instance) if it doesn't exist
$folder->create();

// Rename the folder
$folder->rename('new-folder-name');

// Move the folder to a new directory
$folder->move('/path/to/new/parent/directory', overwrite: true);

// Move a list of files (an array of string path names or File instances) to the folder
$folder->moveFilesToSelf(['/path/to/file1.txt', '/path/to/file2.txt', $fileInstance]);

// Delete the folder and all its contents (recursively)
$folder->delete(deep: true);

// Check if the folder contains a file with a specific name
$containsFile = $folder->hasFile('file-name.txt');

// Check if the folder contains a subfolder with a specific name
$containsFolder = $folder->hasSubFolder('subfolder-name');

// Check if the folder has all the files in a list of file names
$hasAllFiles = $folder->hasFiles(['file1.txt', 'file2.txt']);

// Check if the folder has all the subfolders in a list of folder names
$hasAllSubFolders = $folder->hasSubFolders(['subfolder1', 'subfolder2']);

// Check if the folder is empty (contains no files and no folders)
$isEmpty = $folder->isEmpty();

// Check if the folder is not empty (contains at least one file or folder)
$notEmpty = $folder->isNotEmpty();
```

### OS

The `OS` class provides static methods for detecting the OS and handling cross-platform path string operations:

```php
use AntonioPrimera\FileSystem\OS;

// Determine the current OS
OS::isWindows();    //returns true if the current OS is Windows
OS::isLinux();      //returns true if the current OS is Linux
OS::isMac();        //returns true if the current OS is MacOS
OS::isOsx();        //returns true if the current OS is MacOS
OS::isUnix();       //returns true if the current OS is Unix (Linux or MacOS)

// Determine if a string path is absolute
OS::isAbsolutePath('/path/to/file.txt');    //returns true
OS::isAbsolutePath('path/to/file.txt');     //returns false
OS::isAbsolutePath('C:\path\to\file.txt');  //returns true

// Cleans up paths, normalizes separators and returns correct OS specific paths
// All the following calls return '/path/to/file.txt' on Unix and '\path\to\file.txt' on Windows
$path = OS::path('/path/to/file.txt');
$path = OS::path('path', 'to', 'file.txt');
$path = OS::path('/path', '/to', '', null, '\\file.txt');

// Normalizes path separators in a string path if no cleanup is needed, returns '/path/to/file.txt' on Unix, '\path\to\file.txt' on Windows
$path = OS::normalizePathSeparators('/path/to/file.txt');

// Splits a path string into an array of path parts (same result on Unix and Windows)
// All the following calls return ['path', 'to', 'file.txt']
$parts = OS::splitPath('/path/to/file.txt');
$parts = OS::splitPath('\\path\\to\\file.txt');
$parts = OS::splitPath('path/', '\\to\\', 'file.txt');                  //works with mixed separators
$parts = OS::splitPath('path\\', '\\to\\', 'file.txt');                 //works with redundant separators
$parts = OS::splitPath('\\path/to', '/', '', null, '\\', 'file.txt');   //works with dirty paths
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
Also, if you can improve this documentation, please do so.

## License

MIT