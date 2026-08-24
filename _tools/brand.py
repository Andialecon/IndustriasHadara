"""Prepara los archivos de marca a partir del logotipo y el banner oficiales.

Genera:
  - logo-industrias-hadara.png        logotipo para fondos claros (cabecera)
  - logo-industrias-hadara-claro.png  versión en blanco para el azul del pie
  - icono-industrias-hadara.png       favicon cuadrado con el emblema del cuello
  - hero-equipo.webp                  recorte del banner (personas + arco) con
                                      el borde izquierdo difuminado para que se
                                      integre con el fondo de la portada
  - banner-hadara.jpg                 banner completo para redes sociales
"""

import os

from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, "brand")

ASSETS = os.path.join(
    os.path.expanduser("~"),
    ".cursor",
    "projects",
    "c-Users-mauro-Local-Sites-industrias-hadara",
    "assets",
)
LOGO = os.path.join(
    ASSETS,
    "c__Users_mauro_AppData_Roaming_Cursor_User_workspaceStorage_empty-window_images_"
    "Logo_con_transparencia_HADARA-bfa2b2ea-8041-46a6-885d-4c7b81ae13e2.png",
)
BANNER = os.path.join(
    ASSETS,
    "c__Users_mauro_AppData_Roaming_Cursor_User_workspaceStorage_empty-window_images_"
    "758154226_122116078179305600_9045559500993928941_n-f023e108-9be1-47dc-ac7e-5debdaaad94b.png",
)

# El emblema del cuello ocupa las primeras 212 columnas; el logotipo empieza en 233.
ICON_RIGHT = 212
ORANGE = (255, 98, 1)
NAVY = (3, 27, 61)
BG = (239, 239, 239)


def is_orange(pixel) -> bool:
    r, g, b = pixel[:3]
    return r > 140 and 40 < g < 190 and b < 110


def light_variant(image: Image.Image) -> Image.Image:
    """Pasa a blanco todo lo que es azul marino y conserva los naranjas."""
    out = image.copy()
    pixels = out.load()

    for y in range(out.height):
        for x in range(out.width):
            r, g, b, a = pixels[x, y]

            if a == 0 or is_orange((r, g, b)):
                continue

            # Los grises del texto «INDUSTRIAS» quedan en un gris claro para no
            # competir con el nombre.
            gray = max(abs(r - g), abs(g - b)) < 26 and r > 60
            target = (214, 221, 230) if gray else (255, 255, 255)
            pixels[x, y] = (*target, a)

    return out


def build_logo() -> None:
    logo = Image.open(LOGO).convert("RGBA")
    logo = logo.crop(logo.getbbox())
    logo.save(os.path.join(OUT, "logo-industrias-hadara.png"))
    light_variant(logo).save(os.path.join(OUT, "logo-industrias-hadara-claro.png"))
    print("logotipo", logo.size)


def build_icon() -> None:
    """Favicon: emblema en blanco sobre el azul de la marca.

    A 16 px el emblema en azul marino sobre fondo claro se pierde, así que la
    versión para la pestaña invierte los colores.
    """
    icon = light_variant(Image.open(LOGO).convert("RGBA").crop((0, 0, ICON_RIGHT, 170)))
    icon = icon.crop(icon.getbbox())

    side = int(max(icon.size) * 1.34)
    canvas = Image.new("RGBA", (side, side), (*NAVY, 255))
    canvas.paste(icon, ((side - icon.width) // 2, (side - icon.height) // 2), icon)
    canvas.resize((512, 512), Image.LANCZOS).save(os.path.join(OUT, "icono-industrias-hadara.png"))
    print("icono", icon.size)


def build_hero() -> None:
    """Recorta del banner las personas y el arco de la marca.

    La portada escribe su propio titular en HTML para que los buscadores lo lean,
    así que del banner solo se conserva la fotografía. El recorte deja fuera el
    texto grande y borra los restos de tipografía que quedan junto al brazo de la
    modelo: se descartan las manchas que no forman parte de la figura.
    """
    banner = Image.open(BANNER).convert("RGB")
    crop = banner.crop((545, 0, banner.width, banner.height))
    width, height = crop.size
    pixels = crop.load()

    def is_background(x, y):
        r, g, b = pixels[x, y]
        return max(abs(r - BG[0]), abs(g - BG[1]), abs(b - BG[2])) <= 10

    # Etiquetado de regiones contiguas que no son fondo.
    labels = [[0] * width for _ in range(height)]
    sizes = {0: 0}
    label = 0

    for start_y in range(height):
        for start_x in range(width):
            if labels[start_y][start_x] or is_background(start_x, start_y):
                continue

            label += 1
            stack = [(start_x, start_y)]
            labels[start_y][start_x] = label
            count = 0

            while stack:
                x, y = stack.pop()
                count += 1

                for nx, ny in ((x - 1, y), (x + 1, y), (x, y - 1), (x, y + 1)):
                    if 0 <= nx < width and 0 <= ny < height and not labels[ny][nx] and not is_background(nx, ny):
                        labels[ny][nx] = label
                        stack.append((nx, ny))

            sizes[label] = count

    keep = max(sizes, key=sizes.get)

    for y in range(height):
        for x in range(width):
            if labels[y][x] not in (0, keep):
                pixels[x, y] = BG

    crop = crop.resize((width * 2, height * 2), Image.LANCZOS)
    crop.save(os.path.join(OUT, "hero-equipo.webp"), "WEBP", quality=88, method=6)
    print("hero", crop.size, "regiones", len(sizes) - 1)

    banner.save(os.path.join(OUT, "banner-hadara.jpg"), "JPEG", quality=86, optimize=True)


def main() -> None:
    os.makedirs(OUT, exist_ok=True)
    build_logo()
    build_icon()
    build_hero()


if __name__ == "__main__":
    main()
