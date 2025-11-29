<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use TapPay\Tap\ValueObjects\Authentication;

/**
 * Trait for handling 3DS authentication
 */
trait HasAuthentication
{
    public function threeDSecure(bool $enabled = true): static
    {
        $this->data['threeDSecure'] = $enabled;

        return $this;
    }

    public function withoutThreeDSecure(): static
    {
        return $this->threeDSecure(false);
    }

    public function authentication(array|Authentication $authentication): static
    {
        $this->data['authentication'] = $authentication instanceof Authentication
            ? $authentication->toArray()
            : $authentication;

        return $this;
    }

    public function authenticationDetails(
        string $eci,
        ?string $cavv = null,
        ?string $xid = null,
        ?string $dsTransId = null,
        ?string $version = null
    ): static {
        return $this->authentication(new Authentication(
            eci: $eci,
            cavv: $cavv,
            xid: $xid,
            dsTransId: $dsTransId,
            version: $version
        ));
    }
}
