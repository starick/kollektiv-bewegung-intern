# Kollektiv Bewegung Intern

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

Welcome to the internal repository for **Kollektiv:Bewegung**. This project is focused on creating and managing timetables for our organization, providing a reliable and scalable solution built with [Laravel](https://laravel.com). Additional features may be added in the future to support our evolving needs.

## Features

- Timetable creation and management
- TBA

## Getting Started

1. **Clone the repository:**
    ```bash
    git clone https://github.com/starick/kollektiv-bewegung-intern.git
    ```
2. **Install dependencies:**
    ```bash
    composer install
    npm install
    ```
3. **Configure environment:**
    - Copy `.env.example` to `.env` and update settings.
4. **Run migrations:**
    ```bash
    php artisan migrate --seed
    php artisan generate:key
    ```
5. **Start the development server:**
    ```bash
    php artisan serve
    ```
6. **Start vite**
    ```bash
    npm run dev
    ```

## Best Practices Guide

To maintain a clean, scalable, and collaborative codebase, please follow these best practices. Guidelines are separated for **Frontend (Vue)** and **Backend (Laravel)** development:

### General

- **Commits:** Write clear, concise commit messages. Prefer [Conventional Commits](https://www.conventionalcommits.org/).
- **Documentation:** Document props, events, and component usage.
- **Security:** Never commit sensitive data (API keys, secrets).
- **Dependencies:** Regularly update dependencies and remove unused packages.
- **Naming:** Pick descriptive and consistent names for variables, classes and methods. Avoid abbreviations (except for very well-established ones)

### Frontend (Vue)

- **File Naming:** Use lowercase and hyphens for files (e.g., `user-profile.vue`). Use PascalCase for component names inside files.
- **Directory Structure:** Organize components by feature or domain. Place shared components in a `Components/` directory.
- **Code Style:** Use Prettier for formatting. Follow the official [Vue Style Guide](https://vuejs.org/style-guide/).
- **Typescript**:
    - **Type Safety:** Always use explicit types and interfaces. Avoid using `any`; prefer `unknown` or proper type definitions.
    - **Type Inference:** Leverage TypeScript's type inference, but annotate complex structures.
    - **Avoid `any`:** If unsure, use `unknown` and narrow types with type guards.
    - **Interfaces vs Types:** Use `interface` for object shapes and `type` for unions or primitives.
    - **Function Signatures:** Always type function parameters and return values.
    - **Readonly:** Use `readonly` for immutable properties and arrays.
    - **Utility Types:** Use built-in utility types (`Partial`, `Pick`, `Record`, etc.) for concise type manipulation.


### Backend (Laravel)

- **File Naming:** Use PascalCase for classes (e.g., `EventService.php`), snake_case for config and migration files.
- **Directory Structure:** Organize code by domain (e.g., `app/Services`, `app/Models`). Keep related files together.
- **Code Style:** Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) for PHP. Use Laravel's built-in code style tools.
- **Commits:** Write clear, concise commit messages. Prefer [Conventional Commits](https://www.conventionalcommits.org/).
- **Testing:** Write and update feature and unit tests. Run all tests before pushing.
- **Documentation:** Update code comments and API documentation as needed.
- **Security:** Never commit sensitive data (passwords, API keys, etc.).
- **Dependencies:** Regularly update Composer packages and remove unused dependencies.

Consistent practices help keep our project maintainable and collaborative!

We welcome contributions! Please read our [contribution guidelines](CONTRIBUTING.md) before submitting a pull request.

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

---
<p align="center">
    Made with ❤️ by the Kollektiv:Bewegung team.
</p>