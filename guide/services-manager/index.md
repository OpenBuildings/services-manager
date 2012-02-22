Services Manager
================

This modules purpose is to ease out the management of various external services that your application may depend upon. Each service is governed by a single easily reachable class that does all the required loading of PHP or Javascript, 

Setting up:
-----------

Presently there are 2 types of services:

* Javascript Services - they require javascript files to load. To be able to use those you must place those helpers in appropriate places
~~~

<html>
<head>
	<title>Site</title>
	...
	<!-- This needs to be inside your head tag -->
	<?php echo Service::all_heads(); ?>
</head>
<body>
	...
	<!-- This needs to be at the very end of the body, just before the closing body tag -->
	<?php echo Service::all_bodies(); ?>
</body>
</html>
~~~
* PHP Services - they handle their own affairs.

Each service has its own configuration, you can see the default configuration in the ``services-manager.php`` file

Usage:
------

Some services have helper methods to be used throughout your code. The general principle is that those methods will fail silently if the service is disabled for some reason or another. 

For example this will render an addthis toolbox with sharing options for the current url, but if the service is disabled, it will return an empty string, thus your site should not be affected by disabling of the services and continue operation:

~~~
<?php echo Service::factory('addthis')->toolbox() ?>
~~~

In order to insure that for your service specific code will not execute if the service is disabled, you can use the initialized method:

~~~
<?php if (Service::factory('addthis')->initialized()): ?>
	<!-- Your custom addthis code goes here -->
<?php endif; ?>
~~~

You can also disable each service based on a role from jelly_auth. Just use the 'disabled-for-role' => '{somerole}' config parameter.

There are some builtin services that are available:


Addthis
-------

__Configuraitons__:

* __enabled__ : (bool)
* __api-key__ : (string) your api key for addthis
* __load-user-email__ : (bool) if you set this to TRUE will load the current user email and you can use :user-email to access it in the addthis-config
* __addthis-config__ : (array) this will be use to set the javascript variable addthis_config, used by addthis.

__Helpers__:

* __toolbox($url = NULL, $attributes = NULL)__ : Generate a div addthis toolbox, url defaults to inital requests' url


Exceptionalio
-------------

__Configuraitons__:

* __enabled__ : (bool)
* __api-key__ : (string) your api key for exceptionalio
* __use-auth__ : (bool) Get the current user id and email and set it as custom parameters for the exception

__Helpers__:

* __log(Exception $exception)__ : Send the exception to exceptionalio


Googleanalytics
---------------

__Configuraitons__:

* __enabled__ : (bool)
* __api-key__ : (string) your api key for google analytics
* __header__ : (bool) Set it to FALSE to place the GA code at the bottom of the page


Kissinsights
------------

__Configuraitons__:

* __enabled__ : (bool)
* __api-file__ : (string) the file for kissinsights - they don't have api keys yet, but the filename is unique


Kissmetrics
-----------

__Configuraitons__:

* __enabled__ : (bool)
* __api-key__ : (string) your api key for kissmetrics
* __use-auth__ : (bool) Idintify the user with the email in the php api, using the currently logged user
* __php-api__ : (bool) Enabled the php-api. If its set to FALSE all the php methods will silently fail and the KM class will not be loaded at all.
* __more__ : (string) Custom javascript to be placed after kissmetrics has been included, placed just after the script tags

__Helpers__:

* __record($event)__ : Record an event with the PHP API
* __identify($identification)__ : Identify the user with the PHP API
* __set($properties)__ : Set properties to the current user with the PHP API
* __queue($event, $event2 ...)__ : Add the events to the Javascript API queue. If this is a normal page rendering it will place them just after the kissmetrics javascript include code, but if its ajax request will render a script tag directly with the events specified
* __is_async()__ : Find out if its an asynchronous request (ajax)

Example using the queue helper method. This will either add those to the queue and render it in the header, or render them directly here with a script tag:
~~~
<?php echo Service::factory('kissmetrics')->queue(
  array('trackClick', '.add-to-favourites', 'clicked on add to favourites in company profile'),
  array('trackClick', '.remove-from-favourites', 'clicked on remove from favourites in company profile')
) ?>
~~~


Mailchimp
---------

__Configuraitons__:

* __enabled__ : (bool)
* __api-key__ : (string) your api key for mailchimp
* __lists__ : (array) Key value pairs for list alias => list id. If those are set you will be able to use list aliases instead of actual list ids, so for example listSubscribe('newsletter') will result in listSubscribe('<newsletter id>')

__Helpers__:

This service uses Mailchimp api Version 1.3 and all methods are proxied to the API itself. so you can call any of the API methods directly with the service object. All methods starting with list, will try to use the ids specified in the configuration.
Example:

~~~
Service::factory('mailchimp')->listSubscribe('newsletter', 'me@example.com');
~~~

