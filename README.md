## Raffle API

Raffle is a Laravel-based Application for running product raffles. It exposes endpoints for listing products, authenticating users, and creating/cancelling raffle entries, plus a console command that periodically starts raffles.

This document covers **setup**, **API endpoints**, **console commands**, and **testing**.

---

## Getting started

- **Requirements**
    - **PHP**: 8.1+ (matching your Laravel version)
    - **Composer**
    - **SQLite** (for tests) and your preferred DB (for local/dev)

- **Installation**
    1. Clone the repository:

        ```bash
        git clone https://github.com/gauravmhrjn/raffle.git
        cd raffle
        ```

    2. Quick setup script\*\*

        You can run the bundled script to install dependencies, reset the DB, seed demo data, and clear caches:

        ```bash
        sh setup.sh
        ```

        Internally this runs:

        ```bash
        composer install
        cp .env.example .env
        php artisan key:generate
        php artisan migrate:fresh
        php artisan db:seed
        php artisan optimize:clear
        ```

---

## Demo Seeder

Seeding is handled by `DatabaseSeeder`, which populate demo users, products, and initial raffle entries for local development and testing.

- **`UserSeeder`**
- **`BrandSeeder`**
- **`CategorySeeder`**
- **`ProductSeeder`**
- **`RaffleEntrySeeder`**

Demo user after successful seeding:

```bash
user@example.com
password
```

---

## API endpoints

### Authentication

- **POST** `/api/login`
    - **Description**: Authenticate a user and return an API token (Laravel Sanctum).
    - **Body**
        - `email` (string, required)
        - `password` (string, required)
    - **Success response (200)**:
        - `status`: `"success"`
        - `message`: `"You are logged in."`
        - `token`: personal access token for subsequent requests
    - **Error responses**
        - `401 Unauthorized` when credentials are invalid
        - `400 Bad Request` when validation fails

- **POST** `/api/logout` (requires Sanctum auth)
    - **Headers**
        - `Authorization: Bearer <token>`
    - **Description**: Log out the authenticated user and revoke their tokens.
    - **Success response (200)**:
        - `status`: `"success"`
        - `message`: `"You have been logged out."`

### Products

- **GET** `/api/products`
    - **Description**: List active raffle products (paginated).
    - **Response (200)**:
        - `data`: array of product objects (active products)
            - Each item includes: `name`, `brand`, `category`, `slug`, `sku`, `raffle_date`, `image_url`
        - `links`: pagination links
        - `meta`: pagination metadata
    - If there are no active products, returns `data: []` with the same pagination keys.

- **GET** `/api/products/{slug}`
    - **Description**: Show details for a single active product.
    - **Path params**
        - `slug`: unique, human-readable identifier for the product
    - **Success response (200)**:
        - A product object with:
            - `name`, `brand`, `category`, `slug`, `sku`, `price`, `description`, `raffle_date`, `image_url`
    - **Error response (404)**:
        - `status`: `"failed"`
        - `error`: `"Product not found."`

### Raffle entries

These endpoints are protected by:

- `auth:sanctum`
- `throttle:6,1` (max 6 requests per minute per user)

Always call them with:

- `Authorization: Bearer <token>`

- **POST** `/api/raffle/entry`
    - **Description**: Create (or retrieve existing) raffle entry for a product.
    - **Body**
        - `payment_token` (string, required) – pseudo token representing payment authorization
        - `product_id` (int, required) – must refer to an active product
        - `address_id` (int, required) – must belong to the authenticated user
    - **Success response (201 or 200)**:
        - `status`: `"success"`
        - `entry_code`: unique code for the raffle entry
    - **Error responses**
        - `400 Bad Request` with:
            - `status`: `"failed"`
            - `error`: describes:
                - product not active or does not exist
                - address does not exist
                - address does not belong to the user

- **DELETE** `/api/raffle/entry/delete`
    - **Description**: Cancel a pending raffle entry for the authenticated user.
    - **Body**
        - `product_id` (int, required)
    - **Success response (200)**:
        - `status`: `"success"`
    - **Error responses**
        - `400 Bad Request` when product does not exist or is not active, or no matching raffle entry is found.

---

## Console commands

- Command to start raffle processing
    - **Usage**:
        ```bash
        php artisan app:start-raffle
        ```
    - **Description**: Starts the raffle process by delegating to `StartRaffleAction` class.
    - Since the raffle process is based on queues and jobs, we will need to open and keep running the following artisan command on a new terminal to root directory.
      ```bash
      php artisan queue:work
      ```
---

## Testing

The project uses **PHPUnit** and Laravel’s testing helpers.

- **Configuration**
    - `phpunit.xml` defines:
        - **Test suites**:
            - `tests/Unit`
            - `tests/Feature`
        - **Source for coverage**:
            - `app/` directory
        - **Testing environment**:
            - In-memory SQLite database
            - `QUEUE_CONNECTION=sync`
            - `MAIL_MAILER=log`

- **Running the test suite**

    From the project root:

    ```bash
    php artisan test
    ```

- **Example areas covered by tests**
    - **API Feature tests**
        - `AuthControllerTest`: login, invalid credentials, validation failures, logout.
        - `ProductControllerTest`: listing products, showing individual product details, 404 cases.
        - `RaffleEntryControllerTest`: creating raffle entries, preventing duplicates, validating product and address ownership, cancelling entries.
    - **Command & actions**
        - `StartRaffleCommandTest`
        - Various `Actions` unit tests (e.g. `StartRaffleActionTest`, `SelectWinnersActionTest`, `CreateRaffleEntryActionTest`, etc.).

---

## Local development tips

- **API authentication**
    - Use `/api/login` to obtain a bearer token.
    - Include `Authorization: Bearer <token>` on protected endpoints (raffle entry create/delete, logout).

- **Reset & reseed**
    - To reset your local database and reseed demo data:
        ```bash
        php artisan migrate:fresh --seed
        ```

- **Queues & jobs**
    - Test environment uses `QUEUE_CONNECTION=sync`, so jobs like `SelectWinnersJob` and `ChargeWinnerJob` run inline during tests.

---

## License

This application is built on top of the Laravel framework, which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
