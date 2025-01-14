# Collection

When OpenAgenda API return multiple results, they are append in a Collection object.

Collection implements `Countable`, `Iterator` and `JsonSerializable`.

## Methods

### count()
```php
$c = new Collection(['red', 'blue', 'green']);
count($c); // return 3
```

### first()
Get first Collection element.
```php
$c = new Collection(['red', 'blue', 'green']);
$c->first(); // 'red'
```

### last()
Get last Collection element.
```php
$c = new Collection(['red', 'blue', 'green']);
$c->last(); // 'green'
```

### toArray()
Return Collection as array.
All object elements with `toArray()` are also converted.

### json encode
```php
$c = new Collection(['red', 'blue', 'green']);
json_encode($c); // '["red","blue","green"]'
```
