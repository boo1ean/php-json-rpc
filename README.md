What's inside
-------------

Server:

- JSON-RPC server - http://evilscott.github.io/junior/
- Active record implementation - http://www.phpactiverecord.org/
- Simple DI container - http://pimple.sensiolabs.org/
- Validation framework - http://documentup.com/Respect/Validation/
- Event emitter - https://github.com/igorw/evenement
- Testing + coverage - http://phpunit.de/
- Class loader - http://symfony.com/doc/current/components/class_loader
- Zend_Auth + Zend_Session - http://framework.zend.com/manual/1.12/en/zend.auth.html

Client:

- jquery
- underscore
- backbone
- bootstrap
- highlightjs

Server's code has 100% test coverage you can run tests using:

    test/coverage
    
(You need to install xdebug extension for code coverage)

About
-----

Project is a json-rpc api server for mobile apps for car business application where you can book/order car, view available cars/business, leave review, subscribe for push notification and so on.

Play
----

You can play with json rpc test client here [rpc.7-bit.co/client.html](http://rpc.7-bit.co/client.html).   

There are testing purpose methods at the left bottom which create db records omitting validatiom,   
so `you can create user then log in and then play with other methods`.

Setup
-----
1. Add virtual host to httpd config (if you are using apache otherwise you know what to do):

    ```ApacheConf
    <VirtualHost *:80>
        ServerName car-business
        DocumentRoot /path/to/public
        <Directory /path/to/public>
            DirectoryIndex index.html
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>
    ```

2. Run db/schema.sql to create db schema.
3. Install [composer](http://getcomposer.org/) and necessary dependencies (from project root dir):

    ```
    curl -sS https://getcomposer.org/installer | php
    composer.phar install
    ```

4. For client setup install [bower](http://bower.io/) and run (from project root dir)

    ```
    bower install
    ```

5. Run server and access client at `/client.html`

# License

The MIT License (MIT)
Copyright (c) 2014 Egor Gumenyuk <boo1ean0807@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
OR OTHER DEALINGS IN THE SOFTWARE.

