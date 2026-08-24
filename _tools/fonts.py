"""Download the theme webfonts from Google Fonts and self-host them.

Self-hosting removes a third-party request from the critical path and keeps the
site working without external connectivity.
"""

import os
import re
import urllib.request

THEME = os.path.join(
    os.path.dirname(os.path.dirname(os.path.abspath(__file__))),
    "app",
    "public",
    "wp-content",
    "themes",
    "hadara",
)
FONT_DIR = os.path.join(THEME, "assets", "fonts")
os.makedirs(FONT_DIR, exist_ok=True)

CSS_URL = (
    "https://fonts.googleapis.com/css2"
    "?family=Barlow+Condensed:wght@600;700"
    "&family=Inter:wght@400..700"
    "&display=swap"
)
UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"

request = urllib.request.Request(CSS_URL, headers={"User-Agent": UA})
css = urllib.request.urlopen(request, timeout=30).read().decode("utf-8")

blocks = css.split("@font-face")
rules = []
for block in blocks[1:]:
    subset = re.search(r"/\*\s*([a-z0-9\-\[\]]+)\s*\*/", blocks[blocks.index(block) - 1] or "")
    family = re.search(r"font-family:\s*'([^']+)'", block)
    weight = re.search(r"font-weight:\s*([\d\s]+);", block)
    url = re.search(r"url\(([^)]+\.woff2)\)", block)
    unicode_range = re.search(r"unicode-range:\s*([^;]+);", block)
    if not (family and url):
        continue
    # keep only the plain latin subset, which is the last one Google emits
    if unicode_range and "U+0000-00FF" not in unicode_range.group(1):
        continue
    rules.append(
        {
            "family": family.group(1),
            "weight": (weight.group(1).strip() if weight else "400"),
            "url": url.group(1),
        }
    )

lines = ["/* Tipografías auto-alojadas. Generado por _tools/fonts.py */", ""]
for rule in rules:
    slug = rule["family"].lower().replace(" ", "-") + "-" + rule["weight"].replace(" ", "-")
    filename = f"{slug}.woff2"
    target = os.path.join(FONT_DIR, filename)
    data = urllib.request.urlopen(urllib.request.Request(rule["url"], headers={"User-Agent": UA}), timeout=30).read()
    with open(target, "wb") as fh:
        fh.write(data)
    print(f"{filename:<34} {len(data)/1024:6.1f} KB")

    lines += [
        "@font-face {",
        f"\tfont-family: '{rule['family']}';",
        "\tfont-style: normal;",
        f"\tfont-weight: {rule['weight']};",
        "\tfont-display: swap;",
        f"\tsrc: url('../fonts/{filename}') format('woff2');",
        "\tunicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215;",
        "}",
        "",
    ]

css_path = os.path.join(THEME, "assets", "css")
os.makedirs(css_path, exist_ok=True)
with open(os.path.join(css_path, "fonts.css"), "w", encoding="utf-8") as fh:
    fh.write("\n".join(lines))

print("reglas:", len(rules))
print("css ->", os.path.join(css_path, "fonts.css"))
