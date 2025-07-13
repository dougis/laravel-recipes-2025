#!/bin/bash

# Coverage upload script for Codacy
# This script uploads test coverage reports to Codacy for analysis

set -e

# Configuration
CODACY_API_BASE_URL="${CODACY_API_BASE_URL:-https://api.codacy.com}"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if required environment variables are set
check_environment() {
    if [[ -z "${CODACY_PROJECT_TOKEN}" ]]; then
        print_error "CODACY_PROJECT_TOKEN environment variable is not set"
        print_info "Please set the CODACY_PROJECT_TOKEN environment variable with your project token from Codacy"
        exit 1
    fi
}

# Function to check if coverage files exist
check_coverage_files() {
    local coverage_files=()
    
    # Check for PHP coverage files
    if [[ -f "${PROJECT_ROOT}/coverage-unit.xml" ]]; then
        coverage_files+=("${PROJECT_ROOT}/coverage-unit.xml")
    fi
    
    if [[ -f "${PROJECT_ROOT}/coverage-feature.xml" ]]; then
        coverage_files+=("${PROJECT_ROOT}/coverage-feature.xml")
    fi
    
    if [[ -f "${PROJECT_ROOT}/coverage.xml" ]]; then
        coverage_files+=("${PROJECT_ROOT}/coverage.xml")
    fi
    
    # Check for JavaScript/frontend coverage files
    if [[ -f "${PROJECT_ROOT}/coverage/lcov.info" ]]; then
        coverage_files+=("${PROJECT_ROOT}/coverage/lcov.info")
    fi
    
    if [[ ${#coverage_files[@]} -eq 0 ]]; then
        print_error "No coverage files found. Please run tests with coverage generation first."
        print_info "Expected files: coverage-unit.xml, coverage-feature.xml, coverage.xml, or coverage/lcov.info"
        exit 1
    fi
    
    print_info "Found ${#coverage_files[@]} coverage file(s): ${coverage_files[*]}"
    echo "${coverage_files[@]}"
}

# Function to install Codacy coverage reporter
install_codacy_reporter() {
    print_info "Installing Codacy coverage reporter..."
    
    if command -v codacy-coverage-reporter &> /dev/null; then
        print_info "Codacy coverage reporter already installed"
        return 0
    fi
    
    # Download and install the reporter
    curl -Ls https://coverage.codacy.com/get.sh > /tmp/codacy-reporter.sh
    chmod +x /tmp/codacy-reporter.sh
    bash /tmp/codacy-reporter.sh
    
    if [[ $? -eq 0 ]]; then
        print_info "Codacy coverage reporter installed successfully"
    else
        print_error "Failed to install Codacy coverage reporter"
        exit 1
    fi
}

# Function to upload coverage file
upload_coverage_file() {
    local file_path="$1"
    local file_format="$2"
    
    print_info "Uploading coverage file: $file_path (format: $file_format)"
    
    # Determine the appropriate upload command based on format
    if [[ "$file_format" == "lcov" ]]; then
        bash <(curl -Ls https://coverage.codacy.com/get.sh) report -l LCOV -r "$file_path"
    else
        bash <(curl -Ls https://coverage.codacy.com/get.sh) report -l "$(echo "$file_format" | tr '[:lower:]' '[:upper:]')" -r "$file_path"
    fi
    
    local upload_result=$?
    
    if [[ $upload_result -eq 0 ]]; then
        print_info "Successfully uploaded $file_path"
    else
        print_error "Failed to upload $file_path (exit code: $upload_result)"
        return 1
    fi
}

# Function to combine and upload coverage
upload_coverage() {
    local coverage_files=($1)
    local upload_success=0
    local upload_failures=0
    
    for file in "${coverage_files[@]}"; do
        local format="clover"
        
        # Determine format based on file extension/path
        if [[ "$file" == *"lcov.info" ]]; then
            format="lcov"
        elif [[ "$file" == *".xml" ]]; then
            format="clover"
        fi
        
        if upload_coverage_file "$file" "$format"; then
            ((upload_success++))
        else
            ((upload_failures++))
        fi
    done
    
    print_info "Coverage upload summary: $upload_success successful, $upload_failures failed"
    
    if [[ $upload_failures -gt 0 ]]; then
        print_error "Some coverage uploads failed"
        exit 1
    fi
}

# Function to send final coverage report
finalize_coverage() {
    print_info "Finalizing coverage report..."
    bash <(curl -Ls https://coverage.codacy.com/get.sh) final
    
    if [[ $? -eq 0 ]]; then
        print_info "Coverage report finalized successfully"
    else
        print_warning "Failed to finalize coverage report (this may be expected in some CI environments)"
    fi
}

# Main function
main() {
    print_info "Starting Codacy coverage upload process..."
    
    # Check prerequisites
    check_environment
    
    # Find coverage files
    local coverage_files
    coverage_files=$(check_coverage_files)
    
    # Install reporter if needed
    install_codacy_reporter
    
    # Upload coverage files
    upload_coverage "$coverage_files"
    
    # Finalize the report
    finalize_coverage
    
    print_info "Coverage upload process completed successfully!"
}

# Handle script arguments
case "${1:-}" in
    "check")
        check_environment
        check_coverage_files
        print_info "Environment and coverage files check completed successfully"
        ;;
    "install")
        install_codacy_reporter
        ;;
    "upload")
        main
        ;;
    *)
        print_info "Usage: $0 {check|install|upload}"
        print_info "  check   - Check environment and coverage files"
        print_info "  install - Install Codacy coverage reporter"
        print_info "  upload  - Upload coverage to Codacy (default action)"
        echo
        main
        ;;
esac