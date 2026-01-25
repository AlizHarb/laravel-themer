# Testing

We strictly enforce testing to ensure the stability of the package.

## Running Tests

You can run the full test suite using Pest:

```bash
composer test
```

Or manually via:

```bash
vendor/bin/pest
```

## Coverage

The test suite covers:
- Unit tests for all Managers and Helper classes.
- Feature tests for all Artisan commands.
- End-to-end flow tests for theme lifecycle.
