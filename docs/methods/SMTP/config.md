# config(string $option, string $value)
This method is used to set the configuration options for the module.

```php
$SMTP->config('option', 'value');
```

## Available Options
- host: The host of the SMTP server.
- username: The username of the SMTP server.
- password: The password of the SMTP server.
- port: The port of the SMTP server.
- encryption: The encryption method of the SMTP server.
- ctencoding: The content transfer encoding method of the SMTP server.
- sbjillegal: The subject illegal characters of the SMTP server.