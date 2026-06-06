#!/bin/sh

set -eu

cat > /workspace/.gitignore <<EOF
.DS_Store

# Local environment overrides
.env
.env.local
.env.*.local

# Local Docker setup
docker-compose.yml
scripts/

# Ignore everything in WordPress by default
/wordpress/*

# Keep wp-content available for tracked custom code
!/wordpress/wp-content/
/wordpress/wp-content/*

# Keep all plugins in the repository
!/wordpress/wp-content/plugins/
!/wordpress/wp-content/plugins/**

# Ignore all themes except the active project theme
!/wordpress/wp-content/themes/
/wordpress/wp-content/themes/*
!/wordpress/wp-content/themes/${PROJECT_NAME}/
!/wordpress/wp-content/themes/${PROJECT_NAME}/**
EOF

exec docker-entrypoint.sh apache2-foreground
