littleship
==========

A symfony app to manage your docker containers.

## Nginx config

If your are using docker `maxexcloo/nginx-php` image, add this `nginx-symfony.conf`
file to your `/data/config` folder :

```nginx
rewrite ^/app\.php/?(.*)$ /$1 permanent;

location / {
    index app.php;
    try_files $uri @rewriteapp;
}

location @rewriteapp {
    rewrite ^(.*)$ /app.php/$1 last;
}

# pass the PHP scripts to FastCGI server from upstream phpfcgi
location ~ ^/(app|app_dev|config)\.php(/|$) {
    fastcgi_pass unix:/run/php5-fpm.sock;
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    include fastcgi_params;
    fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param  HTTPS off;
}
```
