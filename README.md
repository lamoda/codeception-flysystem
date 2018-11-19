# Codeception FlySystem Extension

This extension supports working with [FlySystem](https://flysystem.thephpleague.com/) with several adapters.

Provides a set of methods for checking and modifying files on remote storage.

## Installation

1. Install library
    ```bash
    composer require lamoda/codeception-flysystem
    ```

2. Add configuration to codeception.yml
    ```yaml
    modules:
        config:
            \Lamoda\Codeception\Extension\FlySystemModule:
                adapters:
                    webdav:
                        builderAdapter: \Lamoda\Codeception\Extension\AdapterBuilder\WebdavAdapterBuilder
                        config:
                            baseUri: "http://webdav-host"
                            userName: "userName"
                            password: "password"
                            authType: "authType"
                    sftp:
                        builderAdapter: \Lamoda\Codeception\Extension\AdapterBuilder\SftpAdapterBuilder
                        config:
                            host: "http://sftp-host"
                            username: "username"
                            password: "password"
                            port: "22"
                            root: "/"
    ```

3. Include to suite
    ```yaml
    modules:
        enabled:
            - \Lamoda\Codeception\Extension\FlySystemModule
    ```

## Supported adapters

### [sftp](https://flysystem.thephpleague.com/adapter/sftp/)

Configuration example:

```yaml
modules:
    config:
        \Lamoda\Codeception\Extension\FlySystemModule:
            adapters:
                sftp:
                    builderAdapter: \Lamoda\Codeception\Extension\AdapterBuilder\SftpAdapterBuilder
                    config:
                        host: "http://sftp-host"
                        username: "username"
                        password: "password"
                        port: "22"
                        root: "/"
```

Usage:

```php
$fileSystem = $this->tester->getFileSystem('sftp');
```

### [webdav](https://flysystem.thephpleague.com/adapter/webdav/)

Configuration example:

```yaml
modules:
    config:
        \Lamoda\Codeception\Extension\FlySystemModule:
            adapters:
                webdav:
                    builderAdapter: \Lamoda\Codeception\Extension\AdapterBuilder\WebdavAdapterBuilder
                    config:
                        baseUri: "http://webdav-host"
                        userName: "userName"
                        password: "password"
                        authType: "authType"
```

Usage:

```php
$fileSystem = $this->tester->getFileSystem('webdav');
```

## Usage

Get instance of FileSystem by name from config:

```php
$fileSystem = $this->tester->getFileSystem('sftp');
```

Modify file on remote server:

```php
$fileSystem->clearDir('/path/to/dir');
$fileSystem->writeFile('test.txt', 'Hello world!');
$fileSystem->copyFile('test.txt', 'test_copy.txt');
$fileSystem->deleteFile('test.txt');

$files = $fileSystem->grabFileList('/path/to/dir');
```

Check files on remote server:

```php
$fileSystem->canSeeFile('test_copy.txt');
$fileSystem->cantSeeFile('test.txt');

$fileSystem->seeInFile('test_copy.txt', 'Hello');

$fileSystem->seeFilesCount('/path/to/dir', 1);

$fileSystem->seeFileFoundMatches('/copy$/', '/path/to/dir');
$fileSystem->dontSeeFileFoundMatches('/test$/', '/path/to/dir');
```

## Development

### PHP Coding Standards Fixer

```bash
make php-cs-check
make php-cs-fix
```
