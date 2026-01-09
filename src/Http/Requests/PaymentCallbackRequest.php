<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tap_id' => ['nullable', 'string'],
            'redirect' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tap_id' => $this->query('tap_id'),
            'redirect' => $this->query('redirect'),
        ]);
    }

    public function tapId(): ?string
    {
        $tapId = $this->input('tap_id');

        return is_string($tapId) && $tapId !== '' ? $tapId : null;
    }

    public function redirectUrl(): ?string
    {
        $redirect = $this->input('redirect');

        return is_string($redirect) ? $redirect : null;
    }
}
