# config(string $option, string $value)
This method is used to set the configuration options for the module.

```php
$Encryption->config('option', 'value');
```

## Available Options
- cipher: The cipher to use for encryption. Default is `AES-256-CBC`.
- key: The private key to use for encryption. Default is `null`.
