# OAuth Helper

A lightweight PHP tool for generating and managing OAuth 2.0 access tokens without hardcoding credentials. Designed for developers who need to work with OAuth-protected APIs during development, testing, or in production environments where manual token management is acceptable.

## The Problem This Solves

When working with OAuth 2.0 APIs like Google Business Profile, Microsoft Graph, or Facebook Graph API, developers face a common challenge: OAuth tokens expire (typically after 1 hour), but the refresh tokens used to generate new access tokens are long-lived. Managing this token lifecycle typically requires:

- Building custom token management infrastructure
- Storing OAuth credentials securely in your application
- Implementing automatic token refresh logic
- Handling token expiration edge cases

For API testing, prototyping, or small-scale integrations, this infrastructure is overkill. You just need a way to get a valid access token to make API requests.

## What This Tool Does

OAuth Helper provides a simple web interface to:

1. **Initial Setup**: Enter your OAuth client credentials (Client ID, Client Secret, Redirect URI, and API scopes) through a web form
2. **Authorization**: Generate an authorization URL and authenticate with the OAuth provider (e.g., Google)
3. **Token Generation**: Exchange the authorization code for a refresh token (long-lived) and access token (short-lived)
4. **Token Renewal**: Use your saved refresh token to generate fresh access tokens whenever needed

All credentials are stored in PHP sessions - nothing is persisted to disk or databases. When you close your browser, the session ends and credentials are cleared.

## Use Cases

### API Research and Testing
When exploring a new API like Google Business Profile API, you need valid access tokens to test endpoints. Instead of building a full OAuth flow into your test scripts, use OAuth Helper to:
- Generate a refresh token once during initial authorization
- Generate fresh access tokens on demand for API testing in tools like Postman, Insomnia, or cURL
- Quickly iterate on API requests without worrying about token expiration

### Development and Prototyping
During early development phases:
- Test OAuth-protected APIs before building production token management
- Share access tokens with team members for collaborative API exploration
- Validate API responses and data structures before committing to an implementation approach

### Small-Scale Integrations
For lightweight integrations where full OAuth infrastructure isn't justified:
- Generate tokens manually when needed (e.g., weekly data exports)
- Use in combination with cron jobs or scheduled scripts
- Suitable for personal projects or internal tools with limited scope

### Educational Purposes
- Understand the OAuth 2.0 flow by seeing each step visually
- Learn how authorization codes, refresh tokens, and access tokens work
- Experiment with different OAuth scopes and permissions

## How It Works

### The OAuth 2.0 Flow

OAuth Helper implements the standard OAuth 2.0 authorization code flow:

1. **User provides credentials**: You enter your OAuth client ID, client secret, redirect URI, and the API scope you want to access
2. **Authorization URL generation**: The tool constructs a proper OAuth authorization URL with all required parameters
3. **User authorization**: You visit the authorization URL, sign in with your account (e.g., Google), and grant permissions
4. **Code exchange**: Google redirects back to the callback URL with an authorization code, which the tool exchanges for tokens
5. **Token storage**: You receive both a refresh token (save this!) and an access token (valid ~1 hour)
6. **Token renewal**: Later, paste your refresh token to generate new access tokens without re-authorizing

### Security Model

- **Session-based**: OAuth credentials are stored in PHP sessions, not written to files or databases
- **No persistence**: Credentials disappear when you close your browser
- **User-controlled**: You provide your own OAuth client credentials - the tool never sees or stores them permanently
- **HTTPS required**: All OAuth flows must use HTTPS to prevent credential interception

## Installation

### Requirements

- PHP 7.4 or higher
- Web server with HTTPS enabled (required for OAuth)
- PHP curl extension enabled
- PHP session support (enabled by default)

### Setup Steps

1. **Upload files to your web server**
```bash
   # Via FTP, or if you have SSH access:
   git clone https://github.com/yourusername/oauth-helper.git
   cd oauth-helper
```

