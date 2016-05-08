# Laravel Easy CRUD Generator
[![Latest Stable Version](https://poser.pugx.org/ahmedash95/ecrud/v/stable)](https://packagist.org/packages/ahmedash95/ecrud) [![Total Downloads](https://poser.pugx.org/ahmedash95/ecrud/downloads)](https://packagist.org/packages/ahmedash95/ecrud) [![Latest Unstable Version](https://poser.pugx.org/ahmedash95/ecrud/v/unstable)](https://packagist.org/packages/ahmedash95/ecrud) [![License](https://poser.pugx.org/ahmedash95/ecrud/license)](https://packagist.org/packages/ahmedash95/ecrud)

sometimes as a backend developer you waste alot of time writing html inputs for small tables or mayby large .. so this package will create ( index,create,update ) views files for you with a few commands.

at first you have to know this package make crud files from your **Migration**

## Installation
Begin by installing the package through Composer. Run the following command in your terminal:

```
$ composer require ahmedash95/ecrud
```

Once done, add the following line in your providers array of ``` config/app.php ```:

```
Ahmedash95\Ecrud\EcrudServiceProvider::class,
```

This package has a single configuration file :
```
$ php artisan vendor:publish --provider="Ahmedash95\Ecrud\EcrudServiceProvider"
```

## Usage

create a crud files from migration
```
$ php artisan ecrud:migration 2016_04_17_144447_create_categories_table
```

if the files already exists the packge won't override them until you force override option
```
$ php artisan ecrud:migration 2016_04_17_144447_create_categories_table --force
```

this package try to guess what fileds you are need in your views for example it's remove the id and timestamps from fileds while loading them from migration file so if you need to except some fileds or generate crud for specific fileds you can use two options ``` only | except ```

Only
```
$ php artisan ecrud:migration 2016_04_17_144447_create_categories_table --only=name,description 
```
Except
```
$ php artisan ecrud:migration 2016_04_17_144447_create_categories_table --except=user_id 
```



the default path of any generated ecrud is the views path ``` resources/views ``` so if you publish the ``` create_categories_table ``` the ecrud path will be ``` resources/views/categories ``` if you want to change the path to be somthing like ``` resources/views/panel/categories ``` you have to use the option **``` path ```**

```
$ php artisan ecrud:migration 2016_04_17_144447_create_categories_table --force --path=panel/categories
```

## Contribution

* Fork it
* Create your feature branch (git checkout -b my-new-feature)
* Commit your changes (git commit -am 'Add some feature')
* Push to the branch (git push origin my-new-feature)
* Create new Pull Request




