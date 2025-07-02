#!/bin/bash

# TEC Sunday School Audio App - Monitoring Script
# Usage: ./monitor.sh

# Configuration
APP_URL="https://your-domain.com"
HEALTH_ENDPOINT="$APP_URL/health"
LOG_FILE="/var/log/tec-sunday-school-monitor.log"
ALERT_EMAIL="admin@your-domain.com"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Function to log with timestamp
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Function to send alert (customize for your notification system)
send_alert() {
    local message="$1"
    log_message "ALERT: $message"
    
    # Email alert (requires mail command)
    if command -v mail >/dev/null 2>&1; then
        echo "$message" | mail -s "TEC Sunday School App Alert" "$ALERT_EMAIL"
    fi
    
    # You can add other notification methods here:
    # - Slack webhook
    # - Discord webhook
    # - SMS service
    # - Push notification service
}

# Check application health
check_health() {
    local response
    local http_code
    
    response=$(curl -s -w "%{http_code}" "$HEALTH_ENDPOINT" --max-time 10)
    http_code="${response: -3}"
    
    if [ "$http_code" = "200" ]; then
        log_message "✅ Health check passed"
        return 0
    else
        log_message "❌ Health check failed - HTTP $http_code"
        send_alert "Health check failed for $APP_URL - HTTP $http_code"
        return 1
    fi
}

# Check disk space
check_disk_space() {
    local usage
    usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    
    if [ "$usage" -gt 90 ]; then
        send_alert "Disk space critical: ${usage}% used"
        return 1
    elif [ "$usage" -gt 80 ]; then
        log_message "⚠️  Disk space warning: ${usage}% used"
        return 0
    else
        log_message "✅ Disk space OK: ${usage}% used"
        return 0
    fi
}

# Check memory usage
check_memory() {
    local mem_usage
    mem_usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
    
    if [ "$mem_usage" -gt 90 ]; then
        send_alert "Memory usage critical: ${mem_usage}%"
        return 1
    elif [ "$mem_usage" -gt 80 ]; then
        log_message "⚠️  Memory usage warning: ${mem_usage}%"
        return 0
    else
        log_message "✅ Memory usage OK: ${mem_usage}%"
        return 0
    fi
}

# Check log file size
check_log_size() {
    local log_size
    local app_log="/var/www/tec-sunday-school/storage/logs/laravel.log"
    
    if [ -f "$app_log" ]; then
        log_size=$(du -m "$app_log" | cut -f1)
        
        if [ "$log_size" -gt 100 ]; then
            log_message "⚠️  Application log file is large: ${log_size}MB"
            # Optionally rotate the log
            # mv "$app_log" "${app_log}.old"
            # touch "$app_log"
            # chown www-data:www-data "$app_log"
        else
            log_message "✅ Log file size OK: ${log_size}MB"
        fi
    fi
}

# Check SSL certificate expiration
check_ssl_cert() {
    local domain
    domain=$(echo "$APP_URL" | sed 's|https://||' | sed 's|/.*||')
    
    if command -v openssl >/dev/null 2>&1; then
        local expiry_date
        expiry_date=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -dates | grep notAfter | cut -d= -f2)
        
        if [ -n "$expiry_date" ]; then
            local expiry_epoch
            local current_epoch
            local days_until_expiry
            
            expiry_epoch=$(date -d "$expiry_date" +%s)
            current_epoch=$(date +%s)
            days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))
            
            if [ "$days_until_expiry" -lt 7 ]; then
                send_alert "SSL certificate expires in $days_until_expiry days"
            elif [ "$days_until_expiry" -lt 30 ]; then
                log_message "⚠️  SSL certificate expires in $days_until_expiry days"
            else
                log_message "✅ SSL certificate OK: $days_until_expiry days until expiry"
            fi
        fi
    fi
}

# Main monitoring function
main() {
    log_message "Starting monitoring check..."
    
    local overall_status=0
    
    # Run all checks
    check_health || overall_status=1
    check_disk_space || overall_status=1
    check_memory || overall_status=1
    check_log_size
    check_ssl_cert
    
    if [ $overall_status -eq 0 ]; then
        log_message "✅ All checks passed"
    else
        log_message "❌ Some checks failed"
    fi
    
    log_message "Monitoring check completed"
    echo ""
}

# Create log file if it doesn't exist
touch "$LOG_FILE"

# Run monitoring
main
