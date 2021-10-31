# Path

Библиотека для работы с путями и url. 
Альфа-версия тестами не покрыта.

PHP >=8.0

Установка
---------

Предпочтительный способ установки - через [composer](http://getcomposer.org/download/).

Запустите команду

```
php composer require vinogradsoft/path "2.0.0"
```

или добавьте в composer.json

```
"vinogradsoft/path": "^2.0.0"
```
### Path

```php 
<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use Vinograd\Path\Path;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$path = new Path('/__NAME__/__NAME__Scanner/__NAME2__Driver');

$path->replaceAll([
    '__NAME__' => 'User',
    '__NAME2__' => 'Filesystem',
]);
$path->updateSource();
echo '<br>', $path;
$path->setAll(['path','to','file.txt']);

$path->updateSource();
echo '<br>', $path;
```
Результат:
``` 
/User/UserScanner/FilesystemDriver
path/to/file.txt
```

### Добавилась поддержка URL. 

Схема показывает какие данные можно получать и модифицировать.
```
     |------------------------------------absolute url--------------------------------|
     |                                                                                |
     |----------------base url-----------------|--------------relative url------------|
     |                                         |                                      |
     |                    authority            |      path          query    fragment |
     |       /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾\ |
      http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment
      \__/   \___/  \_____/  \___________/ \__/
     scheme  user  password      host      port
```

```php 
<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use \Vinograd\Path\Url;

$url = new Url('http://path.local');

$url->setEncodingType(PHP_QUERY_RFC3986);
$url->setPath('user/index')
    ->setScheme('https')
    ->setUser('grigor')
    ->setPassword('password')
    ->setPort('8088')
    ->addParameter('name', [
        'value 1',
        'value 2',
        'value 3',
    ])
    ->setFragment('fragment');
$url->updateSource();

echo '<br>', $url->getAuthority();
echo '<br>', $url->getBaseUrl();
echo '<br>', $url->getRelativeUrl();
echo '<br>', $url; //$url->getSource();
```
Результат:
``` 
grigor:password@path.local:8088
https://grigor:password@path.local:8088
user/index?name%5B0%5D=value%201&name%5B1%5D=value%202&name%5B2%5D=value%203#fragment
https://grigor:password@path.local:8088/user/index?name%5B0%5D=value%201&name%5B1%5D=value%202&name%5B2%5D=value%203#fragment
```