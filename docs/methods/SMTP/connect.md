# connect($username, $password, $host, $port = 465, $encryption = 'ssl')
This method is used to connect to the SMTP server. The method requires the username, password, host, and port of the SMTP server. The method also accepts an optional encryption parameter which defaults to 'ssl'.

```php
$SMTP->connect('mail@domain.com', '123password', '127.0.0.1');
```