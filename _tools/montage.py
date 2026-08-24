"""Contact sheet con las imágenes usadas en el catálogo, para revisarlas de un vistazo."""

import json
import math
import os
import sys

from PIL import Image, ImageDraw

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out")
TILE = 150
COLS = 10


def main() -> None:
    catalog = json.load(open(os.path.join(OUT, "catalog.json"), encoding="utf-8"))
    files = [p["img"] for p in catalog["products"]]
    start = int(sys.argv[1]) if len(sys.argv) > 1 else 0
    count = int(sys.argv[2]) if len(sys.argv) > 2 else len(files)
    files = files[start : start + count]

    rows = math.ceil(len(files) / COLS)
    sheet = Image.new("RGB", (COLS * TILE, rows * (TILE + 14)), "white")
    draw = ImageDraw.Draw(sheet)

    for index, name in enumerate(files):
        img = Image.open(os.path.join(OUT, "img", name)).convert("RGBA")
        img.thumbnail((TILE - 8, TILE - 8))
        canvas = Image.new("RGB", (TILE, TILE), "white")
        canvas.paste(img, ((TILE - img.width) // 2, (TILE - img.height) // 2), img)

        x = (index % COLS) * TILE
        y = (index // COLS) * (TILE + 14)
        sheet.paste(canvas, (x, y))
        draw.text((x + 4, y + TILE + 1), f"{start + index}:{name}", fill="black")

    path = os.path.join(OUT, f"sheet-{start}.png")
    sheet.save(path)
    print(path)


if __name__ == "__main__":
    main()
