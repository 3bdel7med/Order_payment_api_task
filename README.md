# 🚀 Extendable Order & Payment Management API

A professional, production-ready RESTful API built with Laravel 11, following **Clean Architecture**, **SOLID Principles**, and design patterns like **Strategy Pattern**, **Factory Pattern**, and **JWT Authentication**.

## 🛠️ Tech Stack
- **Framework:** Laravel 12 (PHP 8.2+)
- **Authentication:** JWT (JSON Web Tokens) via `php-open-source-saver/jwt-auth`
- **Architecture:** Clean Architecture / Service Layer / Domain-Driven structure
- **Design Patterns:** Strategy Pattern, Factory Pattern, Dependency Injection
- **Database:** MySQL / PostgreSQL with explicit FK constraints & indexes
- **Security:** Throttle Middleware, Global Exception Handler (JSON standardized responses)
- **Testing:** PHPUnit / Pest PHP Feature & Unit Tests

---

## 📂 Project Architecture
The project is structured to maintain strict separation of concerns and open-closed extensibility:

```
app/
├── Core/
│   ├── Auth/
│   │   ├── Contracts/         # Interfaces (e.g., AuthServiceInterface)
│   │   ├── Services/          # Business Logic (e.g., JwtAuthService)
│   │   └── Factories/         # Dynamic Service Resolver (AuthServiceFactory)
│   └── Payment/
│       ├── Contracts/         # PaymentGatewayInterface Contract
│       ├── Strategies/        # CreditCardPayment, PayPalPayment Strategies
│       └── Factories/         # Dynamic Gateway Resolver (PaymentGatewayFactory)
├── Enums/
│   ├── OrderStatusEnum.php    # Order Statuses (pending, confirmed, cancelled)
│   ├── PaymentStatusEnum.php  # Payment Statuses (pending, successful, failed)
│   └── PaymentMethodEnum.php  # Payment Gateway Identifiers (credit_card, paypal)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Auth Endpoints (Register, Login, Me, Logout)
│   │   ├── OrderController.php# RESTful Order CRUD
│   │   └── PaymentController.php # Payment Processing Endpoints
│   ├── Requests/              # Form Validation Requests (Order & Payment)
│   └── Resources/             # API Resource Transformers (Order, Payment, OrderItem)
├── Models/
│   ├── Order.php              # Scopes, Casts & Business Helpers
│   ├── OrderItem.php          # Order Line Items
│   └── Payment.php            # Payment Transaction Ledger
└── Services/
    ├── OrderService.php       # DB Transactions & Order Business Logic
    └── PaymentService.php     # Payment Execution & Validation Rules
```

---

## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd <project-folder>
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Payment Gateway Credentials in `.env`:**
   Add your gateway configurations and credentials to your `.env` file:
   ```env
   # Credit Card Configuration
   CREDIT_CARD_API_KEY=your_cc_api_key_here
   CREDIT_CARD_SECRET=your_cc_secret_here

   # PayPal Configuration
   PAYPAL_CLIENT_ID=your_paypal_client_id_here
   PAYPAL_SECRET=your_paypal_secret_here
   ```

5. **Configure Database & Run Migrations:**
   Update your database credentials in `.env`, then execute migrations:
   ```bash
   php artisan migrate
   ```

6. **Generate JWT Secret:**
   ```bash
   php artisan jwt:secret
   ```

7. **Run the Application:**
   ```bash
   php artisan serve
   ```

---

## 🔌 API Endpoints

### 🔑 Authentication Module
| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| `POST` | `/api/register` | Register a new user | No |
| `POST` | `/api/login` | Authenticate user & receive JWT token | No |
| `GET` | `/api/me` | Retrieve authenticated user profile | Yes (Bearer Token) |
| `POST` | `/api/logout` | Invalidate token / Logout | Yes (Bearer Token) |

