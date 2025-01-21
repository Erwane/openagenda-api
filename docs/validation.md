# Validation

This library embed some validations to help your project to validate data before send.

**Usage**
```php
use OpenAgenda\Validation;
$success = Validation::lang($myLang); // bool
```

## Rules

### `url($check)`: bool

Check the value is an url.

### `phone($check)`: bool

Check the value is a phone number.

### `lang($check)`: bool

Check the lang is accepted by OpenAgenda.

### `multilingual(array $check, ?int $maxLength = null)`: string|bool

Check the data is a multilingual OpenAgenda array.  
You can specify `maxLength` to limit each lang/value length.
```php
$success = Validation::multilingual(['fr' => 'Lorem ispum'], 20);
```
This method can return a string with error detail.

### `image(string|resource $check, float $max)`: bool

Check the string or resource is an image and don't exceed `$max` size (in MegaBytes)
