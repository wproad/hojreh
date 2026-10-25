#!/usr/bin/env bash
# One-command setup for the Hojreh development store.
set -euo pipefail

cd "$(dirname "$0")/.."

WP_URL="${WP_URL:-http://localhost:8080}"
WP_TITLE="${WP_TITLE:-حجره}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASS="${WP_ADMIN_PASS:-admin}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@hojreh.local}"

echo "▶ Starting Docker services…"
docker compose up -d db wordpress

echo "▶ Waiting for WordPress…"
for _ in $(seq 1 60); do
	if curl -fsS -o /dev/null "${WP_URL}/wp-login.php"; then
		break
	fi
	sleep 2
done

run_wp() {
	docker compose --profile cli run --rm wpcli wp "$@"
}

if ! run_wp core is-installed >/dev/null 2>&1; then
	echo "▶ Installing WordPress…"
	run_wp core install \
		--url="${WP_URL}" \
		--title="${WP_TITLE}" \
		--admin_user="${WP_ADMIN_USER}" \
		--admin_password="${WP_ADMIN_PASS}" \
		--admin_email="${WP_ADMIN_EMAIL}" \
		--skip-email
fi

echo "▶ Installing Persian locale and WooCommerce…"
docker compose --profile cli run --rm --entrypoint sh wpcli -c '
set -e
wp language core install fa_IR --activate || true
if ! wp plugin is-installed woocommerce; then wp plugin install woocommerce --activate; fi
wp plugin activate woocommerce >/dev/null 2>&1 || true
wp language plugin install woocommerce fa_IR || true
wp theme activate hojreh
wp option update timezone_string Asia/Tehran >/dev/null
wp option update date_format "Y/m/d" >/dev/null
wp rewrite structure "/%postname%/" --hard >/dev/null 2>&1 || true
wp plugin delete akismet hello >/dev/null 2>&1 || true
'

if command -v pnpm >/dev/null 2>&1; then
	echo "▶ Building theme assets…"
	pnpm install --silent
	pnpm build
else
	echo "! pnpm not found — using the committed build in assets/dist."
fi

PRODUCT_COUNT="$(run_wp post list --post_type=product --format=count 2>/dev/null | tr -d '\r' || echo 0)"

if [ "${PRODUCT_COUNT:-0}" = "0" ]; then
	echo "▶ Seeding demo catalogue…"
	run_wp eval-file /scripts/seed.php
else
	echo "▶ Products already present (${PRODUCT_COUNT}); skipping seed."
fi

echo
echo "✅ Hojreh is ready."
echo "   Store:  ${WP_URL}"
echo "   Admin:  ${WP_URL}/wp-admin/  (${WP_ADMIN_USER} / ${WP_ADMIN_PASS})"
echo "   Cards:  ${WP_URL}/card-demo/"
