"""Print each catalog image with its grid position (row, col) in reading order.

Combined with the rendered page previews this gives an unambiguous mapping from
a product shown on a page to the extracted image file.
"""

import json
import os
import sys

sys.stdout.reconfigure(encoding="utf-8")

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out")
wanted = [int(a) for a in sys.argv[1:]] or None

with open(os.path.join(OUT, "map.json"), encoding="utf-8") as fh:
    pages = json.load(fh)


def cluster(values, tolerance):
    ordered = sorted(values)
    groups = [[ordered[0]]]
    for value in ordered[1:]:
        if value - groups[-1][-1] <= tolerance:
            groups[-1].append(value)
        else:
            groups.append([value])
    return groups


for page in pages:
    if wanted and page["page"] not in wanted:
        continue
    images = page["images"]
    if not images:
        continue
    rows = cluster([im["bbox"][1] for im in images], 60)
    cols = cluster([im["bbox"][0] for im in images], 60)

    def pos(im):
        row = next(i for i, g in enumerate(rows) if im["bbox"][1] in g)
        col = next(i for i, g in enumerate(cols) if im["bbox"][0] in g)
        return row, col

    print(f"\n===== PAGE {page['page']} =====")
    for im in sorted(images, key=pos):
        row, col = pos(im)
        bbox = im["bbox"]
        print(f"  r{row}c{col}  {im['file']:>14} {im['w']}x{im['h']:<5} bbox={bbox}")
