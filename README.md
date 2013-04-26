TODO
----

- DB schema + migrations
- API layer
- Data models
- Config + IoC + DI

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

2. Run schema.sql to create db schema.
3. Install composer and necessary dependencies (from project root dir):

```
curl -sS https://getcomposer.org/installer | php
composer.phar install
```
