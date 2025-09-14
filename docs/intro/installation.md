# Installation

You can install **Base** via Composer.

```bash
composer require stilmark/base
```

### Requirements

Ensure you have:

- PHP 8.2 or higher
- Composer installed and configured
- Extensions: `json`, `curl` (for OAuth functionality)

### Autoloading

Base follows PSR-4 autoloading. After installation, classes can be autoloaded using Composer’s autoloader:

```php
require __DIR__ . '/vendor/autoload.php';

use Stilmark\Base\Env;
```

### Verifying installation

Run the following to check if classes load correctly:

```php
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Stilmark\\Base\\Env'));"
```

Expected output:
```
bool(true)
```

### Quick verification test

Create a simple test file to verify Base is working:

```php
<?php
require 'vendor/autoload.php';

use Stilmark\Base\Env;

// Test environment loading
Env::set('TEST_VAR', 'Hello Base!');
echo Env::get('TEST_VAR') . PHP_EOL;

echo "Stilmark Base installed successfully!" . PHP_EOL;
```

Run with: `php test.php`
