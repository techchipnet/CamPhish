#!/bin/bash
# shorten_url.sh — shorten a URL via TinyURL API
# Usage: shorten_url "https://your-link.com"
# Returns shortened URL or original on failure

shorten_url() {
    local url="$1"
    local short
    short=$(curl -s "https://tinyurl.com/api-create.php?url=$(python3 -c "import urllib.parse; print(urllib.parse.quote('$url'))" 2>/dev/null || echo "$url")" 2>/dev/null)
    if [[ "$short" == https://* ]]; then
        echo "$short"
    else
        echo "$url"
    fi
}
