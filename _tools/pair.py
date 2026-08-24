"""Pair each extracted catalog image with the text block sitting beside it.

The catalog uses a card grid where the photo is on the left of each card and its
label stack (name, weight, sizes) is on the right, so candidates are collected
from the same horizontal band to the right of the photo.

Writes out/pairs.json and prints a human-readable report for verification.
"""

import json
import os

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out")

with open(os.path.join(OUT, "map.json"), encoding="utf-8") as fh:
    pages = json.load(fh)

report = []
for page in pages:
    images = sorted(page["images"], key=lambda im: (round(im["bbox"][1] / 40), im["bbox"][0]))
    entries = []
    for im in images:
        x0, y0, x1, y1 = im["bbox"]
        height = y1 - y0
        band_top = y0 - height * 0.15
        band_bottom = y1 + height * 0.15
        beside = []
        for span in page["spans"]:
            sx0, sy0, sx1, sy1 = span["bbox"]
            scy = (sy0 + sy1) / 2
            if sx0 < x1 - 10:
                continue
            if not (band_top <= scy <= band_bottom):
                continue
            if sx0 > x1 + 420:
                continue
            beside.append((round(sy0), round(sx0), span["text"], span["size"]))
        beside.sort()
        entries.append(
            {
                "file": im["file"],
                "px": f"{im['w']}x{im['h']}",
                "bbox": im["bbox"],
                "beside": [b[2] for b in beside],
                "sizes": [b[3] for b in beside],
            }
        )
    report.append({"page": page["page"], "images": entries})

with open(os.path.join(OUT, "pairs.json"), "w", encoding="utf-8") as fh:
    json.dump(report, fh, ensure_ascii=False, indent=1)

for page in report:
    print(f"\n===== PAGE {page['page']} =====")
    for entry in page["images"]:
        label = " | ".join(entry["beside"]) or "(sin texto al lado)"
        print(f"  {entry['file']:>14} {entry['px']:>10}  {label}")
