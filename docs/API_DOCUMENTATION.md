# API Documentation Guide

This document explains how the API documentation is configured and how to work with it in different environments.

## Overview

The API documentation is generated using [Scribe](https://scribe.knuckles.wtf/), a Laravel API documentation generator. It automatically generates beautiful documentation from your routes, controllers, and docblocks.

## Configuration

The API documentation configuration is located in `config/scribe.php`.

### Key Features

- **Automatic URL Detection**: The documentation automatically uses the correct base URL based on your environment
- **Multiple Formats**: Generates HTML docs, Postman collection, and OpenAPI (Swagger) specification
- **Try It Out**: Interactive API testing directly from the documentation
- **Authentication**: Configured for Laravel Sanctum bearer token authentication

## Environment Configuration

The documentation base URL is automatically configured based on your `APP_URL` environment variable:

### Local Development
```env
APP_URL=http://127.0.0.1:8030
```
Documentation will use: `http://127.0.0.1:8030/api`

### Production
```env
APP_URL=https://www.marketplace.ng
```
Documentation will use: `https://www.marketplace.ng/api`

### Testing with Authentication

To test protected endpoints during documentation generation, set up a test authentication token:

```env
SCRIBE_AUTH_KEY=your_test_bearer_token_here
```

To get a token:
1. Create a test user account
2. Login via `/api/login` endpoint
3. Copy the returned access token
4. Add it to your `.env` file as `SCRIBE_AUTH_KEY`

## Generating Documentation

### Generate All Documentation

```bash
php artisan scribe:generate
```

This command will:
- Process all API routes matching `api/*`
- Generate HTML documentation in `public/docs/`
- Generate Postman collection in `public/docs/collection.json`
- Generate OpenAPI spec in `public/docs/openapi.yaml`

### View Documentation

After generation, you can access the documentation at:

**Local**: http://127.0.0.1:8030/docs
**Production**: https://www.marketplace.ng/docs

## Documentation Outputs

### 1. HTML Documentation
- **Location**: `public/docs/index.html`
- **Features**:
  - Beautiful, searchable interface
  - Code examples in Bash, JavaScript, PHP, and Python
  - Try It Out feature for testing endpoints
  - Organized by endpoint groups

### 2. Postman Collection
- **Location**: `public/docs/collection.json`
- **Usage**: Import into Postman for API testing
- **Features**: All endpoints with examples and authentication

### 3. OpenAPI Specification
- **Location**: `public/docs/openapi.yaml`
- **Usage**: Use with Swagger UI or other OpenAPI tools
- **Version**: OpenAPI 3.0.3

## Documenting Your Endpoints

### Using DocBlocks

Add documentation to your controller methods using PHP docblocks:

```php
/**
 * Get all adverts
 *
 * Returns a paginated list of all active adverts with optional filtering.
 *
 * @group Adverts
 *
 * @queryParam per_page int Number of items per page. Default: 20. Example: 20
 * @queryParam page int Page number. Default: 1. Example: 1
 * @queryParam section string Filter by section (all, gallery, featured, cars, phones, fashion). Example: featured
 *
 * @response 200 {
 *   "success": true,
 *   "data": {
 *     "listings": [...],
 *     "featured": [...]
 *   }
 * }
 */
public function index(Request $request)
{
    // Your code here
}
```

### Marking Authenticated Endpoints

Use the `@authenticated` tag for protected routes:

```php
/**
 * Create new advert
 *
 * @group Adverts
 * @authenticated
 *
 * @bodyParam title string required The advert title. Example: Samsung Galaxy S24
 * @bodyParam price numeric required The price. Example: 50000
 *
 * @response 201 {
 *   "success": true,
 *   "data": {...}
 * }
 */
public function store(Request $request)
{
    // Your code here
}
```

### Marking Unauthenticated Endpoints

For public endpoints when auth is enabled by default:

```php
/**
 * Get categories
 *
 * @group Categories
 * @unauthenticated
 */
public function categories()
{
    // Your code here
}
```

## Endpoint Groups

Endpoints are automatically organized into groups. Current groups in order:
1. Authentication
2. Adverts
3. User Management
4. Messages
5. Payments
6. Search
7. Locations

To assign an endpoint to a group, use the `@group` tag:

```php
/**
 * @group Authentication
 */
```

## Deployment Workflow

### Before Deploying to Production

1. **Update .env file** with production APP_URL:
   ```env
   APP_URL=https://www.marketplace.ng
   ```

2. **Generate documentation**:
   ```bash
   php artisan scribe:generate
   ```

3. **Commit the generated docs**:
   ```bash
   git add public/docs/
   git commit -m "Update API documentation"
   ```

4. **Deploy** to production

### CI/CD Integration

You can add documentation generation to your CI/CD pipeline:

```yaml
# Example for GitHub Actions
- name: Generate API Documentation
  run: |
    php artisan scribe:generate
    git add public/docs/
    git commit -m "Update API documentation [skip ci]" || true
```

## Troubleshooting

### Documentation not updating?

Clear the Scribe cache:
```bash
rm -rf .scribe/endpoints.cache/
php artisan scribe:generate
```

### Wrong base URL in documentation?

Check your `APP_URL` in `.env`:
```bash
grep APP_URL .env
```

Then regenerate:
```bash
php artisan scribe:generate
```

### Authentication not working in "Try It Out"?

1. Make sure CORS is properly configured for your domain
2. Check that your `SCRIBE_AUTH_KEY` is valid
3. Verify the token format is correct (Bearer token)

### Routes not appearing?

Make sure your routes match the pattern in `config/scribe.php`:
```php
'prefixes' => ['api/*'],
```

## Best Practices

1. **Keep documentation updated**: Regenerate after route changes
2. **Use meaningful descriptions**: Write clear, concise docblock descriptions
3. **Provide examples**: Include example values for parameters
4. **Test before deploying**: Always test the "Try It Out" feature
5. **Version your API**: Update the version in `config/scribe.php` when making breaking changes
6. **Group logically**: Use `@group` tags to organize related endpoints

## Additional Resources

- [Scribe Documentation](https://scribe.knuckles.wtf/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)

## Support

If you encounter issues with the API documentation:
1. Check the [Scribe documentation](https://scribe.knuckles.wtf/)
2. Review the Laravel logs: `storage/logs/laravel.log`
3. Verify your PHP version compatibility (Scribe requires PHP 8.0+)
