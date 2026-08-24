"""Extract catalog images and pair each one with the product label nearest to it.

Writes:
  out/pages/page-NN.png    rendered page previews (for visual verification)
  out/img/pNN-K.<ext>      extracted embedded images
  out/map.json             per-page images with bbox + candidate labels
"""

import json
import os

import fitz

PDF = r"c:\Users\mauro\Downloads\CATALOGO DOTACIONES RAYMOND 2026.pdf"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out")

os.makedirs(os.path.join(OUT, "pages"), exist_ok=True)
os.makedirs(os.path.join(OUT, "img"), exist_ok=True)

doc = fitz.open(PDF)
result = []

for pno, page in enumerate(doc, start=1):
    page.get_pixmap(dpi=90).save(os.path.join(OUT, "pages", f"page-{pno:02d}.png"))

    spans = []
    for block in page.get_text("dict")["blocks"]:
        for line in block.get("lines", []):
            for span in line["spans"]:
                text = span["text"].strip()
                if text:
                    spans.append({"text": text, "bbox": list(span["bbox"]), "size": round(span["size"], 1)})

    images = []
    for k, info in enumerate(page.get_images(full=True), start=1):
        xref = info[0]
        rects = page.get_image_rects(xref)
        if not rects:
            continue
        rect = max(rects, key=lambda r: r.get_area())
        data = doc.extract_image(xref)
        name = f"p{pno:02d}-{k}.{data['ext']}"
        with open(os.path.join(OUT, "img", name), "wb") as fh:
            fh.write(data["image"])

        cx, cy = (rect.x0 + rect.x1) / 2, (rect.y0 + rect.y1) / 2
        scored = []
        for span in spans:
            sx = (span["bbox"][0] + span["bbox"][2]) / 2
            sy = span["bbox"][1]
            # labels sit under the photo in this catalog, so weight vertical
            # distance below the image far more favourably than above it
            dy = sy - rect.y1
            penalty = dy if dy >= 0 else abs(dy) * 3.0
            scored.append((abs(sx - cx) + penalty, span["text"], span["size"], round(dy, 1)))
        scored.sort(key=lambda t: t[0])

        images.append(
            {
                "file": name,
                "w": data["width"],
                "h": data["height"],
                "bbox": [round(v, 1) for v in (rect.x0, rect.y0, rect.x1, rect.y1)],
                "labels": [{"text": t, "size": s, "dy": dy} for _, t, s, dy in scored[:6]],
            }
        )

    result.append({"page": pno, "images": images, "spans": spans})

with open(os.path.join(OUT, "map.json"), "w", encoding="utf-8") as fh:
    json.dump(result, fh, ensure_ascii=False, indent=1)

print(f"pages={len(result)} images={sum(len(p['images']) for p in result)}")
print("out ->", OUT)
