<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    'api_path'   => 'api',
    'api_domain' => null,
    'export_path' => 'api.json',

    'info' => [
        'version'     => env('API_VERSION', '1.0.0'),
        'description' => <<<'MD'
# Nisa Ticaret API

Production-grade e-commerce REST API for the **Nisa Ticaret** beverage distribution platform.

## Authentication
All protected endpoints require a **Bearer token** issued by `POST /api/v1/auth/firebase-login`.

Include it in every request:
```
Authorization: Bearer <token>
```

## Rate Limiting
| Endpoint group   | Limit           |
|------------------|-----------------|
| Login            | 10 req / minute |
| General API      | 60 req / min (guest) · 120 req / min (authenticated) |
| Admin analytics  | 300 req / minute |

## Response Format
All responses return JSON. Errors follow the shape:
```json
{ "message": "...", "errors": { "field": ["..."] } }
```

## Status Codes
| Code | Meaning |
|------|---------|
| 200  | OK |
| 201  | Created |
| 401  | Unauthenticated |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 429  | Too Many Requests |
| 500  | Server Error |
MD,
    ],

    'ui' => [
        'title'                    => 'Nisa Ticaret API Docs',
        'theme'                    => 'system',
        'hide_try_it'              => false,
        'hide_schemas'             => false,
        'logo'                     => '',
        'try_it_credentials_policy' => 'include',
        'layout'                   => 'responsive',
    ],

    'servers' => null,

    'enum_cases_description_strategy' => 'description',
    'enum_cases_names_strategy'        => false,
    'flatten_deep_query_parameters'    => true,

    'middleware' => [
        'web',
        // Remove RestrictedDocsAccess so docs are publicly accessible in dev.
        // Add it back (or IP whitelist) for production.
    ],

    'extensions' => [],
];
