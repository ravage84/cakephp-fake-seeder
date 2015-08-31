# FakeSeeder

[![Travis-CI Build Status](https://travis-ci.org/ravage84/cakephp-fake-seeder.png)](https://travis-ci.org/ravage84/cakephp-fake-seeder)
[![Coverage Status](https://img.shields.io/coveralls/ravage84/cakephp-fake-seeder.svg)](https://coveralls.io/r/ravage84/cakephp-fake-seeder?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ravage84/cakephp-fake-seeder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ravage84/cakephp-fake-seeder/?branch=master)
[![Total Downloads](https://poser.pugx.org/ravage84/cakephp-fake-seeder/d/total.png)](https://packagist.org/packages/ravage84/cakephp-fake-seeder)
[![Latest Stable Version](https://poser.pugx.org/ravage84/cakephp-fake-seeder/v/stable.png)](https://packagist.org/packages/ravage84/cakephp-fake-seeder)

A CakePHP shell to seed your database with fake and/or fixed data.

Uses Faker to generate the fake data.
Uses shell tasks for implementing specific seeders.
Organizes logical groups of seeders in custom seeder shells/suites.

# Why should I use it?

You are wondering why you should use this plugin instead of using
[Faker](https://github.com/fzaninotto/Faker) directly or
creating fake data on your own?

This plugin...

- keeps you from doing the repetitive work (DRY)
- provides you three working modes (manual faking to fully automatic guessing) 
- helps you to stick to conventions, by following
  CakePHP's "Convention over Configuration" principle
- still provides you flexible configuration
- reduces documentation needs
- is properly covered by unit tests
- is easy to learn
- is extensible

## Installation

### Requirements

- PHP >= 5.3
- CakePHP 2.x (tested with 2.6.2, 2.7.3)

### Installation via composer

````
composer require ravage84/cakephp-fake-seeder
````

### Installation alternatives

Refer to the CakePHP CookBook section
[How To Install Plugins](http://book.cakephp.org/2.0/en/plugins/how-to-install-plugins.html).

## CakePHP Version Support

This plugin only supports CakePHP 2.x.

## Versioning

The releases of this plugin are versioned using [SemVer](http://semver.org/).

## Configuration

Set the configuration key ``FakeSeeder.seedable`` to true, by adding
``Configure::write('FakeSeeder.seedable', true);`` to your boostrap code.

## How to use

After installing and configuring the plugin, you can invoke the shell
by executing ``php Console/cake.php FakeSeeder.seeder -h``.

````
Welcome to CakePHP v2.6.2 Console
---------------------------------------------------------------
App : app
Path: D:\dev\xampp\htdocs\cate\app\
---------------------------------------------------------------
A shell to seed your database with fake and/or fixed data.

Uses Faker to generate the fake data.
Uses shell tasks for implementing specific seeders.
Organizes logical groups of seeders in custom seeder shells/suites.

Usage:
cake fake_seeder.seeder [options] [<model>]

Options:

--help, -h         Display this help.
--verbose, -v      Enable verbose output.
--quiet, -q        Enable quiet output.
--mode, -m         The seeding mode.
                   'manual' = No Field formatters are guessed.
                   'auto' = All field formatters are guessed.
                   'mixed' = Only missing field formatters are guessed.
                   (choices: manual|auto|mixed)
--locale, -l       The locale to use for Faker.
--records, -r      The amount of records to seed.
--validate         Whether or not to validate when saving the seeding
                   data. (choices: first|1|)
--seed, -s         Set the seed number for Faker to use.
--no-truncate      Prevents that the model gets truncated before
                   seeding.

Arguments:

model  The name of a seeder shell task without 'SeederTask' suffix.
       For example 'Article' for 'ArticleSeederTask'.
       Alternatively the name of a model.
       It will try to guess the field formatters then.
       (optional)

All shell options can be set through:
1. CLI parameter, e.g. "--records"
2. The seeder specific configuration, e.g. "FakeSeeder.Article.records"
3. The general seeder configuration, e.g "FakeSeeder.records"
4. The seeder shell task class properties, e.g. "$_records"
The values are checked in that order. The first value found is taken.
If no value is set, it will fall back to an optional default value.

When no seeders are set (e.g. in a custom seeder suite) and if called
without arguments, it will prompt to execute one of the seeder shell
tasks available.

````

- See [ExampleSeederShell.php](Docs/Examples/ExampleSeederShell.php) for an example how to create
a **custom seeder shell**.
- See [ExampleSeederTask.php](Docs/Examples/ExampleSeederTask.php) for an example how to create
a **custom seeder task**.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)

## Changelog

See [CHANGELOG.md](CHANGELOG.md)

## TODOs

- Improve Documentation
- Simplify integration of 3rd party data provider
- Implement seeder shell task baking
- Check possibility to use code for TestFixtures, like [gourmet/faker](https://github.com/gourmet/faker)

## License

This plugin is licensed under the [MIT License](LICENSE).

## Attribution

This project was inspired by the works of others, such as:

- The book ["Build APIs You Won't Hate"](https://leanpub.com/build-apis-you-wont-hate) by [Phil Sturgeon](https://github.com/philsturgeon)
- The [Faker plugin for CakePHP 3.x](https://github.com/gourmet/faker) by [Jad Bitar](https://github.com/jadb)
- [Faker](https://github.com/fzaninotto/Faker) by [Francois Zaninotto](https://github.com/fzaninotto)
- [CakePHP Faker Fixtures Plugin](https://github.com/burriko/cakephp-faker-fixtures/)
- [CakePHP Seed Shell Plugin](https://github.com/jlis/Cake-Seed-Shell)
