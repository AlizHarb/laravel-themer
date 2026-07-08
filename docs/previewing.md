# Theme Previewing

Preview URLs let teams inspect inactive themes without changing the global active theme.

```bash
php artisan theme:preview brand
php artisan theme:preview brand --path=/checkout
php artisan theme:preview brand --signed --expires=30
```

Unsigned preview URLs append `preview_theme=brand` to the target path.

Signed preview URLs use Laravel signed URLs and expire after the configured number of minutes.

Previewing is useful for:

- QA on staging.
- Client review links.
- Admin theme switchers.
- Verifying theme inheritance before activation.
