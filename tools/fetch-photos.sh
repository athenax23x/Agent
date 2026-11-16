#!/usr/bin/env bash
# Downloads candidate product photos from the Pexels CDN (Pexels License: free for
# commercial use, no attribution required) into /tmp/shots/<slug>.jpg.
# Usage: tools/fetch-photos.sh <slug:pexels_id> [...]
set -u
OUT="${OUT:-/tmp/shots}"
mkdir -p "$OUT"
for pair in "$@"; do
  slug="${pair%%:*}"
  id="${pair##*:}"
  url="https://images.pexels.com/photos/${id}/pexels-photo-${id}.jpeg?auto=compress&cs=tinysrgb&w=1400"
  code=$(curl -s -o "$OUT/$slug.jpg" -w '%{http_code}' --max-time 60 "$url")
  size=$(wc -c < "$OUT/$slug.jpg" 2>/dev/null || echo 0)
  printf '%-14s id=%-9s http=%s bytes=%s\n' "$slug" "$id" "$code" "$size"
done
