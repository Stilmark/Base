# Returning JSON & CSV

Demonstrates sending JSON and CSV using `Render` and controller helpers.

## JSON in a controller
```php
use Stilmark\Base\Controller;

class UsersController extends Controller
{
    public function show()
    {
        $user = ['id' => 1, 'name' => 'Alice'];
        return $this->json($user); // 200 OK with application/json
    }
}
```

## JSON anywhere with `Render`
```php
use Stilmark\Base\Render;

Render::json(['ok' => true], 201);
```

## CSV export
```php
use Stilmark\Base\Render;

$rows = [
    ['id','name','email'],
    [1,'Alice','alice@example.com'],
    [2,'Bob','bob@example.com'],
];

Render::csv($rows, 'users.csv'); // Sets headers and streams CSV
```

## Verify
```bash
curl -i http://localhost:8000/users/1
curl -OJ http://localhost:8000/export/users   # -O -J honors filename header
```
