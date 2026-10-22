# 🚀 Extendable Order & Payment Management API

A RESTful API for managing orders and payments, built with **Laravel 12** and **PHP 8.2+**. It follows a service-layer architecture and SOLID principles, and uses the **Strategy** and **Factory** patterns so new payment gateways can be added with minimal changes.

---

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Authentication:** JWT via `tymon/jwt-auth`
- **Architecture:** Service Layer with Contracts (interfaces)
- **Design Patterns:** Strategy, Factory, Dependency Injection
- **Database:** MySQL / PostgreSQL, with foreign key constraints and indexes
- **Security:** Throttle middleware, global exception handler (standardized JSON errors), policy-based authorization
- **Testing:** Pest on top of PHPUnit (Feature & Unit tests)
- **CI:** GitHub Actions

---

## 🔒 Security & Authorization (IDOR Protection)

The API uses `OrderPolicy` to make sure users can **only** view, update, or delete their own orders. Accessing or modifying another user's order returns `403 Forbidden`, which protects against **Insecure Direct Object Reference (IDOR)** attacks.

---

## 🚀 Continuous Integration

The project includes a GitHub Actions workflow in `.github/workflows/run-tests.yml`. On every push or pull request to `main`, it runs the automated test suite on an Ubuntu runner with **PHP 8.2**, so regressions are caught early.

You can check the latest run in the repository's **Actions** tab.

---

## 📂 Project Architecture

```
app/
├── Core/
│   ├── Auth/
│   │   ├── Contracts/         # Interfaces (e.g., AuthServiceInterface)
│   │   ├── Services/          # Business logic (e.g., JwtAuthService)
│   │   └── Factories/         # Service resolver (AuthServiceFactory)
│   └── Payment/
│       ├── Contracts/         # PaymentGatewayInterface
│       ├── Strategies/        # CreditCardPayment, PayPalPayment
│       └── Factories/         # Gateway resolver (PaymentGatewayFactory)
├── Enums/
│   ├── OrderStatusEnum.php    # pending, confirmed, cancelled
│   ├── PaymentStatusEnum.php  # pending, successful, failed
│   └── PaymentMethodEnum.php  # credit_card, paypal
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Register, Login, Me, Logout
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   ├── Requests/              # Form Requests (validation)
│   └── Resources/             # API Resources (Order, Payment, OrderItem)
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   └── Payment.php
└── Services/
    ├── OrderService.php       # Order business logic & DB transactions
    └── PaymentService.php     # Payment execution & validation rules
```

---

## ⚙️ Installation & Setup

### Requirements

- PHP 8.2 or higher
- Composer
- SQLite (default), or MySQL / PostgreSQL

### Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/3bdel7med/Order_payment_api_task.git
   cd Order_payment_api_task
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up the environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database:** the project defaults to **SQLite** (`DB_CONNECTION=sqlite`), so no extra setup is needed. To use MySQL or PostgreSQL instead, update the `DB_*` values in `.env`.

5. **Configure payment gateway credentials** in `.env`:
   ```env
   # Credit Card
   CREDIT_CARD_API_KEY=your_cc_api_key_here
   CREDIT_CARD_SECRET=your_cc_secret_here

   # PayPal
   PAYPAL_CLIENT_ID=your_paypal_client_id_here
   PAYPAL_SECRET=your_paypal_secret_here
   ```

6. **Generate the JWT secret:**
   ```bash
   php artisan jwt:secret
   ```

7. **Run migrations:**
   ```bash
   php artisan migrate
   ```

8. **Start the server:**
   ```bash
   php artisan serve
   ```

The API is now available at `http://127.0.0.1:8000/api`.

---

## 🔌 API Endpoints

All protected endpoints require the header:

```
Authorization: Bearer <your_jwt_token>
```

### 🔑 Authentication

| Method | Endpoint        | Description                           | Auth Required |
|--------|-----------------|---------------------------------------|---------------|
| `POST` | `/api/register` | Register a new user                   | No            |
| `POST` | `/api/login`    | Authenticate and receive a JWT token  | No            |
| `GET`  | `/api/me`       | Get the authenticated user's profile  | Yes           |
| `POST` | `/api/logout`   | Invalidate the current token          | Yes           |

### 📦 Orders

| Method   | Endpoint           | Description                                                          | Auth Required |
|----------|--------------------|----------------------------------------------------------------------|---------------|
| `GET`    | `/api/orders`      | List orders (filter with `?status=`, paginated)                      | Yes           |
| `POST`   | `/api/orders`      | Create an order with line items and an auto-calculated total         | Yes           |
| `GET`    | `/api/orders/{id}` | View an order with its items and payment status                      | Yes           |
| `PUT` / `PATCH` | `/api/orders/{id}` | Update the order status (e.g., confirm or cancel)             | Yes           |
| `DELETE` | `/api/orders/{id}` | Delete an order (blocked if it has associated payments)              | Yes           |

