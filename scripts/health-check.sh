#!/bin/bash

# Laravel Recipes 2025 - Health Check Script
# This script performs comprehensive health checks on the application

set -e

# Configuration
APP_NAME="laravel-recipes-2025"
HEALTH_CHECK_URL="http://localhost:8080"
TIMEOUT=10
VERBOSE=false

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -u|--url)
            HEALTH_CHECK_URL="$2"
            shift 2
            ;;
        -t|--timeout)
            TIMEOUT="$2"
            shift 2
            ;;
        -h|--help)
            echo "Usage: $0 [options]"
            echo ""
            echo "Options:"
            echo "  -v, --verbose     Enable verbose output"
            echo "  -u, --url URL     Base URL for health checks (default: http://localhost:8080)"
            echo "  -t, --timeout N   Request timeout in seconds (default: 10)"
            echo "  -h, --help        Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Health check result tracking
HEALTH_CHECKS_PASSED=0
HEALTH_CHECKS_TOTAL=0
HEALTH_ISSUES=()

# Logging functions
log() {
    if [ "$VERBOSE" = true ]; then
        echo -e "${BLUE}[INFO]${NC} $1"
    fi
}

success() {
    echo -e "${GREEN}[✓]${NC} $1"
    ((HEALTH_CHECKS_PASSED++))
}

error() {
    echo -e "${RED}[✗]${NC} $1"
    HEALTH_ISSUES+=("$1")
}

warning() {
    echo -e "${YELLOW}[!]${NC} $1"
    HEALTH_ISSUES+=("$1")
}

check_counter() {
    ((HEALTH_CHECKS_TOTAL++))
}

# Health check functions
check_docker_services() {
    log "Checking Docker services..."
    check_counter
    
    if command -v docker-compose >/dev/null 2>&1; then
        if docker-compose ps 2>/dev/null | grep -q "Up"; then
            local running_services=$(docker-compose ps --services --filter="status=running" | wc -l)
            local total_services=$(docker-compose ps --services | wc -l)
            
            if [ "$running_services" -eq "$total_services" ]; then
                success "All Docker services are running ($running_services/$total_services)"
            else
                error "Only $running_services/$total_services Docker services are running"
            fi
        else
            error "No Docker services are running"
        fi
    else
        warning "Docker Compose not available - skipping service check"
    fi
}

check_web_server() {
    log "Checking web server availability..."
    check_counter
    
    if curl -f -s --max-time "$TIMEOUT" "$HEALTH_CHECK_URL" >/dev/null; then
        success "Web server is responding"
    else
        error "Web server is not responding at $HEALTH_CHECK_URL"
    fi
}

check_api_endpoints() {
    log "Checking API endpoints..."
    
    # Public recipes endpoint
    check_counter
    if curl -f -s --max-time "$TIMEOUT" "$HEALTH_CHECK_URL/api/v1/recipes/public" >/dev/null; then
        success "Public recipes API is responding"
    else
        error "Public recipes API is not responding"
    fi
    
    # Public cookbooks endpoint
    check_counter
    if curl -f -s --max-time "$TIMEOUT" "$HEALTH_CHECK_URL/api/v1/cookbooks/public" >/dev/null; then
        success "Public cookbooks API is responding"
    else
        error "Public cookbooks API is not responding"
    fi
    
    # Health endpoint (if available)
    check_counter
    if curl -f -s --max-time "$TIMEOUT" "$HEALTH_CHECK_URL/health" >/dev/null; then
        success "Health endpoint is responding"
    else
        warning "Health endpoint is not available (this is optional)"
    fi
}

check_database_connectivity() {
    log "Checking database connectivity..."
    check_counter
    
    if command -v docker-compose >/dev/null 2>&1; then
        if docker-compose exec -T mongodb mongo --eval "db.runCommand({ ping: 1 })" >/dev/null 2>&1; then
            success "MongoDB is accessible and responding"
        else
            error "MongoDB is not accessible or not responding"
        fi
    else
        warning "Cannot check MongoDB - Docker Compose not available"
    fi
}

check_redis_connectivity() {
    log "Checking Redis connectivity..."
    check_counter
    
    if command -v docker-compose >/dev/null 2>&1; then
        if docker-compose exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
            success "Redis is accessible and responding"
        else
            error "Redis is not accessible or not responding"
        fi
    else
        warning "Cannot check Redis - Docker Compose not available"
    fi
}

check_application_health() {
    log "Checking Laravel application health..."
    check_counter
    
    if command -v docker-compose >/dev/null 2>&1; then
        # Check if we can run artisan commands
        if docker-compose exec -T app php artisan --version >/dev/null 2>&1; then
            success "Laravel application is healthy"
        else
            error "Laravel application is not responding to artisan commands"
        fi
        
        # Check database connection through Laravel
        check_counter
        if docker-compose exec -T app php artisan tinker --execute="DB::connection()->getMongoDB()->listCollections(); echo 'OK';" 2>/dev/null | grep -q "OK"; then
            success "Laravel can connect to MongoDB"
        else
            error "Laravel cannot connect to MongoDB"
        fi
        
        # Check if migrations are up to date
        check_counter
        if docker-compose exec -T app php artisan migrate:status >/dev/null 2>&1; then
            success "Database migrations are up to date"
        else
            warning "Cannot verify migration status"
        fi
    else
        warning "Cannot check Laravel application - Docker Compose not available"
    fi
}

