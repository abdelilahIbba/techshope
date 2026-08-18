#!/bin/sh
# Verifies that this clone is configured to push exclusively to the
# techshope repository, and never to the retired Twamof/-droguerie_s repo.
#
# Usage: ./scripts/verify-git-remote.sh

EXPECTED_URL="https://github.com/abdelilahIbba/techshope.git"
OLD_URL_PATTERN="Twamof.*droguerie_s"

pass=0
fail=0

ok()   { printf '  [PASS] %s\n' "$1"; pass=$((pass + 1)); }
bad()  { printf '  [FAIL] %s\n' "$1"; fail=$((fail + 1)); }
info() { printf '  [INFO] %s\n' "$1"; }

echo "== 1. origin remote URL =="
origin_fetch_url=$(git remote get-url origin 2>/dev/null)
origin_push_url=$(git remote get-url --push origin 2>/dev/null)

if [ "$origin_fetch_url" = "$EXPECTED_URL" ]; then
    ok "origin fetch URL is $EXPECTED_URL"
else
    bad "origin fetch URL is '$origin_fetch_url', expected '$EXPECTED_URL'"
fi

if [ "$origin_push_url" = "$EXPECTED_URL" ]; then
    ok "origin push URL is $EXPECTED_URL"
else
    bad "origin push URL is '$origin_push_url', expected '$EXPECTED_URL'"
fi

echo ""
echo "== 2. no remote points at the old repository =="
old_refs=$(git remote -v | grep -Ei "$OLD_URL_PATTERN")
if [ -z "$old_refs" ]; then
    ok "no configured remote references the old repository"
else
    bad "found remote(s) still pointing at the old repository:"
    printf '%s\n' "$old_refs" | sed 's/^/         /'
fi

echo ""
echo "== 3. branch tracking =="
current_branch=$(git branch --show-current)
upstream=$(git rev-parse --abbrev-ref "${current_branch}@{upstream}" 2>/dev/null)

if [ "$upstream" = "origin/${current_branch}" ]; then
    ok "'$current_branch' tracks 'origin/$current_branch'"
else
    bad "'$current_branch' tracks '$upstream', expected 'origin/$current_branch'"
fi

echo ""
echo "== 4. global URL rewrite safety net (covers ad-hoc old-URL pushes) =="
rewrites=$(git config --global --get-regexp 'url\..*\.insteadof' 2>/dev/null | grep -Ei "$OLD_URL_PATTERN")
if [ -n "$rewrites" ]; then
    ok "global insteadOf rewrite(s) present for the old repository URL"
else
    bad "no global insteadOf rewrite found — an ad-hoc push to the old URL would not be redirected"
fi

echo ""
echo "== 5. pre-push hook guard =="
hooks_path=$(git config core.hooksPath 2>/dev/null)
if [ "$hooks_path" = ".githooks" ] && [ -x "$(git rev-parse --show-toplevel)/.githooks/pre-push" ]; then
    ok "core.hooksPath=.githooks and pre-push guard is executable"
else
    bad "pre-push guard is not active (core.hooksPath='$hooks_path')"
fi

echo ""
echo "== 6. origin reachability (read-only, no push performed) =="
if git ls-remote --heads origin >/dev/null 2>&1; then
    ok "origin ($EXPECTED_URL) is reachable"
else
    info "could not reach origin (no network access or missing credentials in this environment) — skipped"
fi

echo ""
echo "============================================"
echo "  $pass check(s) passed, $fail check(s) failed"
echo "============================================"

[ "$fail" -eq 0 ]
