.PHONY: lint lint-fix test coverage build clean help

# Plugin name
PLUGIN_NAME = bmlt-enabled-stats

# Build settings
BUILD_DIR = build
DIST_DIR = dist
FILENAME ?= $(PLUGIN_NAME)
ZIP_FILENAME = $(FILENAME).zip

# Files and directories to include in the build
INCLUDE_FILES = \
	bmlt-enabled-stats.php \
	uninstall.php \
	readme.txt

INCLUDE_DIRS = \
	admin \
	assets \
	block \
	includes \
	languages \
	templates

# Files and patterns to exclude from the build
EXCLUDE_PATTERNS = \
	*.git* \
	*.DS_Store \
	*node_modules* \
	*vendor* \
	*.log \
	*.swp \
	*.swo

help: ## Show this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

lint: ## Run PHPCS linting
	composer install --prefer-dist --no-progress --quiet
	./vendor/bin/phpcs

lint-fix: ## Auto-fix PHPCS issues
	composer install --prefer-dist --no-progress --quiet
	./vendor/bin/phpcbf

test: ## Run PHPUnit tests
	composer install --prefer-dist --no-progress --quiet
	./vendor/bin/phpunit

coverage: ## Run PHPUnit tests with coverage
	composer install --prefer-dist --no-progress --quiet
	./vendor/bin/phpunit --coverage-clover coverage.xml

build: clean ## Build plugin zip file
	@echo "Building $(ZIP_FILENAME)..."
	@mkdir -p $(BUILD_DIR)/$(PLUGIN_NAME)

	@# Copy individual files
	@for file in $(INCLUDE_FILES); do \
		if [ -f "$$file" ]; then \
			cp "$$file" $(BUILD_DIR)/$(PLUGIN_NAME)/; \
		fi; \
	done

	@# Copy directories
	@for dir in $(INCLUDE_DIRS); do \
		if [ -d "$$dir" ]; then \
			cp -r "$$dir" $(BUILD_DIR)/$(PLUGIN_NAME)/; \
		fi; \
	done

	@# Remove unwanted files
	@find $(BUILD_DIR)/$(PLUGIN_NAME) -name '.git*' -exec rm -rf {} + 2>/dev/null || true
	@find $(BUILD_DIR)/$(PLUGIN_NAME) -name '.DS_Store' -delete 2>/dev/null || true
	@find $(BUILD_DIR)/$(PLUGIN_NAME) -name '*.log' -delete 2>/dev/null || true
	@find $(BUILD_DIR)/$(PLUGIN_NAME) -name '*.swp' -delete 2>/dev/null || true
	@find $(BUILD_DIR)/$(PLUGIN_NAME) -name '*.swo' -delete 2>/dev/null || true

	@# Create zip file
	@cd $(BUILD_DIR) && zip -r $(ZIP_FILENAME) $(PLUGIN_NAME)
	@echo "Built: $(BUILD_DIR)/$(ZIP_FILENAME)"

clean: ## Clean build artifacts
	@rm -rf $(BUILD_DIR)
	@rm -rf $(DIST_DIR)
	@rm -f coverage.xml
	@echo "Cleaned build artifacts"

install: ## Install composer dependencies
	composer install --prefer-dist --no-progress

update: ## Update composer dependencies
	composer update
