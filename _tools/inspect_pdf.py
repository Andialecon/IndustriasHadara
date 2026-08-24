import fitz
import sys

doc = fitz.open(r"c:\Users\mauro\Downloads\CATALOGO DOTACIONES RAYMOND 2026.pdf")
total = 0
for i, page in enumerate(doc):
    imgs = page.get_images(full=True)
    info = []
    for xref, *_ in imgs:
        try:
            d = doc.extract_image(xref)
            info.append(f"{d['width']}x{d['height']}.{d['ext']}")
        except Exception as e:
            info.append(f"ERR:{e}")
    total += len(imgs)
    print(f"page {i+1}: {len(imgs)} imgs -> {', '.join(info)}")
print("TOTAL", total)
