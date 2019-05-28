#Api for watch on your crypto wallets
you can add wallets to your bookmarks and watch on balance

Requirements
------------
- [Docker][1]

Need to do before start container
---------------------------------
* clone repository `git clone git@github.com:KonstantinPopov/sf4-rest-api.git /local/path/application`
* go to the folder with application `cd /local/path/application` 
* define local environment 
* copy `.env` into `.env.local`. eg for dev env:
```
APP_SECRET=ce54664062276629e7f53870faf2a52a
XDEBUG_CONFIG=remote_host=docker.for.mac.localhost
PHP_IDE_CONFIG=serverName=rest-api.local
APP_ENV=dev
ETHER_SCAN_API_KEY='DSF46D4G1FCVQ9WDQWU4CFRQYMN9ANXVQ6'
MYSQL_ROOT_PASSWORD=rootpass
DB_NAME=rest_api
DATABASE_URL=mysql://root:${MYSQL_ROOT_PASSWORD}@db/${DB_NAME}
```
* customize your environment variable


Installation
------------
* `docker-compose up`

Development
----------
* define 'rest-api.local' [server][2] in your ide
* xdebug enable, set up environment for xdebug(look above)
* set up docker php interpreter
* debug in test, add extra parameters to your interpreter in ide 
`-dxdebug.remote_enable=1 -dxdebug.remote_port=9000 -dxdebug.remote_host=docker.for.mac.localhost -dxdebug.remote_mode=jit`
and choice docker php interpreter as default interpreter, And set up path to autoload file and path phpunit.xml.dist
* enable phpmd, phpcs, php-cs-fixer in your ide config

How to use
----------
* go to swagger of API `http://127.0.0.1:8080/api/doc` that describe api endpoints
* api base path `http://127.0.0.1:8080/api/` you can define your host for that
* in dev environment load fixtures. so for test can be uses API token `1234567890` of test user.


Sync Balance:
-------------
### Cron
* automatically runs every minute, defined command in scheduled file: `docker/crontab/scheduled.crontab`
* stop cron `docker-compose exec php service cron stop` 
* start cron `docker-compose exec php service cron start` 
###Manual run
`docker-compose exec php bin/console app:sync-balance`


Run Test
--------
` docker-compose exec php  composer test`


Add new Currency
----------------
* create new migrations `docker-compose exec php bin/console make:migration`
* execute new migrations `docker-compose exec php bin/console :migration`


Add support new Api platform
----------------------------
- create new class - it have to impliment AdapterInterface

```
<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use Psr\Http\Message\ResponseInterface;

/**
 * Use this adapter in tests
 */
class NewAdapter extends AbstractAdapter
{
      const SLUG = 'new-api-adapter';
  
      protected const ENDPOINT = '/get_balance/{ADDRESS}';
  
      /**
       * {@inheritDoc}
       */
      protected function getEndpointOptionsByWallet(Wallet $wallet): array
      {
          return [['{ADDRESS}'], [$wallet->getAddress()], ];
      }
  
      /**
       * {@inheritDoc}
       */
      protected function mappingResponseBalance($responseData)
      {
          if (is_null($balance = $responseData['balance'] ?? null)) {
              throw new WrongApiResponseException('Wrong Response. Cant map response.');
          }
  
          return $responseData['balance];
      }
}
```

and define argument
```
#/config/service.yml

service:
    ...
    App\Services\Wallet\ApiAdapters\NewAdapter:
        arguments: 
            - '@GuzzleHttp\ClientInterface'
            - '%env(NewAdapter_URL)%'
            - '%env(NewAdapter_API_KEY)%'
        tags:
            - { name: !php/const App\Services\Wallet\ApiAdapters\AdapterChain::TAG_NAME }
```
* define currencies that uses this adapter 
     write migrations and update/create currency that uses this adapter
* You can define custom http option in getEndpointOptions method:
```
    protected function getEndpointOptions():array
    {
        return ['auth' => ['x-api-key => $this->apiKey]];
    }
```


[1]: https://www.docker.com
[2]: https://www.jetbrains.com/help/phpstorm/servers.html