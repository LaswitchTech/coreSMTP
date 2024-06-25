# send($options = [])
This method is used to send an email. The method accepts an array of options. The method will return `true` if the email is sent successfully, otherwise it will return `false`.

## Options
The method accepts the following options:
- `from` (string) - The email address of the sender.
- `to` (string) - The email address of the recipient.
- `reply-to` (string) - The email address to reply to.
- `cc` (string) - The email address of the carbon copy recipient.
- `bcc` (string) - The email address of the blind carbon copy recipient.
- `subject` (string) - The subject of the email.
- `body` (string) - The body of the email.
- `attachments` (array) - An array of attachments. Each attachment should simply be a path to the attachment file.
  - `path` (string) - The path to the attachment file.

```php
$SMTP->send([
    'from' => 'mail@domain.com',
    'to' => 'mail@domain.com',
    'subject' => 'Test Email',
    'body' => 'This is a test email.'
]);
```