# SSL Certificates from Cloudflare

Place your Cloudflare SSL certificates in this folder.

## Required File Names

The Nginx configuration expects these exact file names:
- **Certificate file**: `certificate.crt`
- **Private key file**: `private.key`

## Setup Instructions

### Step 1: Copy Your Cloudflare Certificates

Copy your Cloudflare certificate files to this `ssl` folder.

### Step 2: Rename Files (if needed)

If your Cloudflare files have different names, rename them to match:

**Windows (PowerShell):**
```powershell
# If your certificate is named differently (e.g., origin.crt, cert.pem, etc.)
Copy-Item "your-certificate-file.crt" -Destination "ssl\certificate.crt"
Copy-Item "your-private-key-file.key" -Destination "ssl\private.key"
```

**Linux/Mac:**
```bash
# If your certificate is named differently
cp your-certificate-file.crt ssl/certificate.crt
cp your-private-key-file.key ssl/private.key
```

### Step 3: Verify Files

Make sure you have both files in the `ssl` folder:
- `ssl/certificate.crt`
- `ssl/private.key`

### Step 4: Set File Permissions (Linux/Mac)

```bash
chmod 644 ssl/certificate.crt
chmod 600 ssl/private.key
```

## Common Cloudflare Certificate File Names

Cloudflare typically provides certificates with names like:
- `origin.crt`, `certificate.crt`, `fullchain.crt`, or `cert.pem` → rename to `certificate.crt`
- `origin.key`, `private.key`, or `key.pem` → rename to `private.key`

## Testing

After placing the certificates, start the Docker containers:
```bash
docker-compose up -d
```

Check Nginx logs to verify SSL is working:
```bash
docker-compose logs nginx
```
