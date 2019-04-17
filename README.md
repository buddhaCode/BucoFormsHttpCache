# BucuFormsHttpCache
Shopware plugin to enable HTTP caching for forms

## Features
This plugin enables HTTP caching, warming and invalidation for forms. Works like caching for shop pages. Standard cache time is 4 days (14400 seconds).

## Technical information
Currently, this works only for new Shopware installations. With the Shopware 5.5 release, the internal URL for forms SEO URLs changed from `sViewport=ticket&sFid=<ID>`to `sViewport=forms&sFid=<ID>`. This properly broke the SEO URL generation in your installation. So, the cache warming isn't working, because it relies on the SEO URL generation. **Installing this plugin will migrate the forms SEO URLs to the new schema.** Hard links in the categorie's "External" field and the shop page's "Link" field will be migrated, too. Please check for other hard links to the internal form URL!!!

## Compatibility
* Shopware >= 5.5.0
* PHP >= 7.0

## Installation

### Git Version
* Checkout plugin in `/custom/plugins/BucuFormsHttpCache`
* Install the plugin with the Plugin Manager

### Install with composer
* Change to your root installation of Shopware
* Run command `composer require buddhaCode/BucuFormsHttpCache` and install and active plugin with Plugin Manager 

## Contributing
Feel free to fork and send pull requests!

## Licence
This project uses the [GPLv3 License](LICENCE).
