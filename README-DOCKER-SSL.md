# Docker SSL Setup Guide

This guide explains how to set up SSL/HTTPS for the Pulse Master Portal using Let's Encrypt certificates.

## Prerequisites

1. A domain name pointing to your server's IP address
2. Ports 80 and 443 open in your firewall
3. Docker and Docker Compose installed

## Quick Start

### 1. Set Environment Variables

Add to your `.env` file:

```env
DOMAIN=yourdomain.com
CERTBOT_EMAIL=your-email@example.com
CERTBOT_STAGING=0  # Set to 1 for testing
```

### 2. Update the Init Script

Edit `scripts/init-letsencrypt.sh` and update:
- `domains` array with your domain(s)
- `email` with your email address

### 3. Run the SSL Setup

```bash
# Make script executable (Linux/Mac)
chmod +x scripts/init-letsencrypt.sh

# Run the initialization script
./scripts/init-letsencrypt.sh
```

### 4. Start the Services

```bash
docker-compose up -d
```

## Manual Certificate Setup

If you prefer to set up certificates manually:

```bash
# Start services without SSL first
docker-compose up -d app

# Request certificate
docker-compose run --rm certbot certonly --webroot \
  -w /var/www/certbot \
  -d yourdomain.com \
  -d www.yourdomain.com \
  --email your-email@example.com \
  --agree-tos

# Start nginx with SSL
docker-compose up -d
```

## Certificate Renewal

Certificates are automatically renewed by the `certbot` service running in the background. It checks for renewal every 12 hours.

To manually renew:

```bash
docker-compose run --rm certbot renew
docker-compose exec nginx nginx -s reload
```

## Testing (Staging Mode)

For testing without hitting Let's Encrypt rate limits:

1. Set `CERTBOT_STAGING=1` in your `.env` file
2. Update `staging=1` in `scripts/init-letsencrypt.sh`
3. Run the setup script

## Troubleshooting

### Certificate Not Found

If you see SSL errors, ensure:
- Domain DNS points to your server
- Ports 80 and 443 are accessible
- Certificates are in `/etc/letsencrypt/live/yourdomain.com/`

### Check Certificate Status

```bash
docker-compose exec certbot certbot certificates
```

### View Nginx Logs

```bash
docker-compose logs nginx
```

### Test Nginx Configuration

```bash
docker-compose exec nginx nginx -t
```

## Without SSL (Development)

For local development without SSL, you can access the app directly on port 8082:

```bash
# Comment out nginx and certbot services in docker-compose.yml
# Or use a separate compose file without SSL
```

Access at: `http://localhost:8082`
