"""Re-extract the catalog images applying their soft mask, then normalise them.

extract_image() returns only the base image, so pictures whose transparency is
stored in a separate SMask came out with a black background. Rebuilding the
pixmap from the document applies the mask. Each image is then trimmed to its
visible content and padded evenly so the product grid looks consistent.
"""

import io
import os

import fitz
from PIL import Image, ImageChops, ImageOps

PDF = r"c:\Users\mauro\Downloads\CATALOGO DOTACIONES RAYMOND 2026.pdf"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out", "img")
os.makedirs(OUT, exist_ok=True)

MAX_SIDE = 1100
PAD_RATIO = 0.04

doc = fitz.open(PDF)


def content_box(image):
    """Bounding box of the visible subject, using alpha when present."""
    if image.mode == "RGBA":
        alpha_box = image.split()[3].point(lambda v: 255 if v > 12 else 0).getbbox()
        if alpha_box:
            return alpha_box
    rgb = image.convert("RGB")
    inverted = ImageChops.invert(ImageOps.grayscale(rgb))
    return inverted.point(lambda v: 255 if v > 18 else 0).getbbox()


saved = with_alpha = 0
for pno, page in enumerate(doc, start=1):
    for k, info in enumerate(page.get_images(full=True), start=1):
        xref = info[0]
        pix = fitz.Pixmap(doc, xref)
        if pix.colorspace and pix.colorspace.n == 4:
            pix = fitz.Pixmap(fitz.csRGB, pix)

        meta = doc.extract_image(xref)
        smask = meta.get("smask")
        if not pix.alpha and smask:
            pix = fitz.Pixmap(pix, fitz.Pixmap(doc, smask))

        image = Image.open(io.BytesIO(pix.tobytes("png")))
        image = image.convert("RGBA" if pix.alpha else "RGB")
        if pix.alpha:
            with_alpha += 1

        box = content_box(image)
        if box:
            image = image.crop(box)

        pad = round(max(image.size) * PAD_RATIO)
        canvas_size = (image.width + pad * 2, image.height + pad * 2)
        if image.mode == "RGBA":
            canvas = Image.new("RGBA", canvas_size, (255, 255, 255, 0))
            canvas.paste(image, (pad, pad), image)
        else:
            canvas = Image.new("RGB", canvas_size, (255, 255, 255))
            canvas.paste(image, (pad, pad))
        image = canvas

        if max(image.size) > MAX_SIDE:
            ratio = MAX_SIDE / max(image.size)
            image = image.resize((round(image.width * ratio), round(image.height * ratio)), Image.LANCZOS)

        image.save(os.path.join(OUT, f"p{pno:02d}-{k}.png"), optimize=True)
        saved += 1

print(f"guardadas={saved} con transparencia={with_alpha}")
