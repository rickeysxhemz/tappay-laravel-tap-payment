# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability within this package, please send an email to **dev@waqasmajeed.dev**. All security vulnerabilities will be promptly addressed.

### What to Include

When reporting a vulnerability, please include:

1. Description of the vulnerability
2. Steps to reproduce
3. Potential impact
4. Suggested fix (if any)

### Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 5 business days
- **Resolution**: Dependent on severity and complexity

### Disclosure Policy

- We will acknowledge receipt of your vulnerability report
- We will provide an estimated timeline for a fix
- We will notify you when the vulnerability is fixed
- We ask that you do not publicly disclose the vulnerability until we have had a chance to address it

## Security Best Practices

When using this package, please ensure:

1. **Never commit API keys** - Use environment variables
2. **Validate webhook signatures** - Always verify `x-tap-signature` header
3. **Use HTTPS** - Ensure your redirect URLs use HTTPS
4. **Keep dependencies updated** - Regularly run `composer update`
5. **Use test keys in development** - Never use production keys for testing

## Known Security Considerations

### Webhook Validation

This package validates webhook signatures using HMAC-SHA256. Always ensure:

```php
// Webhook validation is automatic when using WebhookController
// The package validates x-tap-signature header
```

### API Key Storage

Store your Tap API keys securely:

```env
# .env file (never commit this)
TAP_SECRET_KEY=sk_live_xxxxx
TAP_PUBLISHABLE_KEY=pk_live_xxxxx
```

### CSRF Protection

The webhook endpoint should be excluded from CSRF verification:

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'tap/webhook',
];
```

## Security Updates

Security updates will be released as patch versions and announced through:

- GitHub Security Advisories
- Release notes
- Package changelog