# Usage
## Initiate SMTP
To use `SMTP`, simply include the SMTP.php file and create a new instance of the `SMTP` class.

```php
<?php

// Import additionnal class into the global namespace
use LaswitchTech\coreSMTP\SMTP;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate SMTP
$SMTP = new SMTP();
```

### Properties
`SMTP` provides the following properties:

- [Configurator](https://github.com/LaswitchTech/coreConfigurator)
- [Logger](https://github.com/LaswitchTech/coreSMTP)

### Methods
`SMTP` provides the following methods:

- [addTemplate()](methods/SMTP/addTemplate.md)
- [close()](methods/SMTP/close.md)
- [config()](methods/SMTP/config.md)
- [connect()](methods/SMTP/connect.md)
- [isInstalled()](methods/SMTP/isInstalled.md)
- [login()](methods/SMTP/login.md)
- [send()](methods/SMTP/send.md)
- [setTemplate()](methods/SMTP/setTemplate.md)

## Initiate Command for coreCLI integration
To use `Command`, simply create `Command/SmtpCommand.php` file and extend a new instance of the `Command` class.

```php

// Import Logger class into the global namespace
// These must be at the top of your script, not inside a function
use LaswitchTech\coreSMTP\Command;

// Initiate the Command class
class SmtpCommand extends Command {}
```

### Methods
`Command` provides the following methods:

- [method()](methods/Command/method.md)

## Initiate Controller for coreAPI and/or coreRouter integration
To use `Controller`, simply create `Controller/SmtpController.php` file and extend a new instance of the `Controller` class.

```php

// Import Logger class into the global namespace
// These must be at the top of your script, not inside a function
use LaswitchTech\coreSMTP\Controller;

// Initiate the Controller class
class SmtpController extends Controller {}
```

### Methods
`Controller` provides the following methods:

- [method()](methods/Controller/method.md)
