# PHP библиотека для чтения файлов в формате MXL - табличных документов 1С:Предприятие
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/vampirus/mxl-reader/master/LICENSE)

Поддерживается 6 версия формата. Умеет читать только текстовые данные. Результат возвращаетсяв кодировке UTF-8.


## Установка
Вы можете установить данный пакет с помощью composer:

```
composer require vampirus/mxl-reader
```


## Использование

```php
$mxl = new \VampiRUS\MxlReader\Mxl($pathToFile);
$data = $mxl->getDataAsArray();
```

в `$data` получим двумерный массив с данными из файла