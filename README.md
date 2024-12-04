# Admin Debug Tools

Admin Debug Tools makes it easy to manage your site's logs and debug settings directly from the WordPress dashboard, without needing to edit backend files. View, filter and manage debug logs, toggle debug mode, and control debug-related constants all from one central location.

## Features

- View formatted debug log files
- Monitor latest log entries in near real-time
- Filter logs by type (Errors, Warnings, Notices, Deprecations)
- Search logs with regex support
- Save and clear log files
- Download log files
- Toggle debug mode with one click
- Edit WP_DEBUG and related constants

### Prerequisites

- PHP 8.0+
- WordPress 6.0+
- NPM
- Composer

### Local Setup

1. Clone the repository;
2. Run `composer install` to install PHP dependencies;
3. Run `npm run install` to install JS dependencies;
4. Run `npm run start` to start the development build process and watch for changes;
5. Activate the plugin through WordPress admin panel;

### Code Standards and Style

Run PHPCS to check code standards and style:

```
./vendor/bin/phpcs --standard=phpcs.xml.dist includes
```

### Running Tests

The project uses PHPUnit for unit testing. Here are some common testing commands:

```
./vendor/bin/phpunit
./vendor/bin/phpunit tests/FileContentGetterTest.php
./vendor/bin/phpunit tests/FileContentGetterTest.php --filter testGetContent
```

