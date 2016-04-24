googl-php
=========

Introduction
------------
googl-php is a small and simple PHP class intended to use with the Google URL shortening service "Goo.gl" and licensed under the GNU GPL v3.

Functions
---------
The class currently supports 2 methods:
  * *Shorten* a URL
  * *Expand* (look up) an already shortened URL

Usage
-----
```php
<?php 

require_once('Googl.class.php');

$googl = new Googl('YOUR_API_KEY');

// Shorten URL
$googl->shorten('http://www.google.com/');

// Look up long URL
$googl->expand('http://goo.gl/fbsS');

unset($googl);
```

API key
-------
You will need an API key to use the the shortening service.

Learn more at https://developers.google.com/url-shortener/v1/getting_started#auth.

OAuth is not supported by this library. Please use the authentication via application key.

Further info
------------
For further information about Goo.gl and its API, please visit: https://developers.google.com/url-shortener/.
