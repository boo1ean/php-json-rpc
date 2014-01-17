Setup
-----
1. Add virtual host to httpd config:

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
