# HestiaCP API Usage Examples

## New API Endpoints Added

Three new API endpoint permissions have been added to enable creating system users, web domains, and databases through the HestiaCP API.

### 1. User Management API (`user-management`)

**Permission**: `user-management`  
**Role**: `admin` (admin-only access)  
**Commands**: User creation, deletion, suspension, and management operations

#### Example: Create a new system user

```bash
curl -X POST https://your-server.com:8083/api/ \
  -d "access_key=YOUR_ACCESS_KEY" \
  -d "secret_key=YOUR_SECRET_KEY" \
  -d "cmd=v-add-user" \
  -d "arg1=newuser" \
  -d "arg2=SecurePassword123" \
  -d "arg3=user@example.com" \
  -d "arg4=default" \
  -d "arg5=John Doe"
```

### 2. Web Domains API (`web-domains`)

**Permission**: `web-domains`  
**Role**: `user` (available to all users)  
**Commands**: Web domain creation, deletion, suspension, and management operations

#### Example: Add a new web domain

```bash
curl -X POST https://your-server.com:8083/api/ \
  -d "access_key=YOUR_ACCESS_KEY" \
  -d "secret_key=YOUR_SECRET_KEY" \
  -d "cmd=v-add-web-domain" \
  -d "arg1=username" \
  -d "arg2=example.com" \
  -d "arg3=192.168.1.100" \
  -d "arg4=yes" \
  -d "arg5=www.example.com"
```

#### Example: List web domains

```bash
curl -X POST https://your-server.com:8083/api/ \
  -d "access_key=YOUR_ACCESS_KEY" \
  -d "secret_key=YOUR_SECRET_KEY" \
  -d "cmd=v-list-web-domains" \
  -d "arg1=username" \
  -d "arg2=json"
```

### 3. Database Management API (`database-management`)

**Permission**: `database-management`  
**Role**: `user` (available to all users)  
**Commands**: Database creation, deletion, suspension, and management operations

#### Example: Create a new database

```bash
curl -X POST https://your-server.com:8083/api/ \
  -d "access_key=YOUR_ACCESS_KEY" \
  -d "secret_key=YOUR_SECRET_KEY" \
  -d "cmd=v-add-database" \
  -d "arg1=username" \
  -d "arg2=mydatabase" \
  -d "arg3=dbuser" \
  -d "arg4=DatabasePassword123" \
  -d "arg5=mysql"
```

#### Example: List databases

```bash
curl -X POST https://your-server.com:8083/api/ \
  -d "access_key=YOUR_ACCESS_KEY" \
  -d "secret_key=YOUR_SECRET_KEY" \
  -d "cmd=v-list-databases" \
  -d "arg1=username" \
  -d "arg2=json"
```

## Generating Access Keys

### For User Management (Admin Only)

```bash
# Generate access key for user management
v-add-access-key admin user-management "User management key"
```

### For Web Domains and Database Management (All Users)

```bash
# Generate access key for web domains and database management
v-add-access-key username web-domains,database-management "Web and DB management key"
```

## JSON Request Format

You can also send requests using JSON format:

```bash
curl -X POST https://your-server.com:8083/api/ \
  -H "Content-Type: application/json" \
  -d '{
    "access_key": "YOUR_ACCESS_KEY",
    "secret_key": "YOUR_SECRET_KEY", 
    "cmd": "v-add-web-domain",
    "arg1": "username",
    "arg2": "example.com",
    "arg3": "192.168.1.100",
    "arg4": "yes",
    "arg5": "www.example.com"
  }'
```

## API Installation Notes

These API definition files are automatically installed when:

1. **New Installation**: The API definitions in `install/common/api/` are copied to `$HESTIA/data/api/` during installation
2. **System Upgrade**: The API definitions are updated during HestiaCP upgrades
3. **Manual Installation**: You can manually copy the files if needed:

```bash
# Copy API definitions to runtime location
cp -rf /usr/local/hestia/install/common/api/* /usr/local/hestia/data/api/
```

## Available Commands by API

### User Management Commands
- `v-add-user` - Create new system user
- `v-delete-user` - Delete system user
- `v-suspend-user` - Suspend user account
- `v-unsuspend-user` - Unsuspend user account
- `v-list-user` - Get user details
- `v-list-users` - List all users
- `v-change-user-password` - Change user password
- `v-change-user-package` - Change user package
- `v-change-user-shell` - Change user shell
- `v-change-user-contact` - Update user contact
- `v-change-user-language` - Change user language
- `v-change-user-name` - Update user display name

### Web Domain Commands
- `v-add-web-domain` - Create new web domain
- `v-delete-web-domain` - Delete web domain
- `v-suspend-web-domain` - Suspend web domain
- `v-unsuspend-web-domain` - Unsuspend web domain
- `v-list-web-domain` - Get domain details
- `v-list-web-domains` - List all domains
- `v-add-web-domain-alias` - Add domain alias
- `v-delete-web-domain-alias` - Remove domain alias
- `v-add-web-domain-ssl` - Add SSL certificate
- `v-delete-web-domain-ssl` - Remove SSL certificate
- `v-add-web-domain-ftp` - Create FTP account
- `v-delete-web-domain-ftp` - Delete FTP account
- Domain configuration commands (IP, templates, etc.)

### Database Management Commands
- `v-add-database` - Create new database
- `v-delete-database` - Delete database
- `v-suspend-database` - Suspend database
- `v-unsuspend-database` - Unsuspend database
- `v-list-database` - Get database details
- `v-list-databases` - List all databases
- `v-change-database-password` - Change database password
- `v-change-database-user` - Change database user
- `v-change-database-owner` - Change database owner
- `v-dump-database` - Export database backup
- `v-import-database` - Import database backup

## Security Considerations

1. **Access Key Permissions**: Only grant the minimum required permissions for each access key
2. **IP Restrictions**: Consider restricting API access to specific IP addresses
3. **Regular Key Rotation**: Periodically rotate access keys for security
4. **HTTPS Only**: Always use HTTPS for API communications
5. **Role-Based Access**: User management APIs are admin-only for security

## Error Handling

The API will return appropriate HTTP status codes and error messages:

- `200` - Success
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (invalid credentials)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `500` - Internal Server Error