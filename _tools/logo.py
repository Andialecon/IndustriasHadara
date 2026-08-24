"""Derive the site brand assets from the generated Industrias Hadara logo.

The generator returns the lockup on an opaque near-white background, so the
background is keyed out first. Produces a trimmed horizontal logo, a light
knockout variant for the dark footer, and a square emblem for the site icon.
"""

import os

from PIL import Image, ImageChops, ImageOps

SRC = r"C:\Users\mauro\.cursor\projects\c-Users-mauro-Local-Sites-industrias-hadara\assets\hadara-logo.png"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out", "brand")
os.makedirs(OUT, exist_ok=True)

AMBER = (245, 166, 35)
SOLID_BELOW = 205
CLEAR_ABOVE = 238

source = Image.open(SRC).convert("RGB")
r, g, b = source.split()

gray = ImageOps.grayscale(source)
chroma = ImageChops.subtract(
    ImageChops.lighter(ImageChops.lighter(r, g), b),
    ImageChops.darker(ImageChops.darker(r, g), b),
)

span = CLEAR_ABOVE - SOLID_BELOW
alpha = ImageChops.lighter(
    gray.point(lambda v: 255 if v <= SOLID_BELOW else (0 if v >= CLEAR_ABOVE else round(255 * (CLEAR_ABOVE - v) / span))),
    chroma.point(lambda v: 255 if v >= 18 else 0),
)

img = source.copy()
img.putalpha(alpha)
logo = img.crop(img.getbbox())
print("recortado a", logo.size)


def scaled(image, height):
    ratio = height / image.height
    return image.resize((max(1, round(image.width * ratio)), height), Image.LANCZOS)


header = scaled(logo, 140)
header.save(os.path.join(OUT, "logo-industrias-hadara.png"))
print("logo header ->", header.size)

work = scaled(logo, 420)
pixels = work.load()
for y in range(work.height):
    for x in range(work.width):
        pr, pg, pb, pa = pixels[x, y]
        if pa == 0:
            continue
        if pr > 190 and 110 < pg < 210 and pb < 120:
            pixels[x, y] = (*AMBER, pa)
        elif (pr + pg + pb) / 3 < 120:
            pixels[x, y] = (255, 255, 255, pa)
        else:
            pixels[x, y] = (203, 213, 224, pa)

light = scaled(work, 140)
light.save(os.path.join(OUT, "logo-industrias-hadara-claro.png"))
print("logo claro ->", light.size)

# The emblem sits left of the wordmark; the widest fully transparent column run
# is the gap between them.
mask = logo.split()[3]
width, height = mask.size
data = list(mask.getdata())
columns = [max(data[y * width + x] for y in range(0, height, 3)) for x in range(width)]

best_start = best_len = run_start = run_len = 0
for x, value in enumerate(columns):
    if value < 8:
        if run_len == 0:
            run_start = x
        run_len += 1
        if run_len > best_len:
            best_len, best_start = run_len, run_start
    else:
        run_len = 0

print(f"gap: inicia en {best_start}px, ancho {best_len}px (imagen {width}px)")
emblem = logo.crop((0, 0, best_start, height))
emblem = emblem.crop(emblem.getbbox())
print("emblema ->", emblem.size)

side = round(max(emblem.size) * 1.2)
icon = Image.new("RGBA", (side, side), (0, 0, 0, 0))
icon.paste(emblem, ((side - emblem.width) // 2, (side - emblem.height) // 2), emblem)
icon.resize((512, 512), Image.LANCZOS).save(os.path.join(OUT, "icono-industrias-hadara.png"))
print("icono -> 512x512")
print("out ->", OUT)
