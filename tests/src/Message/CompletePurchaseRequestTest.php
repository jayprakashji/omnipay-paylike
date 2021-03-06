<?php

namespace ByTIC\Omnipay\Paylike\Tests\Message;

use ByTIC\Omnipay\Paylike\Message\CompletePurchaseRequest;
use ByTIC\Omnipay\Paylike\Message\CompletePurchaseResponse;
use ByTIC\Omnipay\Paylike\Tests\Fixtures\HttpRequestBuilder;

/**
 * Class CompletePurchaseRequestTest
 * @package ByTIC\Omnipay\Paylike\Tests\Message
 */
class CompletePurchaseRequestTest extends AbstractRequestTest
{
    public function testSimpleSend()
    {
        $client = $this->getHttpClient();
        $httpRequest = HttpRequestBuilder::createCompletePurchase();
        $request = new CompletePurchaseRequest($client, $httpRequest);
        $request->initialize(
            [
                'publicKey' => getenv('PAYLIKE_PUBLIC_KEY'),
                'privateKey' => getenv('PAYLIKE_PRIVATE_KEY')
            ]
        );

        /** @var CompletePurchaseResponse $response */
        $response = $request->send();
        self::assertInstanceOf(CompletePurchaseResponse::class, $response);
        self::assertTrue($response->isSuccessful());

        $data = $response->getData();
        self::assertIsArray($data);
        self::assertTrue($data['success']);
        self::assertArrayHasKey('transaction', $data);
    }
}
