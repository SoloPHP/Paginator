# PHP Paginator

A lightweight, framework-agnostic pagination package for PHP 8.2+.

## Features

- Zero dependencies
- Framework agnostic
- Configurable items per page
- Maintains all query parameters in URLs
- SEO-friendly pagination links
- Ellipsis support for long page lists
- Static interface
- Pre-calculated pagination support

## Requirements

- PHP 8.2 or higher

## Installation

You can install the package via composer:

```bash
composer require solo/paginator
```

## Usage

```php
public static function paginate(
    array $queryParams,
    int $totalItems,
    ?array $allowedPerPage = null,
    ?int $currentPage = null,
    ?int $currentPerPage = null,
): PaginationResult
```

Parameters:
- `$queryParams` - Array of query parameters (typically `$_GET` or `$request->getQueryParams()`)
- `$totalItems` - Total number of items to paginate
- `$allowedPerPage` - Optional array of allowed "per page" values. Default is `[10, 25, 50, 100]`
- `$currentPage` - Optional pre-calculated current page number
- `$currentPerPage` - Optional pre-calculated items per page value

Basic usage:

```php
use Solo\Paginator;

class ProductsController
{
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams(); // or $_GET
        $result = Paginator::paginate(
            queryParams: $queryParams,
            totalItems: $totalItems,
            currentPage: $page,
            currentPerPage: $perPage
        );

        return $this->view->render('products/list', [
            'pagination' => $result
        ]);
    }
}
```

Custom items per page options:

```php
$result = Paginator::paginate(
    queryParams: $queryParams,
    totalItems: 100,
    allowedPerPage: [20, 40, 60, 80]
);
```

## PaginationResult

The `paginate()` method returns a `PaginationResult` object with the following properties:

- `page`: Current page number
- `perPage`: Items per page
- `totalPages`: Total number of pages
- `links`: Array of pagination links with url, page number and current state
- `nextPageUrl`: URL for the next page
- `previousPageUrl`: URL for the previous page
- `hasNextPage`: Whether there is a next page
- `hasPreviousPage`: Whether there is a previous page
- `perPageOptions`: Array of available per page options with urls

### Template Usage

Basic pagination links:

```php
<?php foreach ($result->links as $link): ?>
    <?php if ($link->isEllipsis): ?>
        <span>...</span>
    <?php else: ?>
        <a 
            href="<?= $link->url ?>"
            <?= $link->isCurrent ? 'class="active"' : '' ?>
        >
            <?= $link->page ?>
        </a>
    <?php endif; ?>
<?php endforeach; ?>
```

Items per page selector:

```php
<select onchange="window.location.href=this.value">
    <?php foreach ($result->perPageOptions as $option): ?>
        <option 
            value="<?= $option->url ?>"
            <?= $option->isCurrent ? 'selected' : '' ?>
        >
            <?= $option->value ?> items
        </option>
    <?php endforeach; ?>
</select>
```

Navigation links:

```php
<?php if ($result->hasPreviousPage): ?>
    <a href="<?= $result->previousPageUrl ?>">Previous</a>
<?php endif; ?>

<?php if ($result->hasNextPage): ?>
    <a href="<?= $result->nextPageUrl ?>">Next</a>
<?php endif; ?>
```

## Default Values

- Default page: 1
- Default items per page: 50
- Default per page options: [10, 25, 50, 100]
- Minimum links count: 3

## Query Parameters

The paginator uses the following query parameters:
- `page`: Current page number
- `per-page`: Number of items per page

All other query parameters in the URL are preserved.

## License

MIT