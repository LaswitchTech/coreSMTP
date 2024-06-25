# Usage
## Initiate Encryption
To use `Encryption`, simply include the Encryption.php file and create a new instance of the `Encryption` class.

```php
<?php

// Import additionnal class into the global namespace
use LaswitchTech\coreEncryption\Encryption;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate Encryption
$Encryption = new Encryption();
```

### Properties
`Encryption` provides the following properties:

- [Configurator](https://github.com/LaswitchTech/coreConfigurator)
- [Logger](https://github.com/LaswitchTech/coreEncryption)

### Methods
`Encryption` provides the following methods:

- [config()](methods/Encryption/config.md)
- [decrypt()](methods/Encryption/decrypt.md)
- [encrypt()](methods/Encryption/encrypt.md)
- [isInstalled()](methods/Encryption/isInstalled.md)
- [setCipher()](methods/Encryption/setCipher.md)
- [setPrivateKey()](methods/Encryption/setPrivateKey.md)
- [setPublicKey()](methods/Encryption/setPublicKey.md)

## Initiate Command for coreCLI integration
To use `Command`, simply create `Command/EncryptionCommand.php` file and extend a new instance of the `Command` class.

```php

// Import Logger class into the global namespace
// These must be at the top of your script, not inside a function
use LaswitchTech\coreEncryption\Command;

// Initiate the Command class
class EncryptionCommand extends Command {}
```

### Methods
`Command` provides the following methods:

- [encryptAction()](methods/Command/encryptAction.md)
- [decryptAction()](methods/Command/decryptAction.md)

## Initiate Controller for coreAPI and/or coreRouter integration
To use `Controller`, simply create `Controller/EncryptionController.php` file and extend a new instance of the `Controller` class.

```php

// Import Logger class into the global namespace
// These must be at the top of your script, not inside a function
use LaswitchTech\coreEncryption\Controller;

// Initiate the Controller class
class EncryptionController extends Controller {}
```

### Methods
`Controller` provides the following methods:

- [encryptAction()](methods/Controller/encryptAction.md)
- [decryptAction()](methods/Controller/decryptAction.md)