Allowed values for the `status` filter: `pending`, `confirmed`, `cancelled`.

### 💳 Payments

| Method | Endpoint                | Description                                                  | Auth Required |
|--------|-------------------------|--------------------------------------------------------------|---------------|
| `GET`  | `/api/payments`         | List payments (filter with `?order_id=`, paginated)          | Yes           |
| `POST` | `/api/payments/process` | Process a payment for a confirmed order via the chosen gateway | Yes         |

---

## 🛡️ Business Rules & Validation

- **Payment eligibility:** Payments can **only** be processed for orders in `confirmed` status. Orders in `pending` or `cancelled` status are rejected with `422 Unprocessable Entity`.
- **Order deletion guard:** An order **cannot** be deleted if it has associated payments.
- **Idempotency check:** An order with a successful payment cannot be paid again.
- **Ownership:** Users can only access their own orders (see IDOR protection above).
- **Validation:** All inputs are validated through Form Requests, and errors are returned as standardized JSON.

---

## 🧩 Payment Gateway Extensibility Guide

The payment system uses the **Strategy Pattern** with a **Factory**. Each gateway is a separate class implementing `PaymentGatewayInterface`, and the factory picks the right one based on the requested payment method. This lets you add gateways (e.g., Apple Pay, Fawry, Stripe) without touching `PaymentService` or `PaymentController` (**Open/Closed Principle**).

### How to Add a New Gateway (example: `ApplePay`)

1. **Add the identifier to the enum** in `app/Enums/PaymentMethodEnum.php`:
   ```php
   case APPLE_PAY = 'apple_pay';
   ```

2. **Add credentials** to `config/services.php` and `.env.example`:
   ```php
   'apple_pay' => [
       'merchant_id' => env('APPLE_PAY_MERCHANT_ID'),
       'certificate' => env('APPLE_PAY_CERTIFICATE'),
   ],
   ```

3. **Create the strategy class** `app/Core/Payment/Strategies/ApplePayPayment.php`:
   ```php
   <?php

   namespace App\Core\Payment\Strategies;

   use App\Core\Payment\Contracts\PaymentGatewayInterface;
   use App\Enums\PaymentStatusEnum;
   use App\Models\Order;
   use Exception;
   use Illuminate\Support\Str;

   class ApplePayPayment implements PaymentGatewayInterface
   {
       public function pay(Order $order, array $data): array
       {
           $merchantId = config('services.apple_pay.merchant_id');

           if (empty($merchantId)) {
               throw new Exception('Apple Pay merchant credentials are not configured.', 500);
           }

           // Call the real gateway here, or simulate the response.
           return [
               'payment_id' => 'APPLE-' . strtoupper(Str::random(10)),
               'amount'     => $order->total_amount,
               'status'     => PaymentStatusEnum::SUCCESSFUL,
           ];
       }
   }
   ```

4. **Register the strategy in the factory** `app/Core/Payment/Factories/PaymentGatewayFactory.php`:
   ```php
   public static function make(string $method): PaymentGatewayInterface
   {
       $paymentMethod = PaymentMethodEnum::tryFrom($method);

       return match ($paymentMethod) {
           PaymentMethodEnum::CREDIT_CARD => new CreditCardPayment(),
           PaymentMethodEnum::PAYPAL      => new PayPalPayment(),
           PaymentMethodEnum::APPLE_PAY   => new ApplePayPayment(),
           default => throw new InvalidArgumentException(
               "Payment method [{$method}] is not supported."
           ),
       };
   }
   ```

That's all. No changes are needed in `PaymentService` or `PaymentController`.

---

## 📬 API Documentation (Postman)

The Postman collection is located at `docs/order-payment-api.postman_collection.json`. Import it into Postman via **Import → File**.

It is organized into four folders (Authentication, Orders, Payments, Logout) and includes saved success and error responses for each request.

The collection uses these variables: `base_url`, `token`, `email`, `password`, `order_id`, and `order_to_delete_id`. Set `base_url` to your local server (e.g., `http://127.0.0.1:8000`), then run **Register** or **Login** first and copy the returned token into the `token` variable.

---

## 🧪 Running Tests

```bash
php artisan test
```

**Feature tests** cover authentication, order creation / listing / deletion, order ownership (IDOR protection), payment processing, the confirmed-order rule, duplicate-payment prevention, and the rule that orders with payments cannot be deleted.

**Unit tests** cover the payment gateway factory (resolving each gateway and rejecting unsupported methods).

---

## 📝 Notes & Assumptions

- Payment processing is **simulated**; no real gateway API calls are made.
- Gateway credentials are read from `.env` through `config/services.php`.
- JWT tokens are sent in the `Authorization: Bearer` header.

---

## 👨‍💻 Author

**Abdelhamed Fathy**
