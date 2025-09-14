# Render

Response helpers for common output types.

## Methods
```php
void json(mixed $data, int $statusCode = 200)
void csv(array $rows, string $filename, int $statusCode = 200)
void view(string $view, array $data = [])   // placeholder
```

## JSON
```php
Render::json(['status' => 'ok']);
```

## CSV
```php
$rows = [['id','name'], [1,'Alice']];
Render::csv($rows, 'users.csv');
```
