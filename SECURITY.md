# Security Policy

## Supported versions

Only the latest release on the `main` branch receives security fixes.

## Reporting a vulnerability

**Please don't open a public GitHub issue for security problems.**

Report vulnerabilities privately in one of these ways:

- Use GitHub's **Report a vulnerability** button on this repository's Security tab, or
- Email **admin@eloom.com.au** with "SECURITY" in the subject line.

Please include:

- a description of the issue and its impact
- steps to reproduce it, or a proof of concept
- the affected version or commit

We aim to acknowledge reports within 5 business days and to agree on a disclosure timeline with you. Please give us reasonable time to release a fix before you disclose anything publicly.

## Deployment hardening

If you run this application, we recommend that you:

- set `APP_ENV=production` and `APP_DEBUG=false`
- make sure your web server never executes scripts under `public/images/`, where uploads are stored (see below)
- keep `.env`, `storage/oauth-*.key` and database backups outside the web root
- run `composer audit` regularly and apply dependency updates
- create the first super admin as soon as you deploy: until one user exists, `POST /admin/register` is open so that the setup form works
- grant the `database_backup` permission to as few roles as possible — it allows a full database download and a restore that replaces every table

### How access control works

Routes carry no authentication middleware. Each controller declares its guard in its
constructor (`$this->middleware('auth:user')`, or `auth:student`, `auth:trainer`,
`auth:agent`, `auth:agent_branch_user`), and individual actions call `checkRole($key, $action)`.

If you fork this project and add a controller, it is reachable by anyone until you add that
constructor. Review your own controllers for it, and see [CONTRIBUTING.md](CONTRIBUTING.md).

### Uploaded files

Uploads are checked against an allow-list of file types before they're saved (`uploadFile()` in `app/helpers.php`). As a second layer, stop the web server from running scripts in the upload folder:

- **Apache:** `public/images/.htaccess` does this already, as long as `AllowOverride` permits `FileInfo` (Laravel's own `public/.htaccess` needs that too).
- **nginx:** add this before your main `location ~ \.php$` block:

```nginx
location ^~ /images/ {
    location ~* \.(php[0-9]?|phtml|phar|pht)$ { deny all; }
}
```
