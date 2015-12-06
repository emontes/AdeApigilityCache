# AdeApigilityCache
=======================

This is a module for caching APIs generated with Apigility.

Indroduction
------------
I tried to install and configure Http Cache ZF  in my Apigility application but really could not.

So I decided to implement this code that I have found is quite easy to implement.

So far the only problem I found with this method of caching is when I have active Z-Ray on my development server.

Instalation
------------
My Api is called "Hotels". Then you have to replace the /module/Your_Module/module.php file (In my case /module/Hoteles/module.php).
Then you have to change the namespace for which corresponds to your Api.
Finally within the onBootStrap() in the array $routes are all methods that are available to for caching.
