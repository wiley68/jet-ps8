#!/usr/bin/env bash
set -euo pipefail

module_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$module_root"

for command in php composer zip; do
    if ! command -v "$command" >/dev/null 2>&1; then
        printf 'Missing required command: %s\n' "$command" >&2
        exit 1
    fi
done

version="$(php <<'PHP'
<?php
$source = file_get_contents('creditjet.php');
if (!preg_match('/\$this->version\s*=\s*[\'"](\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?)[\'"]\s*;/', $source, $matches)) {
    file_put_contents('php://stderr', "Cannot read the module version from creditjet.php.\n");
    exit(1);
}
echo $matches[1];
PHP
)"

dist_dir="$module_root/dist"
mkdir -p "$dist_dir"
staging_dir="$(mktemp -d "$dist_dir/.build-XXXXXX")"
trap 'rm -rf -- "$staging_dir"' EXIT

package_dir="$staging_dir/creditjet"
mkdir "$package_dir"
cp -R -- creditjet.php index.php logo.png config.xml config_bg.xml \
    composer.json composer.lock CHANGELOG.md bom.json \
    classes config controllers css fonts img js mails src translations views \
    "$package_dir/"

# Generate production autoload files without modifying the installed module.
composer --working-dir="$package_dir" install \
    --no-dev --no-plugins --no-scripts --prefer-dist \
    --optimize-autoloader --no-interaction --no-progress

archive_name="CC_PrestaShop_8_JET_v.$version.zip"
(
    cd "$staging_dir"
    zip -q -r "$archive_name" creditjet
)
mv -f -- "$staging_dir/$archive_name" "$dist_dir/$archive_name"

for old_archive in "$dist_dir"/CC_PrestaShop_8_JET_v.*.zip; do
    if [[ "$old_archive" != "$dist_dir/$archive_name" ]]; then
        rm -f -- "$old_archive"
    fi
done

printf 'Created: %s\n' "$dist_dir/$archive_name"
