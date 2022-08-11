# Acquia Site Studio Content Hub

Acquia Site Studio Content Hub provides integration between [Acquia Site Studio](https://www.acquia.com/products/drupal-cloud/site-studio) and [Acquia Content Hub](https://www.acquia.com/products/drupal-cloud/content-hub) in the form of two modules, a subscriber and a publisher. These two modules provide an Acquia approved and tested route for using the two products in tandem.

The core Acquia Site Studio module can be found at https://github.com/acquia/cohesion - for full details on product setup and installation please refer to the [documentation](https://sitestudiodocs.acquia.com/).

## Installation with composer

Using composer is the preferred way of managing your modules and themes as composer handles dependencies automatically and there is less margin for error. You can find out more about composer and how to install it here: https://getcomposer.org/. It is not recommended to edit your composer.json file manually.

Open up your terminal and navigate to your project root directory.

Run the following commands to require the module:

```
composer require acquia/sitestudio_contenthub
```

Site Studio ContentHub will install along with several module dependencies from drupal.org.

You can now enable the modules via drush with the following commands:

```
drush cr
drush pm-enable sitestudio_contenthub_publisher -y
drush pm-enable sitestudio_contenthub_subscriber -y
```

## Using Acquia Site Studio Content Hub module

Acquia Site Studio Content Hub module contains two submodules - "Site Studio Content Hub Publisher" and "Site Studio Content Hub Subscriber".
The main module is used as a "wrapper" and is intentionally hidden from module list in Drupal UI. Enabling it will add no additional benefit or functionality.
Instead, one or both submodules should be enabled, depending on desired functionality of the site - Content Publishing, Subscribing or both.

If the site is to be in a role of "Publisher", enable "Site Studio Content Hub Publisher" via Drupal UI or via Drush:
```
drush pm-enable sitestudio_contenthub_publisher -y
```
If the site is to be in a role of "Subscriber", enable "Site Studio Content Hub Publisher" via Drupal UI or via Drush:
```
drush pm-enable sitestudio_contenthub_subscriber -y
```

## Documentation
Further documentation for Site Studio Content Hub is available [here](https://sitestudiodocs.acquia.com/6.7/user-guide/using-acquia-cohesion-acquia-content-hub).

## License

Copyright (C) 2021 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
