paseto-bundle
=============
PasetoBundle is a Symfony bundle to integrate [Paseto](https://github.com/paragonie/paseto/) into Symfony applications.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 1: Install bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```shell
$ composer require mehdibo/paseto-bundle
```

### Step 2: Configuration
Add environment variables to `.env`:
```dotenv
###> mehdibo/paseto-bundle ###
PASETO_SYMMETRIC_KEY=
PASETO_ASYMMETRIC_SECRET_KEY=
###< mehdibo/paseto-bundle ###
```

You can generate keys using the bundle's command:
```shell
./bin/console mehdibo:paseto:generate-symmetric
./bin/console mehdibo:paseto:generate-generate-asymmetric
```

Create the configuration file `config/packages/mehdibo_paseto.yaml`

```yaml
mehdibo_paseto:
  secret_keys:
    symmetric_key: '%env(PASETO_SYMMETRIC_KEY)%'
    asymmetric_key: '%env(PASETO_ASYMMETRIC_SECRET_KEY)%'
```

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Mehdibo\Bundle\PasetoBundle\MehdiboPasetoBundle::class => ['all' => true],
];
```

Usage
============

### Creating Paseto tokens

You can use the bundle's services to create tokens.

```php

// For building local tokens
$localBuilder = new \Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder();
// For building public tokens
$publicBuilder = new \Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder();
```

From a controller:

```php
namespace App\Controller;

use Mehdibo\Bundle\PasetoBundle\Services\LocalPasetoBuilder;
use Mehdibo\Bundle\PasetoBundle\Services\PublicPasetoBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TokensController extends AbstractController
{

    #[Route('/public', name: 'public')]
    public function public(PublicPasetoBuilder $builder): Response
    {
        $builder->setIssuedAt()->setClaims(['custom' => 'claim']);
        return new Response($builder->toString());
    }

    #[Route('/local', name: 'local')]
    public function local(LocalPasetoBuilder $builder): Response
    {
        $builder->setIssuedAt()->setClaims(['custom' => 'claim']);
        return new Response($builder->toString());
    }
}
```

### Decoding Paseto tokens

TODO