# addTemplate($templateName, $templatePath = null)
This method is used to add a template to the SMTP module. The template can be used to send emails. The template can be added by providing the template name and the path to the template file. If the template path is not provided, the template name will be used as the template path.

```php
$SMTP->addTemplate('default');
```