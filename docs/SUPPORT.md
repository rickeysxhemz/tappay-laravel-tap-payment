# Support

## Documentation

- [README](README.md) - Installation and basic usage
- [CONTRIBUTING](CONTRIBUTING.md) - How to contribute
- [TESTING](TESTING.md) - Testing guide

## Getting Help

### GitHub Issues

For bug reports and feature requests, please use [GitHub Issues](https://github.com/tappay/laravel-tap-payment/issues).

Before creating an issue:

1. Search existing issues to avoid duplicates
2. Use the appropriate issue template
3. Provide as much detail as possible

### GitHub Discussions

For questions and general discussions, use [GitHub Discussions](https://github.com/tappay/laravel-tap-payment/discussions).

Good for:
- Usage questions
- Best practices
- Feature ideas
- Community support

## External Resources

### Tap Payments Documentation

- [Tap API Documentation](https://developers.tap.company/)
- [Tap Dashboard](https://dashboard.tap.company/)
- [Tap Support](https://support.tap.company/)

### Laravel Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel News](https://laravel-news.com/)

## Common Issues

### API Authentication Errors

If you receive 401 errors:
- Verify your `TAP_SECRET_KEY` is correct
- Ensure you're using the right key (test vs live)
- Check that the key hasn't been revoked

### Webhook Not Receiving Events

1. Verify your webhook URL is accessible
2. Check that you've configured the webhook in Tap Dashboard
3. Ensure CSRF is disabled for the webhook route
4. Check your server logs for incoming requests

### Minimum Charge Amount

Tap has minimum charge amounts per currency:
- 3-decimal currencies (KWD, BHD, OMR): 0.100 minimum
- 2-decimal currencies (SAR, AED, USD): 0.10 minimum

## Response Time

- **Bug Reports**: We aim to respond within 48 hours
- **Feature Requests**: Reviewed on a regular basis
- **Security Issues**: See [SECURITY.md](SECURITY.md)

## Professional Support

For professional support or custom development, please contact the maintainer directly.