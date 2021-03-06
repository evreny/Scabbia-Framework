README
======

[Scabbia Framework](http://www.scabbiafw.com/) is an open source PHP framework project under GPL license. It had been under development by [Eser Ozvataf](http://eser.ozvataf.com/) for 2 years and reached version 1.5 on stable branch. It's active development is frozen but small bugfixes will be available in time.

Scabbia 2 is the new branch of Scabbia Framework which has multiple contributors and currently on planning stage. It will also have a different software license and architecture starting from scratch. Keep visiting project homepage and repositories for further updates.

All branches of Scabbia follows the accepted standards of PHP Framework Interop Group (http://www.php-fig.org/).



VERSION 1.5
===========

Installation
------------
##### Alternative 1: Zip Package #####

Download [Skeleton Application](https://github.com/larukedi/Scabbia-Skeleton/archive/master.zip) and launch `php composer_update.php`.

##### Alternative 2: Composer #####

On *nix:
``` bash
php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
php composer.phar create-project larukedi/scabbia-skeleton -s dev
```

On Windows:
Download and install [Composer-Setup.exe](http://getcomposer.org/Composer-Setup.exe) then run:
``` bat
composer create-project larukedi/scabbia-skeleton -s dev
```



Requirements
------------
* PHP 5.3.7+ (http://www.php.net/)
* Composer Dependency Manager** (http://getcomposer.org/)

** Skeleton application auto-installs this requirement with other dependencies.


Documentation
-------------
Documentation can be found under different repository at (https://github.com/larukedi/Scabbia-Docs/).

Languages:

* [Turkish](https://github.com/larukedi/Scabbia-Docs/blob/master/tr/index.md)


Dependencies
------------
* psr/log: PSR-3 Logger Interface (http://www.php-fig.org/)
* facebook/php-sdk: Facebook PHP SDK
* dflydev/markdown: Markdown Parser
* mustache/mustache: Mustache Parser
* trekksoft/potomoco: gettext compiler
* leafo/lessphp: LESS compiler


Bundled Components
------------------
* fonts/KabobExtrabold.ttf
* twitter/bootstrap
* twitter/hogan
* twitter/typeahead
* introjs
* jquery
* cleditor
* flot
* jquery.maskedinput
* jquery.tablesorter
* jquery.validation
* laroux.js
* mapbox
* normalize.css
* reset.css


Optional PHP Extensions
-----------------------
* curl: http communication
* gd: image manipulation
* gettext: translation
* intl: i18n features
* mbstring: multibyte string manipulation
* memcache: caching features
* mongo: mongodb support
* soap: soap protocol
* sockets: socket communication
* tokenizer: lexical analysis


License
-------
See [license.txt](license.txt)


Contributing
------------
* Fork the repo, push your changes to your fork, and submit a pull request.
* If something does not work, please report it using GitHub issues.