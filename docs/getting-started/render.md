# Rendering Responses (`Render`)

The `Render` class provides helpers for sending responses.

### JSON response

```php
use Stilmark\Base\Render;

Render::json(['success' => true]);
```

### CSV response

```php
$data = [
    ['id' => 1, 'name' => 'Alice'],
    ['id' => 2, 'name' => 'Bob']
];

Render::csv($data, 'users.csv');
```

### View rendering

A `view()` method exists as a placeholder for template rendering (TBD).
