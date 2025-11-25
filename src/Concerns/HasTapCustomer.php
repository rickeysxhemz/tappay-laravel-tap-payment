<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Customer;

trait HasTapCustomer
{
    public function tapCustomerId(): ?string
    {
        return $this->tap_customer_id ?? null;
    }

    public function setTapCustomerId(?string $customerId): void
    {
        $this->tap_customer_id = $customerId;
        $this->save();
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    protected function ensureTapCustomerExists(): void
    {
        if ($this->tapCustomerId()) {
            return;
        }

        $this->refresh();

        if (!$this->tapCustomerId()) {
            $this->createAsTapCustomer();
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function createAsTapCustomer(array $options = []): Customer
    {
        if ($this->tapCustomerId()) {
            return Tap::customers()->retrieve($this->tapCustomerId());
        }

        $email = $this->email ?? null;

        if ($email === null || $email === '') {
            throw new InvalidArgumentException('Email is required to create a Tap customer');
        }

        $firstName = $this->name ?? $this->first_name ?? 'Guest';

        $customerData = array_merge([
            'first_name' => $firstName,
            'email' => $email,
        ], $options);

        if ($this->phone ?? null) {
            $customerData['phone'] = [
                'country_code' => $this->phone_country_code ?? config('tap.default_country_code', '968'),
                'number' => $this->phone,
            ];
        }

        $customer = Tap::customers()->create($customerData);

        if (!$customer->id()) {
            throw new ApiErrorException('Failed to create Tap customer');
        }

        $this->setTapCustomerId($customer->id());

        return $customer;
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function asTapCustomer(): ?Customer
    {
        return $this->tapCustomerId()
            ? Tap::customers()->retrieve($this->tapCustomerId())
            : null;
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function updateTapCustomer(array $data): Customer
    {
        if (!$this->tapCustomerId()) {
            return $this->createAsTapCustomer($data);
        }

        return Tap::customers()->update($this->tapCustomerId(), $data);
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function deleteTapCustomer(): bool
    {
        if (!$this->tapCustomerId()) {
            return false;
        }

        $deleted = Tap::customers()->delete($this->tapCustomerId());

        if ($deleted) {
            $this->setTapCustomerId(null);
        }

        return $deleted;
    }
}