# BOLT11 Lightning Invoice

[![Build Status](https://api.travis-ci.org/dcentrica/lightning-bolt11.svg?branch=master)](https://travis-ci.org/dcentrica/lightning-bolt11)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dcentrica/lightning-bolt11/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dcentrica/lightning-bolt11/?branch=master)
[![License](https://poser.pugx.org/dcentrica/lightning-bolt11/license.svg)](https://github.com/dcentrica/lightning-bolt11/blob/master/LICENSE.md)


## What is this?

Jan 2019: A WORK IN PROGRESS!

This is a PHP fork of the Python3 [rustyrussell/lightning-payencode](https://github.com/rustyrussell/lightning-payencode) library. A PHP library for encoding payment data suited to [Lightning Network](https://lightning.network) payments as QR codes.

The library adheres to a minimal subset of the [BOLT11 standard](https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md).

## Installation

```bash
composer require dcentrica/lightning-bolt11
```

## Requirements

* &gt;= PHP7.1
* [mpdecimal PHP Extension](http://www.bytereef.org/mpdecimal/)
* [php-decimal](http://php-decimal.io/#installation)

Note: If you're running multiple versions of PHP through the [Ondřej Surý](https://launchpad.net/~ondrej/+archive/ubuntu/php) PPA, ensure you're currently using the desired version of PHP and that the same version
is set for use by `php-config` and `phpize`, e.g.:

```sh
sudo update-alternatives --set php /usr/bin/php7.2
sudo update-alternatives --set php-config /usr/bin/php-config7.2
sudo update-alternatives --set phpize /usr/bin/phpize7.2
```

The following libs and extensions are required to be installed and built into PHP:

```bash
#> sudo apt install libmpdec-dev
#> sudo pecl install decimal
#> sudo phpenmod decimal
```


```bash
#> sudo mv /path/to/php/cli/conf.d/20-decimal.ini /path/to/php/cli/conf.d/30-decimal.ini
```

Check all is well:

```bash
#> php --re decimal
```

## Usage

```php
<?php

use DCentrica\LightningNetwork\Bolt11;

$bolt11 = (new Bolt11())
    ->setPaymentAmount(0.1, 'm')
    ->setNetwork('tb')
    ->setPayeeNodeKey('00102030405060708090001020304050607080900010203040506070809010')
    ->setRecoveryFlag(0)
    ->setTxSignature('Bar')
    ->setPaymentHash('2ba5c0920f71b0e910c2e9fad1adf7269723a1c330d881003a2347a624844984')
    ->setDescription('Paid in full')
    ->setCltvExpiry(12345678);

// Decode, and output suitable for use in a QR code
echo $bolt11->getPreimageAsString();
echo $bolt11->rawAsString();

// Just the Human Readable Parts
var_dump($bolt11->getHumanPart());

// Just the Machine Readable Parts
var_dump($bolt11->getDataPart());
    
```

## Credits

Big-ups to all the [Lightning Network](https://lightning.network) developers.
