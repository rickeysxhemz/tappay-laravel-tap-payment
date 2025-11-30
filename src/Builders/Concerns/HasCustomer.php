<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use InvalidArgumentException;
use TapPay\Tap\ValueObjects\Customer;

/**
 * Trait for handling customer data
 */
trait HasCustomer
{
    /**
     * @param  array<string, mixed>|Customer  $customer
     */
    public function customer(array|Customer $customer): static
    {
        $existing = $this->getExistingCustomerData();
        $newData = $customer instanceof Customer ? $customer->toArray() : $customer;
        $this->data['customer'] = array_merge($existing, $newData);

        return $this;
    }

    public function customerId(string $customerId): static
    {
        $existing = $this->getExistingCustomerData();
        $this->data['customer'] = array_merge($existing, ['id' => $customerId]);

        return $this;
    }

    public function customerFirstName(string $firstName): static
    {
        $firstName = trim($firstName);

        if (empty($firstName)) {
            throw new InvalidArgumentException('First name cannot be empty');
        }

        if (strlen($firstName) > 100) {
            throw new InvalidArgumentException('First name cannot exceed 100 characters');
        }

        $existing = $this->getExistingCustomerData();
        $this->data['customer'] = array_merge($existing, ['first_name' => $firstName]);

        return $this;
    }

    public function customerLastName(string $lastName): static
    {
        $lastName = trim($lastName);

        if (empty($lastName)) {
            throw new InvalidArgumentException('Last name cannot be empty');
        }

        if (strlen($lastName) > 100) {
            throw new InvalidArgumentException('Last name cannot exceed 100 characters');
        }

        $existing = $this->getExistingCustomerData();
        $this->data['customer'] = array_merge($existing, ['last_name' => $lastName]);

        return $this;
    }

    public function customerEmail(string $email): static
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        $existing = $this->getExistingCustomerData();
        $this->data['customer'] = array_merge($existing, ['email' => $email]);

        return $this;
    }

    public function customerPhone(string $countryCode, string $number): static
    {
        if (! preg_match('/^\d{1,4}$/', $countryCode)) {
            throw new InvalidArgumentException('Country code must be 1-4 digits');
        }

        if (! preg_match('/^\d{6,15}$/', $number)) {
            throw new InvalidArgumentException('Phone number must be 6-15 digits');
        }

        $existing = $this->getExistingCustomerData();
        $this->data['customer'] = array_merge($existing, [
            'phone' => [
                'country_code' => $countryCode,
                'number' => $number,
            ],
        ]);

        return $this;
    }

    /**
     * Get existing customer data with proper type safety
     *
     * @return array<string, mixed>
     */
    private function getExistingCustomerData(): array
    {
        if (isset($this->data['customer']) && is_array($this->data['customer'])) {
            /** @var array<string, mixed> */
            return $this->data['customer'];
        }

        return [];
    }
}
