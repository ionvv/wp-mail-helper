# WP Mail Helper

WP Mail Helper is a package to help WordPress developers to send custom emails.

## Getting Started

### Minimum requirements and dependencies

WP Mail Helper requires:

* PHP 7+
* WordPress - latest
* Composer to be installed

### Installation

Install via composer

```
composer require ionvv/wp-mail-helper
```

## Usage

### Basic usage
```
$email_controller = new \WpMailHelper\Email();
$email_controller->sendEmail(
    'recipient@domain.tld', // $to
    'sender@domain.tld',    // $from
    'Email subject',        // $subject
    'Email content'         // $message
);
```

### Advanced usage
```
$email_controller->sendEmail(
    'recipient@domain.tld', // $to
    'sender@domain.tld',    // $from
    'Email subject',        // $subject
    'Email content',        // $message
    'template',             // $message_type
    [],                     // $headers
    'john.doe@domain.tld',  // $cc
    'john.doe@domain.tld',  // $bcc
    [WP_CONTENT_DIR . '/uploads/dummy-file.zip']
);
```

### Use a post as email content
```
$email_controller = new \WpMailHelper\Email();
$email_controller->sendEmail(
    'recipient@domain.tld', // $to
    'sender@domain.tld',    // $from
    'Email subject',        // $subject
    '$post->ID',            // $message
    'post'                  // $message_type
);
```

### Use a custom template as email content
```
$email_controller = new \WpMailHelper\Email();
$email_controller->sendEmail(
    'recipient@domain.tld',                               // $to
    'sender@domain.tld',                                  // $from
    'Email subject',                                      // $subject
    get_stylesheet_directory() . '/emails/template.html', // $message
    'template'                                            // $message_type
);
```

### Use custom variables in email content
Sometimes you'll have to replace some variables programatically in your emails.
You can use the 'shortcodes' in the email subject and content. Check the following example to see how you can use them:
```
$email_subject = 'Welcome to our website, {{FIRST_NAME}}';
$email_content = 'Dear {{FIRST_NAME}}, thanks for joining our website. You have been registered with the following email address: {{EMAIL_ADDRESS}}';

$email_controller = new \WpMailHelper\Email();
$email_controller->setVars(
    [
        'FIRST_NAME' => 'Ion',
        'EMAIL_ADDRESS' => 'ion@domain.tld'
    ],
    [
        'FIRST_NAME' => 'Ion'
    ]
);
$email_controller->sendEmail(
    'ion@domain.tld',       // $to
    'sender@domain.tld',    // $from
    $email_subject,         // $subject
    $email_content          // $message
);
```
User will receive an email with the following subject:
```
Welcome to our website, Ion
```
and the content:
```
Dear Ion, thanks for joining our website. You have been registered with the following email address: ion@domain.tld
```

---

## Changelog

### 1.0.0
* Initial release

---

## License
WP Mail Helper code is licensed under MIT license.
