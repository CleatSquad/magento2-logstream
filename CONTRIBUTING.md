# Contributing to CleatSquad Magento 2 LogStream

First off, thank you for considering contributing to CleatSquad LogStream! 🎉

## 📋 How Can I Contribute?

### 🐛 Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates.

When you create a bug report, include as many details as possible:

- **Magento version** (e.g., 2.4.6)
- **PHP version** (e.g., 8.1, 8.2)
- **Steps to reproduce**
- **Expected behavior**
- **Actual behavior**
- **Screenshots** (if applicable)

### 💡 Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- Use a clear and descriptive title
- Provide a detailed description of the suggested enhancement
- Explain why this enhancement would be useful

### 🔧 Pull Requests

1. **Fork** the repository
2. **Clone** your fork locally
3. **Create a branch** for your changes:
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. **Make your changes** following our coding standards
5. **Test your changes** thoroughly
6. **Commit** with a clear message:
   ```bash
   git commit -m "feat: add new feature description"
   ```
7. **Push** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
8. **Open a Pull Request** against the `master` branch

## 🎨 Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use meaningful variable and function names
- Add PHPDoc comments to classes and methods
- Write unit tests for new features
- Ensure all existing tests pass

## 🧪 Running Tests

```bash
# Run unit tests
composer unit-test

# Run PHPStan static analysis
composer phpstan
```

## 📝 Commit Message Format

We follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

## 📜 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for your contribution! 🙏
