# Path

[![codecov](https://codecov.io/gh/vinogradsoft/path/branch/master/graph/badge.svg?token=H05QAU54L4)](https://codecov.io/gh/vinogradsoft/path)

Библиотека для работы с путями и url.

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

Поддержка рассчитана на генерацию url в различных билдерах. Не предполагалась реализация Psr7 и не будет.
Схема показывает какие данные можно получать и модифицировать.
```
  |---------------------------------------absolute url---------------------------------|
  |                                                                                    |
  |-----------------base url----------------|------------------relative url------------|
  |                                         |                                          |
  |                    authority            |          path            query   fragment|
  |       /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾\|
  |http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment|
   \__/   \___/  \_____/  \___________/ \__/                  \___/
  scheme  user   password     host      port                  suffix
```

```php 
<?php
require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use \Vinograd\Path\Url;

$url = Url::createBlank();

$url->setScheme('https')
    ->setUser('grigor')
    ->setPassword('pass@word')
    ->setHost('host.ru')
    ->setPort('8088')
    ->setPath('/user/index')
    ->setSuffix('.php')
    ->setArrayQuery([
        'key1' => 'value1',
        'key2' => 'value2'
    ])->setFragment('fragment');

$url->updateSource();

printUrl($url);
echo '<br><br>####################################';

$url = new Url('https://grigor:pass%40word@host.ru:8088/user/index?key1=value1&key2=value2#fragment');
$url->setSuffix('.php');
$url->updateSource();

printUrl($url);

function printUrl($url)
{
    echo '<br><b>Authority:</b> ', $url->getAuthority();
    echo '<br><b>BaseUrl:</b> ', $url->getBaseUrl();
    echo '<br><b>RelativeUrl:</b> ', $url->getRelativeUrl();
    echo '<br><b>AbsoluteUrl:</b> ', $url; //$url->getSource();
    echo '<br>';

    echo '<br><b>getScheme:</b> ', $url->getScheme();
    echo '<br><b>getUser:</b> ', $url->getUser();
    echo '<br><b>getPassword:</b> ', $url->getPassword();
    echo '<br><b>getHost:</b> ', $url->getHost();
    echo '<br><b>getPort:</b> ', $url->getPort();
    echo '<br><b>getPath:</b> ', $url->getPath();
    echo '<br><b>getSuffix:</b> ', $url->getSuffix();
    echo '<br><b>getQuery:</b> ', $url->getQuery();
    echo '<br><b>getFragment:</b> ', $url->getFragment();
}
```
Результат:
``` 
Authority: grigor:pass%40word@host.ru:8088
BaseUrl: https://grigor:pass%40word@host.ru:8088
RelativeUrl: /user/index.php?key1=value1&key2=value2#fragment
AbsoluteUrl: https://grigor:pass%40word@host.ru:8088/user/index.php?key1=value1&key2=value2#fragment

getScheme: https
getUser: grigor
getPassword: pass@word
getHost: host.ru
getPort: 8088
getPath: /user/index.php
getSuffix: .php
getQuery: key1=value1&key2=value2
getFragment: fragment

####################################
Authority: grigor:pass%40word@host.ru:8088
BaseUrl: https://grigor:pass%40word@host.ru:8088
RelativeUrl: /user/index.php?key1=value1&key2=value2#fragment
AbsoluteUrl: https://grigor:pass%40word@host.ru:8088/user/index.php?key1=value1&key2=value2#fragment

getScheme: https
getUser: grigor
getPassword: pass@word
getHost: host.ru
getPort: 8088
getPath: /user/index.php
getSuffix: .php
getQuery: key1=value1&key2=value2
getFragment: fragment
```

По умолчанию созданный url ни как не кодируется. Такое поведение можно изменить написав свою стратегию создания url, реализовав
интерфейс \Vinograd\Path\UrlStrategy. По умолчанию используется \Vinograd\Path\DefaultUrlStrategy, для передачи на
постобработку созданного url внешним системам - шаблонизаторам с возможностью кодирования и/или другим системам.