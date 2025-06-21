# PHP Paginator

[![Version](https://img.shields.io/badge/version-2.0.1-blue.svg)](https://github.com/solophp/paginator)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](https://opensource.org/licenses/MIT)

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
    array $limitOptions = [10, 25, 50, 100]
): PaginationResult
```

Parameters:
- `$queryParams` - Array of query parameters (typically `$_GET` or `$request->getQueryParams()`)
- `$totalItems` - Total number of items to paginate
- `$limitOptions` - Optional array of allowed limit values. Default is `[10, 25, 50, 100]`

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
            totalItems: $totalItems
        );

        return $this->view->render('products/list', [
            'pagination' => $result
        ]);
    }
}
```

Custom limit options:

```php
$result = Paginator::paginate(
    queryParams: $queryParams,
    totalItems: 100,
    limitOptions: [20, 40, 60, 80]
);
```

## PaginationResult

The `paginate()` method returns a `PaginationResult` object with the following properties:

- `page`: Current page number
- `limit`: Items per page limit
- `totalPages`: Total number of pages
- `totalItems`: Total number of items
- `links`: Array of pagination links with url, page number and current state
- `nextPageUrl`: URL for the next page
- `previousPageUrl`: URL for the previous page
- `hasNextPage`: Whether there is a next page
- `hasPreviousPage`: Whether there is a previous page
- `limitOptions`: Array of available limit options with urls

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
    <?php foreach ($result->limitOptions as $option): ?>
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
- Default limit: 25
- Default limit options: [10, 25, 50, 100]
- Minimum links count: 3

## Query Parameters

The paginator uses the following query parameters:
- `page`: Current page number
- `limit`: Number of items per page (previously `per_page`)

All other query parameters in the URL are preserved.

## License

MIT