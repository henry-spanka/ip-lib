[![Build Status](https://api.travis-ci.org/mlocati/ip-lib.svg?branch=master)](https://travis-ci.org/mlocati/ip-lib)
[![HHVM Status](http://hhvm.h4cc.de/badge/mlocati/ip-lib.svg?style=flat)](http://hhvm.h4cc.de/package/mlocati/ip-lib)
[![StyleCI Status](https://styleci.io/repos/54139375/shield)](https://styleci.io/repos/54139375)
[![Coverage Status](https://coveralls.io/repos/github/mlocati/ip-lib/badge.svg?branch=master)](https://coveralls.io/github/mlocati/ip-lib?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mlocati/ip-lib/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mlocati/ip-lib/?branch=master)

# IPLib - Handle IPv4, IPv6 and IP ranges


## Introduction

This library can handle IPv4, IPv6 addresses, as well as IP ranges, in CIDR formats (like `::1/128` or `127.0.0.1/32`) and in pattern format (like `::*:*` or `127.0.*.*`).


## Requirements

The only requirement is PHP 5.3.3.
__No external dependencies__ and __no special PHP configuration__ are needed (yes, it will __always work__ even if PHP has not been built with IPv6 support!).


## Manual installation

[Download](https://github.com/mlocati/ip-lib/releases) the latest version, unzip it and add these lines in our PHP files:

```php
require_once 'path/to/iplib/ip-lib.php';
```


## Installation with Composer

Simply run `composer install mlocati/ip-lib`, or add these lines to your `composer.json` file:

```json
"require": {
    "mlocati/ip-lib": "1.*"
}
```


## Sample usage


### Parse an address

To parse an IPv4 address:

```php
$address = \IPLib\Address\IPv4::fromString('127.0.0.1');
```

To parse an IPv6 address:

```php
$address = \IPLib\Address\IPv6::fromString('::1');
```

To parse an address in any format (IPv4 or IPv6):

```php
$address = \IPLib\Factory::addressFromString('::1');
$address = \IPLib\Factory::addressFromString('127.0.0.1');
```


### Parse an IP address range

To parse a subnet (CIDR) range:

```php
$range = \IPLib\Range\Subnet::fromString('127.0.0.1/24');
$range = \IPLib\Range\Subnet::fromString('::1/128');
```

To parse a pattern (asterisk notation) range:

```php
$range = \IPLib\Range\Pattern::fromString('127.0.0.*');
$range = \IPLib\Range\Pattern::fromString('::*');
```

To parse an andress as a range:

```php
$range = \IPLib\Range\Single::fromString('127.0.0.1');
$range = \IPLib\Range\Single::fromString('::1');
```

To parse a range in any format:

```php
$range = \IPLib\Factory::rangeFromString('127.0.0.*');
$range = \IPLib\Factory::rangeFromString('::1/128');
$range = \IPLib\Factory::rangeFromString('::');
```


### Format addresses and ranges

Both IP addresses and ranges have a `toString` method that you can use to retrieve a textual representation:
 
```php
echo \IPLib\Factory::addressFromString('127.0.0.1')->toString();
// prints 127.0.0.1
echo \IPLib\Factory::addressFromString('127.000.000.001')->toString();
// prints 127.0.0.1
echo \IPLib\Factory::addressFromString('::1')->toString();
// prints ::1
echo \IPLib\Factory::addressFromString('0:0::1')->toString();
// prints ::1
echo \IPLib\Factory::rangeFromString('0:0::1/64')->toString();
// prints ::1/64
```

When working with IPv6, you may want the full (expanded) representation of the addresses. In this case, simply use a `true` parameter for the `toString` method:

```php
echo \IPLib\Factory::addressFromString('::')->toString(true);
// prints 0000:0000:0000:0000:0000:0000:0000:0000
echo \IPLib\Factory::addressFromString('::1')->toString(true);
// prints 0000:0000:0000:0000:0000:0000:0000:0001
echo \IPLib\Factory::addressFromString('fff::')->toString(true);
// prints 0fff:0000:0000:0000:0000:0000:0000:0000
echo \IPLib\Factory::addressFromString('::0:0')->toString(true);
// prints 0000:0000:0000:0000:0000:0000:0000:0000
echo \IPLib\Factory::addressFromString('1:2:3:4:5:6:7:8')->toString(true);
// prints 0001:0002:0003:0004:0005:0006:0007:0008
echo \IPLib\Factory::rangeFromString('0:0::1/64')->toString();
// prints 0000:0000:0000:0000:0000:0000:0000:0001/64
```


### Check if an address is contained in a range

All the range types offer a `contains` method, and all the IP address types offer a `matches` method: you can call them to check if an address is contained in a range:

```php
$address = \IPLib\Factory::addressFromString('1:2:3:4:5:6:7:8');
$range = \IPLib\Factory::rangeFromString('0:0::1/64');

$contained = $address->matches($range);
// that's equivalent to
$contained = $range->contains($address);
```

Please remark that if the address is IPv4 and the range is IPv6 (or vice-versa), the result will always be `false`.


### Getting the type of an IP address

If you want to know if an address is within a private network, or if it's a public IP, or whatever you want, you can use the `getRangeType` method:

```php
$address = \IPLib\Factory::addressFromString('::');

$typeID = $address->getRangeType();

$typeName = \IPLib\Range\Type::getName();
```

The most notable values of the range type ID are:
-  `\IPLib\Range\Type::T_UNSPECIFIED` if the address is all zeros (`0.0.0.0` or `::`)
-  `\IPLib\Range\Type::T_LOOPBACK` if the address is the localhost (usually `127.0.0.1` or `::1`)
-  `\IPLib\Range\Type::T_PRIVATENETWORK` if the address is in the local network (for instance `192.168.0.1` or `fc00::1`)
-  `\IPLib\Range\Type::T_PUBLIC` if the address is for public usage (for instance `104.25.25.33` or `2001:503:ba3e::2:30`)

 
### Using a database

This package offers a great feature: you can store address ranges in a database table, and check if an address is contained in one of the saved ranges with a simple query.

To save a range, you need to store the address type (for IPv4 it's `4`, for IPv6 it's `6`), as well as two values representing the start and the end of the range.
These methods are:
```php
$range->getAddressType();
$range->getComparableStartString();
$range->getComparableEndString();
```

Let's assume that you saved the type in a field called `addressType`, and the range boundaries in two fields called `rangeFrom` and `rangeTo`.

When you want to check if an address is within a stored range, simply use the `getComparableString` method of the address and check if it's between the fields `rangeFrom` and `rangeTo`, and check if the stored `addressType` is the same as the one of the address instance you want to check.

Here's a sample code:

```php
/*
 * Let's assume that:
 * - $pdo is a PDO instance
 * - $range is a range object
 * - $address is an address object
 */

// Save the $range object
$insertQuery = $pdo->prepare('
    insert into ranges (addressType, rangeFrom, rangeTo)
    values (:addressType, :rangeFrom, :rangeTo)
');
$insertQuery->execute(array(
    ':addressType' => $range->getAddressType(),
    ':rangeFrom' => $range->getComparableStartString(),
    ':rangeTo' => $range->getComparableEndString(),
));

// Retrieve the saved ranges where an address $address falls:
$searchQuery = $pdo->prepare('
    select * from ranges
    where addressType = :addressType
    and :address between rangeFrom and rangeTo
');
$searchQuery->execute(array(
    ':addressType' => $address->getAddressType(),
    ':address' => $address->getComparableString(),
));
$rows = $searchQuery->fetchAll();
$searchQuery->closeCursor();
```
