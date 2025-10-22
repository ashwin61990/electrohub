# AWS Deployment Guide for ElectroHub

## Issues Fixed

### 1. Database Configuration
- ✅ Updated `config/Database.php` to support AWS RDS
- ✅ Added environment variable detection for AWS
- ✅ Improved error handling and logging

### 2. File Upload Issues
- ✅ Fixed file upload paths to use absolute paths
- ✅ Added proper permission checks
- ✅ Enhanced error handling for file operations

### 3. Error Handling
- ✅ Added comprehensive error logging
- ✅ Improved debugging capabilities
- ✅ Added validation for all form inputs

## AWS Configuration Steps

### 1. Database Setup (RDS)
Set these environment variables in your AWS Elastic Beanstalk or EC2:
```
RDS_HOSTNAME=your-rds-endpoint.amazonaws.com
RDS_DB_NAME=electronics_store
RDS_USERNAME=your_username
RDS_PASSWORD=your_password
```

### 2. File Permissions
Ensure the following directories have write permissions:
- `/uploads/products/` (755 or 775)
- `/tmp/` for error logs

### 3. PHP Configuration
Ensure these PHP settings in your AWS environment:
- `upload_max_filesize = 10M`
- `post_max_size = 12M`
- `memory_limit = 256M`
- `max_execution_time = 300`

## Troubleshooting Steps

### 1. Run Debug Script
Visit: `https://your-domain.com/debug.php`
This will check:
- Database connectivity
- File permissions
- Required PHP extensions
- Environment variables

### 2. Check Error Logs
- AWS CloudWatch logs
- PHP error logs in `/tmp/php_errors.log`
- Application logs

### 3. Common Issues & Solutions

#### HTTP 500 Error
1. **Database Connection**: Check RDS credentials and security groups
2. **File Permissions**: Ensure upload directories are writable
3. **PHP Errors**: Check error logs for specific issues
4. **Missing Extensions**: Ensure PDO, PDO_MySQL, GD are installed

#### File Upload Issues
1. Check directory permissions (755/775)
2. Verify upload limits in PHP configuration
3. Ensure sufficient disk space

#### Session Issues
1. Check session directory permissions
2. Verify session configuration in PHP

## Security Considerations

1. **Remove debug.php** from production
2. **Set proper file permissions** (not 777)
3. **Use HTTPS** for all admin operations
4. **Regular database backups**
5. **Keep PHP and dependencies updated**

## Database Migration

If you need to migrate your local database to AWS RDS:

1. Export local database:
```bash
mysqldump -u root -p electronics_store > database_backup.sql
```

2. Import to RDS:
```bash
mysql -h your-rds-endpoint.amazonaws.com -u username -p electronics_store < database_backup.sql
```

## Performance Optimization

1. **Enable caching** (Redis/ElastiCache)
2. **Use CDN** for static files
3. **Optimize images** before upload
4. **Database indexing** for frequently queried columns

## Monitoring

1. **CloudWatch** for application metrics
2. **RDS monitoring** for database performance
3. **Error tracking** through logs
4. **Uptime monitoring**

---

**Note**: After deployment, test all functionality thoroughly, especially:
- User registration/login
- Product addition/editing
- Image uploads
- Database operations
