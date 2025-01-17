<?php

namespace Contributte\GopayInline\Service;

use Contributte\GopayInline\Api\Entity\Payment;
use Contributte\GopayInline\Api\Lists\TargetType;
use Contributte\GopayInline\Api\Objects\Target;

abstract class AbstractPaymentService extends AbstractService
{

	/**
	 * Add required target field
	 *
	 * @param Payment $payment
	 * @return void
	 */
	protected function preConfigure(Payment $payment)
	{
		if (!$payment->getTarget()) {
			$target = new Target();
			$target->goid = $this->client->getGoId();
			$target->type = TargetType::ACCOUNT;

			$payment->setTarget($target);
		}
	}

}
