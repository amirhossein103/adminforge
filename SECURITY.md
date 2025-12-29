# Security Policy

## Supported Versions

We actively support the following versions of AdminForge with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability within AdminForge, please follow these steps:

### How to Report

**DO NOT** create a public GitHub issue for security vulnerabilities.

Instead, please email security concerns to:
**amirhossein103@gmail.com** (replace with actual security contact)

Include the following information:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### What to Expect

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days with assessment
- **Fix Timeline**: Critical issues patched within 14 days

### Security Measures in AdminForge

AdminForge implements multiple security layers:

1. **ABSPATH Protection**: All source files check for direct access
2. **Nonce Verification**: All forms include WordPress nonces
3. **Input Sanitization**: 20+ sanitization methods via SecurityTrait
4. **Input Validation**: Type-safe validation for all field types
5. **Output Escaping**: All output uses `esc_html()`, `esc_attr()`, `esc_url()`
6. **SQL Protection**: Uses WordPress $wpdb prepared statements
7. **Capability Checks**: Admin features check `manage_options` capability

### Common WordPress Security Best Practices

When using AdminForge in your project:

- Always validate user capabilities before saving data
- Use nonce verification for all admin forms
- Sanitize all user input
- Escape all output
- Never trust user input, even from administrators
- Keep WordPress and PHP updated
- Use HTTPS in production

## Disclosure Policy

- We will acknowledge receipt of your vulnerability report
- We will keep you informed of the fix progress
- We will publicly credit researchers (unless you prefer anonymity)
- We will release a security advisory after the fix is deployed

## Security Updates

Security updates are released as patch versions (e.g., 1.0.1). We recommend:

- Enable automatic updates for AdminForge
- Monitor our GitHub releases
- Subscribe to security announcements

Thank you for helping keep AdminForge secure!
