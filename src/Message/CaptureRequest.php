<?php

namespace ByTIC\Omnipay\Paylike\Message;

use ByTIC\Omnipay\Paylike\Helper;
use ByTIC\Omnipay\Paylike\Traits\HasApiTrait;

/**
 * Class CaptureRequest
 * @package ByTIC\Omnipay\Paylike\Message
 *
 * @method CaptureResponse send
 */
class CaptureRequest extends AbstractRequest
{
    use HasApiTrait;

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('privateKey', 'transactionId', 'amount');

        $data = ['success' => false];

        $parameters = [
            'amount' => (int)round($this->getAmount() * 100), // api requires int
        ];

        //optionals
        $currency = $this->getParameter('currency');
        if (is_string($currency) && strlen($currency) === 3) {
            $parameters['currency'] = $currency;
        }
        $descriptor = $this->getParameter('descriptor');
        if (is_string($descriptor)) {
            if(Helper::validateDescriptor($descriptor)){
                throw new \Omnipay\Common\Exception\InvalidRequestException("The descriptor does not conform to requirements.");
            }
            $parameters['descriptor'] = $descriptor;
        }


        try {
            $transactions = $this->getApi()->transactions();
            $transaction = $transactions->capture(
                $this->getTransactionId(),
                $parameters
            );
            if (is_array($transaction)) {
                $data['transaction'] = $transaction;
                $data['success'] = true;
            }
        } catch (\Paylike\Exception\NotFound $e) {
            //The transaction was not found
            $data['exception_class'] = "\Paylike\Exception\NotFound";
            $data['message'] = "The transaction was not found";

        } catch (\Paylike\Exception\InvalidRequest $e) {
            // Bad (invalid) request - see $e->getJsonBody() for the error
            $data['exception_class'] = "\Paylike\Exception\InvalidRequest";
            $data['message'] = "Bad (invalid) request - ".substr(json_encode($e->getJsonBody()), 0, 250 );

        } catch (\Paylike\Exception\Forbidden $e) {
            // You are correctly authenticated but do not have access.
            $data['exception_class'] = "\Paylike\Exception\Forbidden";
            $data['message'] = "You are correctly authenticated but do not have access.";

        } catch (\Paylike\Exception\Unauthorized $e) {
            // You need to provide credentials (an app's API key)
            $data['exception_class'] = "\Paylike\Exception\Unauthorized";
            $data['message'] = "You need to provide credentials (an app's API key)";

        } catch (\Paylike\Exception\Conflict $e) {
            // Everything you submitted was fine at the time of validation, but something changed in the meantime and came into conflict with this (e.g. double-capture).
            $data['exception_class'] = "\Paylike\Exception\Conflict";
            $data['message'] = "Everything you submitted was fine at the time of validation, but something changed in the meantime and came into conflict with this (e.g. double-capture)";

        } catch (\Paylike\Exception\ApiConnection $e) {
            // Network error on connecting via cURL
            $data['exception_class'] = "\Paylike\Exception\ApiConnection";
            $data['message'] = "Network error on connecting via cURL";

        } catch (\Paylike\Exception\ApiException $e) {
            $data['exception_class'] = "\Paylike\Exception\ApiException";
            $data['message'] = "Api Error:" . $e->getMessage();
        }

        return $data;
    }
}