2. **Ensure HTTPS is configured**
   OAuth 2.0 requires HTTPS for security. Obtain an SSL certificate (Let's Encrypt is free) and configure your web server.

3. **Configure OAuth client in your API provider**
   
   For Google APIs:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a project (or select existing)
   - Enable the API you want to use (e.g., "Google Business Profile API")
   - Go to "APIs & Services" → "Credentials"
   - Create "OAuth client ID" → Choose "Web application"
   - Add authorized redirect URI: `https://your-domain.com/callback.php`
   - Save your Client ID and Client Secret

4. **Access the tool**
   Navigate to `https://your-domain.com/setup.php` in your browser

## Usage Guide

### First-Time Setup (Getting Your Refresh Token)

1. **Visit setup.php**
   Navigate to the setup page on your deployed instance

2. **Enter OAuth credentials**
   - **Client ID**: From your OAuth client configuration (e.g., Google Cloud Console)
   - **Client Secret**: The secret key associated with your client ID
   - **Redirect URI**: Must match exactly what's configured in your OAuth client (e.g., `https://your-domain.com/callback.php`)
   - **Scope**: The API permissions you need (e.g., `https://www.googleapis.com/auth/business.manage` for Google Business Profile)

3. **Click "Save & Generate Authorization URL"**
   The tool saves your credentials in the session and generates an authorization URL

4. **Authorize with the provider**
   Click the authorization button. You'll be redirected to the OAuth provider (e.g., Google) where you:
   - Sign in with your account
   - Review the permissions being requested
   - Grant access to your application

5. **Receive your tokens**
   After authorization, you're redirected back to callback.php which displays:
   - **Refresh Token**: Save this somewhere secure (password manager, encrypted file, etc.). You'll need it to generate new access tokens.
   - **Access Token**: Valid for ~1 hour. Use this immediately in API requests.

6. **Save your refresh token**
   ⚠️ **Critical**: Store your refresh token securely. You won't see it again unless you re-authorize (which may require user interaction).

### Generating New Access Tokens

When your access token expires (after ~1 hour), generate a new one:

1. **Visit callback.php**
   Navigate directly to the callback page (it serves as both callback handler and token generator)

2. **Paste your refresh token**
   Enter the refresh token you saved during initial setup

3. **Click "Generate Access Token"**
   The tool exchanges your refresh token for a fresh access token

4. **Copy the new access token**
   Use this token in your API requests for the next hour

### Using Access Tokens in API Requests

Include the access token in the `Authorization` header:

**cURL example:**
```bash
curl -H "Authorization: Bearer ya29.a0AfB_byD..." \
  "https://mybusinessbusinessinformation.googleapis.com/v1/accounts/ACCOUNT_ID/locations/LOCATION_ID/reviews"
```

**Postman/Insomnia:**
- Header name: `Authorization`
- Header value: `Bearer YOUR_ACCESS_TOKEN`

**JavaScript fetch:**
```javascript
fetch('https://api.example.com/endpoint', {
  headers: {
    'Authorization': 'Bearer ya29.a0AfB_byD...'
  }
})
```

## Supported OAuth Providers

This tool works with any OAuth 2.0 provider that follows the standard authorization code flow:

- **Google APIs** (Business Profile, Gmail, Drive, Calendar, etc.)
- **Microsoft Graph API** (Office 365, Azure AD, etc.)
- **Facebook Graph API**
- **GitHub API**
- **LinkedIn API**
- **Spotify API**
- Any other OAuth 2.0 compliant service

### Provider-Specific Notes

**Google APIs:**
- Token endpoint: `https://oauth2.googleapis.com/token` (default)
- Scopes are space-separated URLs (e.g., `https://www.googleapis.com/auth/business.manage`)
- Access tokens valid for 3600 seconds (1 hour)

**Microsoft Graph:**
- Token endpoint: `https://login.microsoftonline.com/common/oauth2/v2.0/token`
- Scopes use space-separated identifiers (e.g., `User.Read Mail.Read`)

**Facebook:**
- Token endpoint: `https://graph.facebook.com/v18.0/oauth/access_token`
- Different parameter names may require code modifications

## Security Considerations

### What This Tool Does NOT Do

- **Does not store credentials permanently**: Everything is session-based
- **Does not transmit credentials to third parties**: All OAuth exchanges happen directly between your browser and the OAuth provider
- **Does not log tokens**: No logging of sensitive credentials or tokens
- **Does not expose credentials in URLs**: OAuth secrets are sent via POST, not GET parameters

### Best Practices

1. **Deploy on HTTPS only**: OAuth requires secure connections
2. **Restrict access**: Use `.htaccess` password protection or firewall rules to limit who can access the tool
3. **Use for development/testing**: Not recommended for production token management at scale
4. **Rotate credentials regularly**: If you suspect credential compromise, revoke and regenerate OAuth clients
5. **Don't commit tokens to git**: Never check refresh tokens or access tokens into version control

### Threat Model

**Protected against:**
- Credential theft via network interception (HTTPS)
- Accidental credential persistence (session-only storage)
- Unauthorized access to your OAuth applications (requires your client credentials)

**Not protected against:**
- Server compromise (if attacker has shell access, they can read PHP sessions)
- XSS attacks (no JavaScript, but keep server patched)
- Physical access to your computer while session is active

**Recommended for:** Internal tools, development environments, testing scenarios
**Not recommended for:** Public-facing applications, high-security production environments, applications requiring audit trails

## Files

- `index.php` - Landing page with tool description
- `setup.php` - Configuration form for entering OAuth credentials
- `callback.php` - Handles OAuth callbacks and token generation/renewal
- `policy.php` - Privacy policy page
- `tos.php` - Terms of service page

## Configuration

No configuration files needed. All settings are provided through the web interface.

If deploying for a specific OAuth provider, you can pre-fill default values by modifying the form defaults in `setup.php`:
```php
<input type="text" name="scope" value="https://www.googleapis.com/auth/business.manage" required>
```

## Troubleshooting

### "redirect_uri_mismatch" error
**Problem**: The redirect URI doesn't match what's configured in your OAuth client.
**Solution**: Ensure the redirect URI in setup.php exactly matches your OAuth client configuration (including https://, trailing slashes, etc.)

### "invalid_grant" error when refreshing tokens
**Problem**: Refresh token is invalid or expired.
**Solution**: Re-authorize to get a new refresh token. Some providers expire refresh tokens after 6 months of inactivity.

### Session lost / "No credentials found"
**Problem**: PHP session expired or browser cleared cookies.
**Solution**: Go back to setup.php and re-enter credentials. Your refresh token is still valid.

### Token doesn't work in API requests
**Problem**: Access token expired or incorrect format.
**Solution**: Generate a fresh token. Ensure you're using `Bearer TOKEN` format in Authorization header (note the space after "Bearer").

## Example: Google Business Profile API

Here's a complete workflow for accessing Google Business Profile reviews:

1. **Create OAuth Client**
   - Go to Google Cloud Console
   - Enable "Google My Business API" and "Business Profile Performance API"
   - Create OAuth 2.0 credentials
   - Add redirect URI: `https://oauth-helper.yourdomain.com/callback.php`

2. **Get Refresh Token**
   - Visit `setup.php`
   - Enter Client ID and Client Secret
   - Redirect URI: `https://oauth-helper.yourdomain.com/callback.php`
   - Scope: `https://www.googleapis.com/auth/business.manage`
   - Authorize and save refresh token

3. **Get Access Token**
   - Visit `callback.php`
   - Paste refresh token
   - Copy new access token

4. **Fetch Reviews**
```bash
   curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
     "https://mybusinessbusinessinformation.googleapis.com/v1/accounts/YOUR_ACCOUNT_ID/locations/YOUR_LOCATION_ID/reviews"
```

5. **Repeat step 3 when token expires** (every hour)

## Development

Want to extend this tool? Some ideas:

- Add token expiration countdown timers
- Store refresh tokens encrypted in database (defeats the "no persistence" design)
- Support for OAuth 1.0a (Twitter, etc.)
- Built-in API testing interface
- Support for PKCE (Proof Key for Code Exchange) flow
- Integration with browser extensions for auto-token injection

## License

MIT License - feel free to modify and use as needed.

## Contributing

Contributions welcome! Please open issues for bugs or feature requests.

## Disclaimer

This tool is provided as-is without warranties. It's designed for development and testing purposes. For production applications with many users, implement proper token management infrastructure with secure credential storage, automatic refresh logic, and audit logging.

## Author

Created to solve the OAuth token management pain during API development and testing.

---

**Privacy Note**: This tool does not collect, store, or transmit any personal information or API credentials to external servers. All OAuth operations occur directly between your browser and the OAuth provider (e.g., Google). Credentials are stored temporarily in PHP sessions and cleared when you close your browser.
