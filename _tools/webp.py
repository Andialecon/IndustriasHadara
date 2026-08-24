"""Convierte las imágenes del catálogo a WebP con transparencia.

Los PNG extraídos del catálogo pesan entre 200 KB y 1,2 MB cada uno, demasiado
para una rejilla de 24 fichas. WebP con pérdida conserva el canal alfa y baja el
peso alrededor de diez veces.
"""

import os

from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, "out", "img")
DST = os.path.join(HERE, "out", "webp")
MAX_SIDE = 1100
QUALITY = 82


def main() -> None:
    os.makedirs(DST, exist_ok=True)
    before = after = 0

    for name in sorted(os.listdir(SRC)):
        if not name.lower().endswith((".png", ".jpeg", ".jpg")):
            continue

        src = os.path.join(SRC, name)
        image = Image.open(src).convert("RGBA")

        if max(image.size) > MAX_SIDE:
            image.thumbnail((MAX_SIDE, MAX_SIDE), Image.LANCZOS)

        dst = os.path.join(DST, os.path.splitext(name)[0] + ".webp")
        image.save(dst, "WEBP", quality=QUALITY, method=6)

        before += os.path.getsize(src)
        after += os.path.getsize(dst)

    print(f"{before / 1048576:.1f} MB -> {after / 1048576:.1f} MB")


if __name__ == "__main__":
    main()
