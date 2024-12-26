# Collection

When the OpenAgenda `GET` endpoint could return multiple results, a Collection is created to iterate over.

Collection implements `Iterator` and `JsonSerializable`.

## Collection methods

### toArray() `array`
Return all results as array.

### toJson() `string`
Return all results and collection as JSON string.

### first() `mixed`
Get first collection element.

### last() `mixed`
Get last collection element.
