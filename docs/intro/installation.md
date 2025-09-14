# Installation

You can install **Base** via Composer.

```bash
composer require stilmark/base
```

### Requirements

Ensure you have:

- PHP 8.1+
- Composer installed and configured

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
