# Contributing to AdminForge

Thank you for considering contributing to AdminForge! This document outlines the guidelines for contributing to this project.

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Setup](#development-setup)
4. [Coding Standards](#coding-standards)
5. [Pull Request Process](#pull-request-process)
6. [Reporting Bugs](#reporting-bugs)
7. [Feature Requests](#feature-requests)

---

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

### Our Standards

- Use welcoming and inclusive language
- Respect differing viewpoints and experiences
- Accept constructive criticism gracefully
- Focus on what's best for the community
- Show empathy towards other community members

---

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Create a new branch for your feature or bugfix
4. Make your changes
5. Test thoroughly
6. Submit a pull request

---

## Development Setup

### Requirements

- PHP 7.4 or higher
- Composer
- WordPress 5.0 or higher
- Node.js (for asset compilation - optional)

### Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/adminforge.git

# Navigate to the plugin directory
cd adminforge

# Install Composer dependencies
composer install

# If working with assets
npm install
```

### Running Tests

```bash
# Run PHP syntax check
php -l adminforge.php

# Run all PHP files syntax check
find . -name "*.php" -exec php -l {} \;
```

---

## Coding Standards

AdminForge follows strict coding standards to ensure consistency and quality.

### PHP Standards

- **PSR-4**: Autoloading standard
- **PSR-12**: Extended coding style guide
- **Strict Types**: Always use `declare(strict_types=1);`
- **Type Hints**: Use type hints for all parameters and return types
- **DocBlocks**: Comprehensive documentation for all classes and methods

#### Example

```php
<?php
/**
 * Example Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Example;

/**
 * ExampleClass description
 */
class ExampleClass
{
    /**
     * Method description
     *
     * @param string $param Parameter description
     * @return bool
     */
    public function exampleMethod(string $param): bool
    {
        // Implementation
        return true;
    }
}
```

### JavaScript Standards

- Use ES6+ syntax
- Use `const` and `let` (avoid `var`)
- Use jQuery for WordPress compatibility
- Comment complex logic
- Use strict mode: `'use strict';`

### CSS Standards

- Use CSS custom properties (variables)
- Follow BEM naming convention for custom classes
- Prefix all AdminForge classes with `adminforge-`
- Mobile-first responsive design

---

## Pull Request Process

### Before Submitting

1. **Test your changes** - Ensure all functionality works as expected
2. **Follow coding standards** - Your code should match the project style
3. **Update documentation** - Add/update relevant documentation
4. **Check for conflicts** - Rebase on latest main branch

### PR Guidelines

1. **Clear Title**: Use a descriptive title (e.g., "Add color picker field validation")
2. **Description**: Explain what changes you made and why
3. **Reference Issues**: Link related issues (e.g., "Fixes #123")
4. **Screenshots**: Include screenshots for UI changes
5. **Testing**: Describe how you tested the changes

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How was this tested?

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests added/updated
```

### Review Process

1. Maintainers will review your PR within 7 days
2. Address any requested changes
3. Once approved, a maintainer will merge your PR
4. Your contribution will be included in the next release

---

## Reporting Bugs

### Before Reporting

1. Check existing issues to avoid duplicates
2. Test with latest version of AdminForge
3. Disable other plugins to rule out conflicts
4. Check WordPress and PHP version compatibility

### Bug Report Template

```markdown
**Describe the bug**
Clear description of the bug

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen

**Screenshots**
If applicable, add screenshots

**Environment:**
- AdminForge Version: [e.g., 1.0.0]
- WordPress Version: [e.g., 6.0]
- PHP Version: [e.g., 8.0]
- Browser: [e.g., Chrome 100]

**Additional context**
Any other relevant information
```

---

## Feature Requests

We welcome feature requests! Please provide:

1. **Clear description** - What feature do you want?
2. **Use case** - Why is this feature needed?
3. **Examples** - Similar implementations or mockups
4. **Impact** - Who benefits from this feature?

### Feature Request Template

```markdown
**Feature Description**
Clear description of the proposed feature

**Problem it Solves**
What problem does this address?

**Proposed Solution**
How should this work?

**Alternatives Considered**
Other solutions you've considered

**Additional Context**
Mockups, examples, or references
```

---

## Development Guidelines

### Architecture Principles

1. **SOLID Principles**: Follow SOLID design principles
2. **DRY**: Don't Repeat Yourself
3. **KISS**: Keep It Simple, Stupid
4. **Separation of Concerns**: Each class has a single responsibility
5. **Dependency Injection**: Use the Container for dependencies

### Performance Considerations

1. Use MetaHelper for meta queries (O(1) access)
2. Minimize database queries
3. Lazy load assets when possible
4. Cache expensive operations
5. Use WordPress transients for temporary data

### Security Best Practices

1. Always sanitize input
2. Escape output
3. Verify nonces for form submissions
4. Check user capabilities
5. Use SecurityTrait methods
6. Never trust user input

### Code Review Checklist

- [ ] Code follows PSR-12 standards
- [ ] Proper type hints used
- [ ] DocBlocks present and accurate
- [ ] No security vulnerabilities
- [ ] Performance optimized
- [ ] Error handling implemented
- [ ] WordPress coding standards followed
- [ ] Backward compatibility maintained

---

## Questions?

If you have questions about contributing:

1. Check existing documentation
2. Search closed issues
3. Open a discussion on GitHub
4. Contact maintainers

---

## License

By contributing to AdminForge, you agree that your contributions will be licensed under the MIT License.

---

## Recognition

All contributors will be recognized in the project's contributors list. Thank you for helping make AdminForge better!
