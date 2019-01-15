## What is this?

Jan 2019: A WORK IN PROGRESS!

A PHP library for encoding payment data suited to [Lightning Network](https://lightning.network) payments. It's ideally suited for use supplying source data for
rendering into a Lightning payment QR code.

The module adheres to a minimal subset of the [BOLT11 standard](https://github.com/lightningnetwork/lightning-rfc/blob/master/11-payment-encoding.md).

## Installation

```bash
composer require dcentrica/lightning-bolt11
```

## Usage

```php
<?php

use DCentrica\LightningNetwork\Bolt11;

$bolt11 = (new Bolt11())
    ->setPaymentAmount(0.1, 'm')
    ->setNetwork('tb')
    // This comes from a Lightning wallet e.g., from lnd or c-lightning
    ->setPayeeNodeKey('00102030405060708090001020304050607080900010203040506070809010')
    ->setRecoveryFlag(0)
    ->setTxSignature('TODO')
    // This comes from a Lightning wallet e.g., from lnd or c-lightning
    ->setPaymentHash('2ba5c0920f71b0e910c2e9fad1adf7269723a1c330d881003a2347a624844984')
    ->setDescription('Paid in full')
    ->setCltvExpiry(12345678) // <-- pvjluez: timestamp (1496314658) = Obtain a 4byte int from time()
    ->setTimestamp(12345678);

// Decode, and output suitable for use in a QR code
echo $bolt11->preImage();
echo $bolt11->raw();

// Just the Human Readable Parts
var_dump($bolt11->getHumanPart());

// Just the Machine Readable Parts
var_dump($bolt11->getDataPart());
    
```

## Credits

This PHP library is a port of the (bitcoinjs/bolt11)[https://github.com/bitcoinjs/bolt11] JavaScript library, big thanks to the multiple authors.
