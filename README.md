# Secure Backup System for WordPress

A secure database backup solution with multiple layers of protection and access controls.

## Features

- 🔒 **Secure Access Controls**
  - WordPress authentication required
  - Nonce-protected URLs
  - Secret key authorization
  - User capability checks
- 🛡️ **Security Measures**
  - .htaccess protection for backups
  - File permission hardening
  - Path traversal prevention
  - SQL injection protection
- 💾 **Backup Features**
  - GZIP compressed backups
  - Chunked data processing for large tables
  - Automatic directory creation
  - Downloadable backup files
  - **New:** Top bar button for quick access
  - **New:** Automatic redirection to dashboard after backup

## Installation

1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Activate the plugin through the **Plugins** menu

## Configuration

1. Go to **Settings > General**
2. Find the **Secure Backup Settings** section

## Usage

1. **Access Backup URL**
   - Use the generated URL while logged in as admin
   - Backup will automatically download as `.sql.gz` file

2. **Backup Storage**
   - Backups are stored in `/wp-content/backups/`
   - Protected by `.htaccess` rules
   - Files automatically deleted after 30 days

## Security Recommendations

1. 🔑 **Secret Key Management**
   - Regenerate secret key periodically
   - Never share backup URLs
   - Use HTTPS exclusively

2. 🛡️ **Server Configuration**
   - Implement IP whitelisting
   - Set up rate limiting
   - Monitor backup directory access

3. 🔄 **Maintenance**
   - Test backups regularly
   - Keep plugin updated
   - Review access logs

## Troubleshooting

**Common Issues:**
- *403 Forbidden*: Verify user permissions and secret key
- *Directory creation failed*: Check wp-content permissions (0750+)
- *Empty backups*: Ensure database user has SELECT privileges
- *Timeout errors*: Increase PHP max_execution_time

## Disclaimer

Always test backups in a staging environment before relying on them for production use. The developers are not responsible for any data loss.


## Changelog

**1.0.0** (2025-02-18)
- Initial release with core backup functionality
- Security layers implementation
- WordPress settings integration