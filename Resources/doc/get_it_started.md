# Get it started

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
composer require "payum/payum-bundle" "payum/offline" "php-http/guzzle7-adapter"
```

_**Note**: Where payum/offline is a php payum extension, you can for example change it to payum/paypal-express-checkout-nvp or payum/stripe. Look at [supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md) to find out what you can use._

Enable the bundle in the kernel (only if you do not use Symfony Flex):

``` php
<?php
// config/bundles.php

return [
        // ...
        Payum\Bundle\PayumBundle\PayumBundle::class => ['all' => true],
];
```

So now after you registered the bundle let's import routing.

```yaml
# config/routes/payum.yaml

payum_all:
    resource: "@PayumBundle/Resources/config/routing/all.xml"
```

## Configure

First we need two entities: a token and an order. 
The token entity is used to protect your payments where the second one stores all your payment information.

_**Note**: In this chapter we show how to use Doctrine ORM entities. There are other supported [storages](storages.md)._

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class PaymentToken extends Token
{
}
```

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;
}
```

next, you have to add mapping of the basic entities you are extended, and configure payum's storages:

```yml
#config/packages/payum.yml

payum:
    security:
        token_storage:
            Acme\PaymentBundle\Entity\PaymentToken: { doctrine: orm }

    storages:
        Acme\PaymentBundle\Entity\Payment: { doctrine: orm }
            
    gateways:
        offline:
            factory: offline
```

_**Note**: You can add other gateways to the gateways section too._

## Prepare order

At this stage we have to create an order. Add some information into it. 
Create a capture token and delegate the job to capture action.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

use Payum\Bundle\PayumBundle\Controller\PayumController;

class PaymentController extends PayumController
{
    public function prepareAction() 
    {
        $gatewayName = 'offline';
        
        $storage = $this->payum->getStorage('Acme\PaymentBundle\Entity\Payment');
        
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        
        $storage->update($payment);
        
        $captureToken = $this->payum->getTokenFactory()->createCaptureToken(
            $gatewayName, 
            $payment, 
            'done' // the route to redirect after capture
        );
        
        return $this->redirect($captureToken->getTargetUrl());    
    }
}
```

## Payment is done

After the capture did its job you will be redirected to done action. 
One we set while token creation in prepare action.
You can read more about it in the dedicated [chapter](purchase_done_action.md)
The capture action script always redirects you to done one, no matter the payment was successful or not.
In done action we may check the payment status, update the model, dispatch events and so on.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php
namespace Acme\PaymentBundle\Controller;

use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Payum\Bundle\PayumBundle\Controller\PayumController;

class PaymentController extends PayumController
{
    public function doneAction(Request $request)
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);
        
        $gateway = $this->payum->getGateway($token->getGatewayName());
        
        // you can invalidate the token. The url could not be requested any more.
        // $this->payum->getHttpRequestVerifier()->invalidate($token);
        
        // Once you have token you can get the model from the storage directly. 
        //$identity = $token->getDetails();
        //$payment = $this->payum->getStorage($identity->getClass())->find($identity);
        
        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();
        
        // you have order and payment status 
        // so you can do whatever you want for example you can just print status and payment details.
        
        return new JsonResponse(array(
            'status' => $status->getValue(),
            'payment' => array(
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails(),
            ),
        ));
    }
}
```

## Next Steps

* [Custom purchase examples](custom_purchase_examples.md).
* [Configure payment in backend](configure-payment-in-backend.md)
* [Done action](purchase_done_action.md)
* [Sandbox](sandbox.md)
* [Console commands](console_commands.md)
* [Debugging](debugging.md)
* [Container tags](container_tags.md).
* [Payment configurations](configuration_reference.md)
* [Back to index](index.md).
