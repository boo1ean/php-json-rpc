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