### 📦 Order Management Module
| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| `GET` | `/api/orders` | List orders (supports `?status=pending|confirmed|cancelled` & pagination) | Yes (Bearer Token) |
| `POST` | `/api/orders` | Create order with line items & auto-calculated total | Yes (Bearer Token) |
| `GET` | `/api/orders/{id}` | View specific order details with items & payment status | Yes (Bearer Token) |
| `PUT` | `/api/orders/{id}` | Update order details / status | Yes (Bearer Token) |
| `DELETE` | `/api/orders/{id}` | Delete order (Blocked if order has associated payments) | Yes (Bearer Token) |

### 💳 Payment Management Module
| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| `GET` | `/api/payments` | List payments (supports filtering by `?order_id=X` & pagination) | Yes (Bearer Token) |
| `POST` | `/api/payments/process` | Process payment for a confirmed order via selected gateway | Yes (Bearer Token) |

---

## 🛡️ Business Rules & Validation Constraints
- **Payment Eligibility:** Payments can **ONLY** be processed for orders in `confirmed` status. Unconfirmed (`pending` or `cancelled`) orders reject payments with a `422 Unprocessable Entity` or `400 Bad Request`.
- **Order Deletion Guard:** An order **CANNOT** be deleted if it has associated payment records, maintaining strict database integrity and financial auditing.
- **Idempotency Check:** Successful payments block duplicate re-processing attempts for the same order.

---

## 🧩 Payment Gateway Extensibility Guide

The payment subsystem is built using the **Strategy Pattern** paired with a **Factory Pattern**, allowing new payment gateways (e.g., Apple Pay, Fawry, Stripe) to be added seamlessly without modifying existing business logic (**Open/Closed Principle**).

### How to Add a New Payment Gateway (e.g., `ApplePay`)

1. **Add Gateway Identifier to Enum:**
   Add the new method to `app/Enums/PaymentMethodEnum.php`:
   ```php
   case APPLE_PAY = 'apple_pay';
   ```

2. **Add Credentials Configuration:**
   Add gateway keys in `config/services.php` and `.env.example`:
   ```php
   'apple_pay' => [
       'merchant_id' => env('APPLE_PAY_MERCHANT_ID'),
       'certificate' => env('APPLE_PAY_CERTIFICATE'),
   ],
   ```

3. **Create Payment Strategy Class:**
   Create `app/Core/Payment/Strategies/ApplePayPayment.php` implementing `PaymentGatewayInterface`:
   ```php
   namespace App\Core\Payment\Strategies;

   use App\Core\Payment\Contracts\PaymentGatewayInterface;
   use App\Enums\PaymentStatusEnum;
   use App\Models\Order;
   use Illuminate\Support\Str;
   use Exception;

   class ApplePayPayment implements PaymentGatewayInterface
   {
       public function pay(Order $order, array $data): array
       {
           $merchantId = config('services.apple_pay.merchant_id');

           if (empty($merchantId)) {
               throw new Exception("Apple Pay merchant credentials not configured.", 500);
           }

           // Process or simulate gateway API request
           return [
               'payment_id' => 'APPLE-' . strtoupper(Str::random(10)),
               'amount'     => $order->total_amount,
               'status'     => PaymentStatusEnum::SUCCESSFUL,
           ];
       }
   }
   ```

4. **Register Strategy in Factory:**
   Update `app/Core/Payment/Factories/PaymentGatewayFactory.php`:
   ```php
   return match ($paymentMethod) {
       PaymentMethodEnum::CREDIT_CARD => new CreditCardPayment(),
       PaymentMethodEnum::PAYPAL      => new PayPalPayment(),
       PaymentMethodEnum::APPLE_PAY   => new ApplePayPayment(),
       default => throw new InvalidArgumentException("Unsupported payment method: {$method}"),
   };
   ```

No changes are needed in `PaymentService` or `PaymentController`.

---

## 🧪 Running Tests

To run the automated test suite (Unit & Feature Tests):

```bash
php artisan test
```

---

## 👨‍💻 Author

**Abdelhamed Fathy**
