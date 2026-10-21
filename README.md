# 🚀 Laravel Clean Architecture API - Authentication Module

A professional, production-ready RESTful API built with Laravel 11, following **Clean Architecture**, **SOLID Principles**, and design patterns like **Factory** and **JWT Authentication**.

## 🛠️ Tech Stack
- **Framework:** Laravel 11 (PHP 8.2+)
- **Authentication:** JWT (JSON Web Tokens) via `php-open-source-saver/jwt-auth`
- **Architecture:** Clean Architecture / Domain-Driven structure
- **Design Patterns:** Factory Pattern, Dependency Injection
- **Security:** Throttle Middleware, Global Exception Handler (JSON standardized responses)
- **Testing:** PHPUnit / Feature Tests

---

## 📂 Project Architecture
The project is structured to maintain separation of concerns:

```

app/
├── Core/
│   └── Auth/
│       ├── Contracts/       # Interfaces (e.g., AuthServiceInterface)
│       ├── Services/        # Business Logic (e.g., JwtAuthService)
│       └── Factories/       # Dynamic Service Resolver (AuthServiceFactory)
├── Enums/
│   └── AuthProviderEnum.php # Supported authentication providers
├── Http/
│   ├── Controllers/Auth/    # HTTP controllers using HasMiddleware
│   ├── Requests/            # Form requests with validation
│   └── Resources/           # API resource transformers

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


4. **Configure Database:**
Update your database credentials in the `.env` file, then run migrations:
```bash
php artisan migrate

```


5. **Generate JWT Secret:**
```bash
php artisan jwt:secret

```


6. **Run the Application:**
```bash
php artisan serve

```



---

## 🔌 API Endpoints (Auth Module)

| Method | Endpoint | Description | Auth Required |
| --- | --- | --- | --- |
| `POST` | `/api/register` | Register a new user | No |
| `POST` | `/api/login` | Authenticate user & get JWT token | No |
| `GET` | `/api/me` | Get authenticated user profile | Yes (Bearer Token) |
| `POST` | `/api/logout` | Invalidate token / Logout | Yes (Bearer Token) |

---

## 🧪 Running Tests

To run the automated feature tests:

```bash
php artisan test

```

---

## 👨‍💻 Author

**Abdelhamed Fathy**
