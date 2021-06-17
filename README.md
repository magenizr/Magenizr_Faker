# Faker

Create customer accounts for your developer and testing team, including a password, billing and shipping address. Faker will provide you the login credentials right away, so that your team can start using the customer accounts.

Waste no more time with creating test accounts over and over again, especially on multi-store environments.

![Magenizr Faker - Cli](https://images2.imgbox.com/e2/92/bSLXmsPj_o.gif)
![Magenizr Faker - Frontend/Backend](https://images2.imgbox.com/06/9b/xh5nhofq_o.gif)

## Features

* Create customers including shipping and billing address based on provided CSV file
* Delete customers ( including test orders ) once you are done with testing
* Use a custom CSV file with your login credentials, shipping details for your team. Copy the file from `Data/customers.csv` and use it as a template.

## Usage

Simply go to `Stores > Configuration > Advanced > Developer > Faker` and enable the module. Enter the path of your CSV file ( relatively from your document root ) or leave it blank to use the sample CSV file that comes with the module.

The following options are available.

- `--action create|delete` ( Default: create )
- `--limit` ( Default: 5 )
- `--columns first_name,last_name,email etc.` ( Default: first_name,last_name,email )
- `--filter email=%hotmail.com`

For example:

```
bash-4.4# php bin/magento faker:account:create --limit 15 --columns "first_name,last_name,address>street,address>country_id,address>city,address>region,address>region_id,address>postcode,address>telephone,email,optional>password,website_id,group_id,store_id"
+---------+------------+------------+-----------------------+--------------------+-----------------+----------------+-------------------+------------------+-------------------+----------------------------------+-------------------+------------+----------+----------+
| Action  | first_name | last_name  | address>street        | address>country_id | address>city    | address>region | address>region_id | address>postcode | address>telephone | email                            | optional>password | website_id | group_id | store_id |
+---------+------------+------------+-----------------------+--------------------+-----------------+----------------+-------------------+------------------+-------------------+----------------------------------+-------------------+------------+----------+----------+
| Updated | Rebbecca   | Didio      | 171 E 24th St         | AU                 | Leith           |                | TAS               | 7315             | 0458-665-290      | rebbecca.didio@didio.com.au      | 4WsJ2FZqUr        | 1          | 1        | 1        |
| Updated | Stevie     | Hallo      | 22222 Acoma St        | AU                 | Proston         |                | QLD               | 4613             | 0497-622-620      | stevie.hallo@hotmail.com         | 2FKjDO7xKp        | 1          | 1        | 1        |
| Updated | Mariko     | Stayer     | 534 Schoenborn St #51 | AU                 | Hamel           |                | WA                | 6215             | 0427-885-282      | mariko_stayer@hotmail.com        | aFa5cbJGXw        | 1          | 1        | 1        |
| Updated | Gerardo    | Woodka     | 69206 Jackson Ave     | AU                 | Talmalmo        |                | NSW               | 2640             | 0443-795-912      | gerardo_woodka@hotmail.com       | tJNnMv8qfn        | 1          | 1        | 1        |
| Updated | Mayra      | Bena       | 808 Glen Cove Ave     | AU                 | Lane Cove       |                | NSW               | 1595             | 0453-666-885      | mayra.bena@gmail.com             | EhWeZligdW        | 1          | 1        | 1        |
...
| Created | Amira      | Chudej     | 3684 N Wacker Dr      | AU                 | Rockside        |                | QLD               | 4343             | 0478-867-289      | amira.chudej@chudej.net.au       | f9CmgSDtok        | 1          | 1        | 1        |
| Created | Marica     | Tarbor     | 68828 S 32nd St #6    | AU                 | Rosegarland     |                | TAS               | 7140             | 0494-982-617      | marica.tarbor@hotmail.com        | yEuRwixDX2        | 1          | 1        | 1        |
| Created | Shawna     | Albrough   | 43157 Cypress St      | AU                 | Ringwood        |                | QLD               | 4343             | 0441-255-802      | shawna.albrough@albrough.com.au  | NhNjKYpGsq        | 1          | 1        | 1        |
| Created | Paulina    | Maker      | 6 S Hanover Ave       | AU                 | Maylands        |                | WA                | 6931             | 0420-123-282      | paulina_maker@maker.net.au       | DrSNms5jjK        | 1          | 1        | 1        |
| Created | Rose       | Jebb       | 27916 Tarrytown Rd    | AU                 | Wooloowin       |                | QLD               | 4030             | 0496-441-929      | rose@jebb.net.au                 | uAZYMDP3uX        | 1          | 1        | 1        |
+---------+------------+------------+-----------------------+--------------------+-----------------+----------------+---------- Result: 15 ----------------+-------------------+----------------------------------+-------------------+------------+----------+----------+
bash-4.4# 
```

```
bash-4.4# php bin/magento faker:account:create --filter "address>country_id=CA" --columns "email,optional>password,address>street,address>country_id,address>city,address>region,address>region_id,address>postcode"
+---------+-------------------------+--------------------+-------------------+----------------+-------------------+------------------+-------------------------------------------+-------------------+
| Action  | address>street          | address>country_id | address>city      | address>region | address>region_id | address>postcode | email                                     | optional>password |
+---------+-------------------------+--------------------+-------------------+----------------+-------------------+------------------+-------------------------------------------+-------------------+
| Created | 2335 Canton Hwy #6      | CA                 | Windsor           |                | ON                | N8N 3N2          | francoise.rautenstrauch@rautenstrauch.com | ZmPrR1T6J3        |
| Created | 6 Arch St #9757         | CA                 | Alcida            |                | NB                | E8J 2C4          | kloud@gmail.com                           | VqJ3VK90Pq        |
| Created | 9547 Belmont Rd #21     | CA                 | Belleville        |                | ON                | K8P 1B3          | lourdes_bauswell@aol.com                  | meTgNQjg85        |
| Created | 73 Pittsford Victor Rd  | CA                 | Vancouver         |                | BC                | V5Z 3K2          | hannah@yahoo.com                          | 5QkOdSf5Nk        |
| Created | 447 Commercial St Se    | CA                 | LIle-Perrot       |                | QC                | J7V 4T4          | tom.loeza@gmail.com                       | oUSaPAKYFh        |
...
| Created | 1534 Sidco Dr           | CA                 | Leduc             |                | AB                | T9E 5A6          | wlablanc@yahoo.com                        | VGJE3GmwsL        |
| Created | 4207 Leon Rd            | CA                 | North Vancouver   |                | BC                | V7L 3X6          | lauryn@yahoo.com                          | yIlZevwQiR        |
| Created | 7564 N Academy Ave      | CA                 | London            |                | ON                | N6A 1S5          | laurena.begin@begin.com                   | L9gsyVs9Z3        |
| Created | 88 E Saint Elmo Rd      | CA                 | Hamilton          |                | ON                | L8R 3J3          | elise@gmail.com                           | JgvfzVwfNw        |
| Created | 75 Westchester Ave      | CA                 | Pierrefonds       |                | QC                | H9J 1W6          | corrie_kardell@aol.com                    | GNNsrR4o6n        |
+---------+-------------------------+--------------------+-------------------+--------------- Result: 29 ---------+------------------+-------------------------------------------+-------------------+
bash-4.4# 
```

## System Requirements

- Magento 2.3.x, 2.4.x
- PHP 5.6.x, 7.x

## Installation (Composer)

1. Update your composer.json `composer require "magenizr/magento2-faker":"1.0.0" --no-update`
2. Install dependencies and update your composer.lock `composer update --lock`

```
./composer.json has been updated
Loading composer repositories with package information
Updating dependencies (including require-dev)              
Package operations: 1 install, 0 updates, 0 removals
  - Installing magenizr/magento2-faker (1.0.0): Downloading (100%)         
Writing lock file
Generating autoload files
```

3. Enable the module and clear static content.

```
php bin/magento module:enable Magenizr_Faker --clear-static-content
php bin/magento setup:upgrade
```

## Installation (Manually)

1. Download the code.
2. Extract the downloaded tar.gz file. Example: `tar -xzf Magenizr_Faker_1.0.0.tar.gz`.
3. Copy the code into `./app/code/Magenizr/Faker/`.
4. Enable the module and clear static content.

```
php bin/magento module:enable Magenizr_Faker --clear-static-content
php bin/magento setup:upgrade
```

## Support

If you experience any issues, don't hesitate to open an issue
on [Github](https://github.com/magenizr/Magenizr_Faker/issues) or email us at [modules@magenizr.com](mailto:modules@magenizr.com).

## Purchase

This module is available for free on [GitHub](https://github.com/magenizr). Feel free to support us on [Patreon](https://patreon.com/magenizr). Reach us out at [modules@magenizr.com](mailto:modules@magenizr.com) if you need a database with up to 1 million fake customer accounts and addresses for test purposes.

## Contact

Follow us on [GitHub](https://github.com/magenizr), [Twitter](https://twitter.com/magenizr) and [Facebook](https://www.facebook.com/magenizr).

## History

===== 1.0.0 =====

* First release

## Roadmap

- Set custom CSV file via CLI ( e.g `--file path/to/file.csv` )
- Signup for newsletter

## License

[OSL - Open Software Licence 3.0](https://opensource.org/licenses/osl-3.0.php)
