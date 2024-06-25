# login($username, $password, $host, $port = 465, $encryption = 'ssl')
This method is used to attempt to login to the SMTP server. The method requires the username, password, host, and port of the SMTP server. The method also accepts an optional encryption parameter which defaults to 'ssl'. If a connection is established, it will return `true`, otherwise it will return `false`. The connection will not be maintained after the method is called.

```php
$SMTP->login('mail@domain.com', '123password', '127.0.0.1');
```