check_disk_space() {
    log "Checking disk space..."
    check_counter
    
    local disk_usage=$(df . | awk 'NR==2 {print $5}' | sed 's/%//')
    
    if [ "$disk_usage" -lt 80 ]; then
        success "Disk usage is healthy (${disk_usage}%)"
    elif [ "$disk_usage" -lt 90 ]; then
        warning "Disk usage is high (${disk_usage}%)"
    else
        error "Disk usage is critical (${disk_usage}%)"
    fi
}

check_memory_usage() {
    log "Checking memory usage..."
    check_counter
    
    if command -v free >/dev/null 2>&1; then
        local memory_usage=$(free | grep '^Mem:' | awk '{print int($3/$2 * 100)}')
        
        if [ "$memory_usage" -lt 80 ]; then
            success "Memory usage is healthy (${memory_usage}%)"
        elif [ "$memory_usage" -lt 90 ]; then
            warning "Memory usage is high (${memory_usage}%)"
        else
            error "Memory usage is critical (${memory_usage}%)"
        fi
    else
        warning "Cannot check memory usage - 'free' command not available"
    fi
}

check_ssl_certificate() {
    log "Checking SSL certificate..."
    check_counter
    
    # Only check if URL uses HTTPS
    if [[ "$HEALTH_CHECK_URL" == https://* ]]; then
        local domain=$(echo "$HEALTH_CHECK_URL" | sed 's|https://||' | sed 's|/.*||')
        
        if echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null | grep -q "notAfter"; then
            local expiry_date=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
            local expiry_epoch=$(date -d "$expiry_date" +%s 2>/dev/null || echo "0")
            local current_epoch=$(date +%s)
            local days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))
            
            if [ "$days_until_expiry" -gt 30 ]; then
                success "SSL certificate is valid (expires in $days_until_expiry days)"
            elif [ "$days_until_expiry" -gt 7 ]; then
                warning "SSL certificate expires soon (in $days_until_expiry days)"
            else
                error "SSL certificate expires very soon (in $days_until_expiry days)"
            fi
        else
            warning "Cannot verify SSL certificate"
        fi
    else
        warning "SSL certificate check skipped (HTTP URL)"
    fi
}

check_queue_workers() {
    log "Checking queue workers..."
    check_counter
    
    if command -v docker-compose >/dev/null 2>&1; then
        if docker-compose ps queue 2>/dev/null | grep -q "Up"; then
            success "Queue workers are running"
        else
            warning "Queue workers are not running"
        fi
    else
        warning "Cannot check queue workers - Docker Compose not available"
    fi
}

check_log_files() {
    log "Checking for recent errors in log files..."
    check_counter
    
    if command -v docker-compose >/dev/null 2>&1; then
        # Check Laravel logs for recent errors
        local recent_errors=$(docker-compose exec -T app sh -c "find storage/logs -name '*.log' -mtime -1 -exec grep -l 'ERROR\|CRITICAL\|EMERGENCY' {} \;" 2>/dev/null | wc -l)
        
        if [ "$recent_errors" -eq 0 ]; then
            success "No recent errors found in application logs"
        else
            warning "Found recent errors in $recent_errors log file(s)"
        fi
    else
        warning "Cannot check log files - Docker Compose not available"
    fi
}

# Main health check execution
main() {
    echo "🏥 Laravel Recipes 2025 - Health Check"
    echo "======================================"
    echo ""
    
    # Run all health checks
    check_docker_services
    check_web_server
    check_api_endpoints
    check_database_connectivity
    check_redis_connectivity
    check_application_health
    check_disk_space
    check_memory_usage
    check_ssl_certificate
    check_queue_workers
    check_log_files
    
    echo ""
    echo "📊 Health Check Summary"
    echo "======================="
    echo "Checks passed: $HEALTH_CHECKS_PASSED/$HEALTH_CHECKS_TOTAL"
    
    if [ ${#HEALTH_ISSUES[@]} -eq 0 ]; then
        echo -e "${GREEN}✅ All health checks passed!${NC}"
        echo ""
        echo "🎉 The application is healthy and ready to serve users."
        exit 0
    else
        echo -e "${RED}❌ Health check issues found:${NC}"
        for issue in "${HEALTH_ISSUES[@]}"; do
            echo "   - $issue"
        done
        echo ""
        echo "🔧 Please address these issues and run the health check again."
        
        # Return appropriate exit code based on severity
        local critical_issues=$(printf '%s\n' "${HEALTH_ISSUES[@]}" | grep -c "✗" || true)
        if [ "$critical_issues" -gt 0 ]; then
            exit 2  # Critical issues
        else
            exit 1  # Warnings only
        fi
    fi
}

# Help text if no arguments and not running in container
if [ "$#" -eq 0 ] && [ -t 1 ]; then
    echo "Laravel Recipes 2025 - Health Check Script"
    echo ""
    echo "This script performs comprehensive health checks on the application."
    echo "Run with -h or --help for usage information."
    echo ""
fi

# Run main function
main "$@"
