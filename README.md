# Pasarguard Sub Proxy

A simple PHP reverse proxy that forwards Pasarguard subscription links to another host (e.g. cPanel) and returns the response as-is to the client.

---

## How to install

- Download the project files
- Open the main PHP file and edit the sections explained below
- Upload everything to your PHP host (cPanel or similar)
- In Pasarguard panel, set **URL Prefix** in:
  `Settings > Subscriptions`
  to your hosted proxy URL

---

## UPSTREAM

Set your source subscription endpoint here:

```php
const UPSTREAM = 'https://your-domain.com';
```

Use only the base URL (no trailing `/`).

---

## PROXY (optional)

If you have connection issues, enable proxy inside cURL settings.

Default is commented out:

```php
// CURLOPT_PROXYTYPE      => CURLPROXY_HTTP,
// CURLOPT_PROXY          => '0.0.0.0:443',
// CURLOPT_PROXYUSERPWD   => 'username:password',
```

Use only if direct connection fails.

Supports:
HTTP proxy
SOCKS5 proxy (if configured accordingly)

Reference:
https://curl.se/libcurl/c/CURLOPT_PROXYTYPE.html
