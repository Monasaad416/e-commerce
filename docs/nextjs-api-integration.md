# Next.js Integration Guide (Laravel API)

This project currently uses Sanctum personal access tokens (Bearer auth) for API authentication.

## 1) API base URL

All routes are under:

- `/api/v1/{locale}`

Examples:

- `POST /api/v1/en/login`
- `GET /api/v1/en/products`
- `GET /api/v1/en/cart` (auth required)

Set in Next.js:

```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1/en
```

## 2) Auth flow (recommended now)

1. Call `POST /login` with `email` and `password`.
2. Save returned `token`.
3. Send `Authorization: Bearer <token>` for protected endpoints.
4. On logout, call `POST /logout` with the bearer token.

## 3) Example fetch client

```ts
const API_BASE = process.env.NEXT_PUBLIC_API_BASE_URL!;

export async function apiFetch<T>(path: string, options: RequestInit = {}): Promise<T> {
  const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;

  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options.headers ?? {}),
    },
  });

  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`);
  }

  return response.json() as Promise<T>;
}
```

## 4) CORS

`config/cors.php` must include your Next.js origins in `allowed_origins`.

Dev example:

- `http://localhost:3000`

Production example:

- `https://shop.example.com`

## 5) Security/production notes

- Use HTTPS in production.
- Prefer storing tokens in secure HttpOnly cookies via a Next.js backend proxy/BFF.
- Keep login/register rate-limited.
- Do not trust frontend prices/totals; backend recalculates monetary values.
