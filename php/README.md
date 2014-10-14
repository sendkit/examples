Sendkit PHP API Example
=======================

## Requirements

1. PHP 5.x (including curl library)
2. libcurl installation supporting http(s).

## Customizing and Configuration

In order to run the example you need to set your username and API-key in the
configuration. The username is the email-address you used when creating your
account and the API-key can be found or created in the [settings of Sendkit][settings].

In the file `sendkit_example_content.php` you can find the content of our tests.

Refer to `config.php` in order explore or change further configurations of the client.

## Run The Mail Example

The example creates a mail template, a corresponding
mail stream and sends a mail by pushing a few recipients into the stream.

Start the example via `php -f sendkit_example.php`.

[settings]: https://sendkit.com/app.html#mail_settings
