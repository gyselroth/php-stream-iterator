# php-stream-iterator

[![Build Status](https://travis-ci.org/gyselroth/php-stream-iterator.svg)](https://travis-ci.org/gyselroth/php-stream-iterator)
 [![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/gyselroth/php-stream-iterator/master/LICENSE)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gyselroth/php-stream-iterator/badges/quality-score.png)](https://scrutinizer-ci.com/g/gyselroth/php-stream-iterator)
[![Code Coverage](https://scrutinizer-ci.com/g/gyselroth/php-stream-iterator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gyselroth/php-stream-iterator/?branch=master)
[![GitHub release](https://img.shields.io/github/release/gyselroth/php-stream-iterator.svg)](https://github.com/gyselroth/php-stream-iterator/releases)
[![Latest Stable Version](https://img.shields.io/packagist/v/gyselroth/stream-iterator.svg)](https://packagist.org/packages/gyselroth/stream-iterator)

\StreamIterator\StreamIterator provides a fully PSR-7 compatible stream wrapper for an interator.
You may also pass a callback which handles each yielded iterator entry.
\StreamIterator is also nicely useable on blocking iterators and/or to create realtime stream responses.

# Requirements

* The minimum supported PHP version is 5.6
* The library depends on the following external PHP libraries:
    * psr/http-message (^1.0)

# Installation

The package is available at packagist an can be installed via composer:

```
composer require gyselroth/stream-iterator
```

## Documentation

The examples use a simple ArrayIterator, of course you may use any kind of traversable object.

### Read the whole iterator
```php
$my_iterator = new \ArrayIterator([0,1,2,3,4,5]);
$stream = new \StreamIterator\StreamIterator($my_iterator);
$contents = $stream->getContents();
echo $contents; //Prints 012345
```

### Using a callback

Using a callback enables us to operate on each of the yielded iterator elements:
```php
$my_iterator = new \ArrayIterator([0,1,2,3,4,5]);
$stream = new \StreamIterator\StreamIterator($my_iterator, function($item) {
    return '-'.$item;
})

$contents = $stream->getContents();
echo $contents; //Prints -0-1-2-3-4-5
```

### JSON stream example

In this example we create a json output from the example iterator:

```php
$my_iterator = new \ArrayIterator([['foo' => 'bar'], ['foo' => 'bar']]);
$stream = new \StreamIterator\StreamIterator($my_iterator, function($item) {
    if($this->tell() === 0) {
        $string = '[';
    } else {
        $string = ',';
    }

    $string .= json_encode($item);

    if($this->eof()) {
        $string .= ']';
    }

    return $string;
})

$contents = $stream->getContents();
echo $contents; //Prints [{"foo":"bar"},{"foo":"bar"}]
```

### (JSON) stream without buffer
This enables a realtime json stream of an iterator. This also allows to operate on blocking iterators
where \Iterator::next() blocks until a new entry gets yielded. Each iterator item gets printed as soon as it arrives.

>**Note** Some web server have output buffers or gzip enabled, this will not work with a realtime stream. Be sure
that all buffers are completely disabled (For endpoints where a realtime stream is used). For example if you are using Nginx and PHP-FPM you will most likely need
to send a header `header('X-Accel-Buffering', 'no')` to disable the fastcgi nginx buffer. Otherwise nginx will buffer your output.

```php
$my_iterator = new \ArrayIterator([['foo' => 'bar'], ['foo' => 'bar']]);
$stream = new \StreamIterator\StreamIterator($my_iterator, function($item) {
    if($this->tell() === 0) {
        $string = '[';
    } else {
        $string = ',';
    }

    $string .= json_encode($item);

    if($this->eof()) {
        $string .= ']';
    }

    echo $string;
    flush();
    return '';
})

$contents = $stream->getContents(); //Prints [{"foo":"bar"},{"foo":"bar"}]
echo $contents; //Prints "" (Empty string)
```

## Changelog
A changelog is available [here](https://github.com/gyselroth/php-stream-iterator/CHANGELOG.md).

## Contribute
We are glad that you would like to contribute to this project. Please follow the given [terms](https://github.com/gyselroth/php-stream-iterator/blob/master/CONTRIBUTING.md).

## Thanks
This projects use ideas provided by Matthew Weier O'Phinney [phly/psr7examples][https://github.com/phly/psr7examples].
