"""Author the Industrias Hadara catalog and emit out/catalog.json for the importer.

Image filenames refer to files produced by extract.py in out/img/. Every pairing
was verified against the rendered catalog pages.
"""

import json
import os
import sys

sys.stdout.reconfigure(encoding="utf-8")

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "out")

CATEGORIES = [
    {
        "slug": "camisas-y-chaquetas",
        "name": "Camisas y chaquetas industriales",
        "menu": "Camisas y chaquetas",
        "title": "Camisas industriales en jean, dril y oxford",
        "intro": "Camisas de dotación en jean, dril y oxford para operación, mantenimiento y áreas administrativas. "
        "Telas de alto gramaje, costuras reforzadas y tallas de S a 5XL en línea caballero y dama, con opción de bordado del logo de su empresa.",
    },
    {
        "slug": "pantalones-y-jeans",
        "name": "Pantalones y jeans de trabajo",
        "menu": "Pantalones y jeans",
        "title": "Pantalones y jeans de trabajo para dotación industrial",
        "intro": "Jeans de 14 onzas, jeans licrados y pantalones en dril de cuatro bolsillos diseñados para jornadas largas. "
        "Incluye referencias dieléctricas para labores eléctricas y horma dama y caballero en tallas 6 a 26 y 28 a 48.",
    },
    {
        "slug": "overoles-y-enterizos",
        "name": "Overoles y enterizos",
        "menu": "Overoles",
        "title": "Overoles industriales enterizos y de dos piezas",
        "intro": "Overoles en dril enterizos, tipo piloto y de dos piezas para mecánica, industria y mantenimiento, "
        "más trajes descartables para manejo de químicos y ambientes que exigen protección desechable.",
    },
    {
        "slug": "chalecos",
        "name": "Chalecos industriales y reflectivos",
        "menu": "Chalecos",
        "title": "Chalecos reflectivos y chalecos en dril",
        "intro": "Chalecos reflectivos de alta visibilidad para obra y logística, y chalecos en dril tipo periodista "
        "con múltiples bolsillos para supervisión, ingeniería y trabajo de campo.",
    },
    {
        "slug": "dotacion-soldador",
        "name": "Dotación para soldador",
        "menu": "Soldador",
        "title": "Dotación para soldador en carnaza y vaqueta",
        "intro": "Línea completa de protección para soldadura y pulimiento en carnaza y vaqueta: delantales, mangas, "
        "polainas, pantalones, chaquetas y capuchas cosidas en hilos de alta resistencia.",
    },
    {
        "slug": "impermeables-y-cuarto-frio",
        "name": "Impermeables y ropa de cuarto frío",
        "menu": "Impermeables y cuarto frío",
        "title": "Impermeables en PVC y ropa térmica para cuarto frío",
        "intro": "Trajes impermeables en PVC con bandas reflectivas, capas y zapatones para trabajo bajo lluvia, "
        "junto con chaquetas y pantalones con capa interior de guata para cavas, neveras industriales y laboratorios.",
    },
    {
        "slug": "mangas-capuchas-delantales",
        "name": "Mangas, capuchas y delantales",
        "menu": "Mangas y delantales",
        "title": "Mangas, capuchas y delantales de protección industrial",
        "intro": "Complementos de dotación que protegen brazos, cabeza y torso: mangas en dril, jean y franela, "
        "capuchas industriales y delantales impermeables en TPU y PVC aptos para manipulación de alimentos.",
    },
    {
        "slug": "calzado-industrial",
        "name": "Calzado industrial de seguridad",
        "menu": "Calzado",
        "title": "Botas de seguridad y calzado industrial dieléctrico",
        "intro": "Botas de seguridad con puntera de acero o composite, suelas dieléctricas resistentes a hidrocarburos, "
        "botas en PVC totalmente impermeables y mocasines de dotación para personal administrativo.",
    },
    {
        "slug": "guantes-de-seguridad",
        "name": "Guantes de seguridad industrial",
        "menu": "Guantes",
        "title": "Guantes de seguridad industrial por nivel de riesgo",
        "intro": "Guantes para cada riesgo: hilaza y poliéster con recubrimiento en látex, PU y nitrilo, "
        "referencias anticorte en fibra HDPE, nitrilo para químicos y guantes en carnaza y vaqueta para calor y soldadura.",
    },
    {
        "slug": "proteccion-visual-facial",
        "name": "Protección visual y facial",
        "menu": "Protección visual",
        "title": "Gafas de seguridad, monogafas y caretas",
        "intro": "Gafas de seguridad en policarbonato con filtro UV y tratamiento antiempaño, monogafas de ajuste hermético "
        "y caretas para esmerilar y soldar, en versiones claras y oscuras según la tarea.",
    },
    {
        "slug": "proteccion-respiratoria",
        "name": "Protección respiratoria",
        "menu": "Respiratoria",
        "title": "Respiradores, medias máscaras y filtros",
        "intro": "Medias máscaras y respiradores de cara completa en silicona y elastómero, respiradores desechables "
        "para material particulado y filtros P100, P3 y de carbón activado para gases y vapores orgánicos.",
    },
    {
        "slug": "proteccion-cabeza-auditiva",
        "name": "Protección para cabeza y oídos",
        "menu": "Cabeza y oídos",
        "title": "Cascos industriales dieléctricos y protectores auditivos",
        "intro": "Cascos tipo I y tipo II clase E dieléctricos con ajuste rachet, versiones reflectivas y para trabajo en alturas, "
        "más protectores auditivos de inserción y de diadema con atenuación certificada.",
    },
    {
        "slug": "trabajo-en-alturas",
        "name": "Equipos para trabajo en alturas",
        "menu": "Trabajo en alturas",
        "title": "Arneses, eslingas y líneas de vida para trabajo en alturas",
        "intro": "Sistemas de protección contra caídas: puntos de anclaje, eslingas de restricción y posicionamiento "
        "con absorbedor de impacto, arrestadores de caída y líneas de vida en poliéster de alta resistencia.",
    },
]

PRODUCTS = []


def add(name, cat, img, excerpt, desc, specs, gallery=None):
    PRODUCTS.append(
        {
            "name": name,
            "cat": cat,
            "img": img,
            "gallery": gallery or [],
            "excerpt": excerpt,
            "desc": desc,
            "specs": specs,
        }
    )


TALLAS_ROPA = "S a 5XL"

# --------------------------------------------------------------- camisas
add(
    "Camisa jean 7 onzas",
    "camisas-y-chaquetas",
    "p03-6.png",
    "Camisa de dotación en jean de 7 onzas, fresca y resistente, disponible en línea caballero y dama de la talla S a la 5XL.",
    "<p>La camisa en jean de 7 onzas es la referencia más versátil de una dotación industrial: pesa lo justo para trabajar "
    "cómodo durante toda la jornada y conserva la resistencia del denim frente al roce y los enganches.</p>"
    "<p>Lleva cuello camisero, botones frontales, dos bolsillos de parche con tapa y costuras reforzadas en hombros y sisas, "
    "los puntos donde una camisa de trabajo se rompe primero. Es la opción indicada para bodega, logística, mantenimiento "
    "y personal técnico que necesita una prenda presentable y duradera.</p>",
    {"Tela": "Jean 7 onzas", "Tallas": TALLAS_ROPA, "Línea": "Caballero y dama", "Personalización": "Bordado o logo de empresa"},
)
add(
    "Chaqueta jean para soldador 14 onzas",
    "camisas-y-chaquetas",
    "p03-1.png",
    "Chaqueta en jean de 14 onzas para soldadura y pulimiento, con denim de alto gramaje que frena chispas y esquirlas.",
    "<p>Chaqueta en jean de 14 onzas pensada para labores de soldadura, pulimiento y esmerilado. El denim de alto gramaje "
    "resiste la proyección de chispa y esquirlas sin perder movilidad, a diferencia de la carnaza, que es más rígida.</p>"
    "<p>El cierre es de botones metálicos, con puños ajustables y bolsillos de parche. Se usa sola o como capa sobre la camisa "
    "de dotación, y se combina con capucha y mangas del mismo material para completar el equipo del soldador.</p>",
    {"Tela": "Jean 14 onzas", "Tallas": TALLAS_ROPA, "Uso": "Soldadura y pulimiento"},
)
add(
    "Camisa jean para soldador 14 onzas",
    "camisas-y-chaquetas",
    "p03-2.png",
    "Camisa en jean de 14 onzas de manga larga para soldadura, con mayor gramaje que la camisa estándar de dotación.",
    "<p>Camisa de trabajo en jean de 14 onzas para quienes necesitan protección adicional contra chispa y calor radiante "
    "sin usar una chaqueta completa. El gramaje duplica el de una camisa de dotación común.</p>"
    "<p>Cuenta con manga larga, cuello camisero, bolsillos frontales y costuras de alta resistencia. Es habitual en talleres "
    "metalmecánicos, estructuras metálicas y mantenimiento industrial.</p>",
    {"Tela": "Jean 14 onzas", "Tallas": TALLAS_ROPA, "Uso": "Soldadura y metalmecánica"},
)
add(
    "Camisa oxford caballero y dama",
    "camisas-y-chaquetas",
    "p03-4.png",
    "Camisa oxford para dotación administrativa y de atención al cliente, disponible en varios colores y en línea caballero y dama.",
    "<p>La camisa oxford es la prenda de dotación para las áreas donde la imagen corporativa pesa tanto como la comodidad: "
    "recepción, ventas, atención al cliente y oficinas técnicas.</p>"
    "<p>El tejido oxford mantiene la forma después de muchos lavados y permite bordar el logo de la empresa en el pecho. "
    "Se fabrica en línea caballero y dama, en varios colores, de la talla S a la 5XL.</p>",
    {"Tela": "Oxford", "Tallas": TALLAS_ROPA, "Línea": "Caballero y dama", "Colores": "Blanco, azul, gris y rosado"},
)
add(
    "Camisa en dril",
    "camisas-y-chaquetas",
    "p03-3.png",
    "Camisa en dril de manga larga para dotación industrial, con amplia carta de colores para diferenciar áreas o cargos.",
    "<p>Camisa en dril de manga larga, la tela más usada en dotación industrial colombiana por su equilibrio entre resistencia, "
    "frescura y facilidad de lavado.</p>"
    "<p>Se ofrece en una carta amplia de colores, lo que permite identificar áreas, turnos o niveles de cargo dentro de la misma "
    "empresa. Tiene bolsillos frontales, botones reforzados y tallas de S a 5XL.</p>",
    {"Tela": "Dril", "Tallas": TALLAS_ROPA, "Colores": "Más de 8 colores disponibles"},
)
add(
    "Camisa en dril tipo administrativa",
    "camisas-y-chaquetas",
    "p05-5.png",
    "Camisa en dril de corte administrativo con dos bolsillos, ideal para supervisión, ingeniería y personal de planta.",
    "<p>Versión de corte más formal de la camisa en dril, con dos bolsillos de parche y acabados pensados para supervisores, "
    "ingenieros y personal que alterna entre planta y oficina.</p>"
    "<p>Conserva la resistencia del dril industrial pero con una silueta más limpia, adecuada para reuniones, visitas de cliente "
    "y auditorías sin cambiar de uniforme.</p>",
    {"Tela": "Dril", "Bolsillos": "2 bolsillos de parche", "Tallas": TALLAS_ROPA},
)
add(
    "Uniforme de servicios generales",
    "camisas-y-chaquetas",
    "p03-5.png",
    "Conjunto de blusa y pantalón para personal de servicios generales, aseo y áreas de salud, en varios colores.",
    "<p>Conjunto de dos piezas para personal de servicios generales, aseo, cocinas y áreas asistenciales. La blusa tiene cuello "
    "en V y vivos de contraste; el pantalón es de tiro cómodo con elástico en la cintura.</p>"
    "<p>La tela es fresca y de secado rápido, pensada para jornadas de movimiento constante y lavados frecuentes. "
    "Disponible en varios colores para diferenciar equipos o áreas.</p>",
    {"Tipo": "Conjunto de dos piezas", "Tallas": TALLAS_ROPA, "Colores": "Varios colores disponibles"},
)

# ---------------------------------------------------------------- jeans
add(
    "Jean caballero 14 onzas",
    "pantalones-y-jeans",
    "p04-5.png",
    "Jean de trabajo para caballero en denim de 14 onzas, la referencia estándar de dotación industrial en tallas 28 a 48.",
    "<p>El jean de 14 onzas es la base de cualquier dotación industrial. Su denim de alto gramaje aguanta el roce contra "
    "estructuras, herramientas y superficies abrasivas mucho más que un jean comercial.</p>"
    "<p>Tiene cinco bolsillos, pretina con presillas reforzadas, cremallera metálica y costuras dobles en tiro y entrepierna. "
    "Se fabrica en tallas 28 a 48, lo que permite cubrir toda la planta con una sola referencia.</p>",
    {"Tela": "Denim 14 onzas", "Tallas": "28 a 48", "Línea": "Caballero"},
)
add(
    "Jean caballero 14 onzas dieléctrico",
    "pantalones-y-jeans",
    "p04-3.png",
    "Jean dieléctrico de 14 onzas sin remaches ni herrajes metálicos expuestos, para labores con riesgo eléctrico.",
    "<p>Jean de trabajo en denim de 14 onzas fabricado sin remaches ni herrajes metálicos expuestos, condición indispensable "
    "cuando el trabajador está cerca de partes energizadas.</p>"
    "<p>Es la referencia que exigen las empresas de energía, telecomunicaciones y mantenimiento eléctrico para cumplir con "
    "el reglamento de instalaciones eléctricas. Mantiene el corte y la resistencia del jean industrial estándar.</p>",
    {"Tela": "Denim 14 onzas", "Característica": "Dieléctrico, sin herrajes metálicos", "Tallas": "28 a 48"},
)
add(
    "Jean caballero licrado",
    "pantalones-y-jeans",
    "p04-4.png",
    "Jean de trabajo con elastano para mayor libertad de movimiento en labores que exigen agacharse y subir escaleras.",
    "<p>Jean de dotación con un porcentaje de elastano que le da recuperación al tejido. La diferencia se nota en tareas donde "
    "el operario se agacha, se arrodilla o sube escaleras muchas veces al día.</p>"
    "<p>Conserva la apariencia del denim clásico y la resistencia necesaria para uso industrial, pero reduce la fatiga y el "
    "desgaste en rodillas y tiro.</p>",
    {"Tela": "Denim con elastano", "Tallas": "28 a 48", "Línea": "Caballero"},
)
add(
    "Jean caballero negro 14 onzas",
    "pantalones-y-jeans",
    "p04-2.png",
    "Jean negro de 14 onzas para dotaciones que necesitan una prenda sobria y que disimule manchas de grasa.",
    "<p>Jean de trabajo en denim negro de 14 onzas. El color oscuro disimula manchas de grasa, aceite y polvo, por lo que se "
    "usa mucho en talleres automotrices, mantenimiento mecánico y personal de servicio técnico en calle.</p>"
    "<p>Comparte construcción con el jean índigo: cinco bolsillos, costuras dobles y pretina reforzada, en tallas 28 a 48.</p>",
    {"Tela": "Denim negro 14 onzas", "Tallas": "28 a 48", "Línea": "Caballero"},
)
add(
    "Jean dama strech clásico",
    "pantalones-y-jeans",
    "p04-1.png",
    "Jean de dotación para dama en denim strech con horma clásica, en tallas 6 a 26.",
    "<p>Jean de trabajo con horma femenina real, no una talla adaptada del molde masculino. El denim strech acompaña el "
    "movimiento y evita que la prenda se abra en la cintura al agacharse.</p>"
    "<p>Es la referencia clásica para dotación de dama en planta, laboratorio y logística. Disponible en tallas 6 a 26.</p>",
    {"Tela": "Denim strech", "Tallas": "6 a 26", "Línea": "Dama"},
)
add(
    "Jean dama DR pretina alta slim",
    "pantalones-y-jeans",
    "p04-6.png",
    "Jean de dotación para dama con pretina alta y corte slim, cómodo para jornadas largas de pie o sentada.",
    "<p>Jean de dotación para dama con pretina alta, que evita que la prenda baje al agacharse, y corte slim que no se engancha "
    "en equipos ni escaleras.</p>"
    "<p>Es la opción preferida cuando la jornada combina estar de pie y sentada frente a un puesto de trabajo. "
    "Se fabrica en tallas 6 a 26.</p>",
    {"Tela": "Denim strech", "Corte": "Pretina alta, slim", "Tallas": "6 a 26", "Línea": "Dama"},
)
add(
    "Pantalón en dril cuatro bolsillos",
    "pantalones-y-jeans",
    "p05-3.png",
    "Pantalón en dril de cuatro bolsillos, fresco y liviano, con amplia carta de colores y tallas 28 a 48.",
    "<p>Pantalón de dotación en dril, más liviano y fresco que el jean, con cuatro bolsillos para llevar herramienta menor, "
    "libreta o radio.</p>"
    "<p>Es la referencia habitual en climas cálidos, servicios generales, mantenimiento locativo y empresas que combinan "
    "el pantalón con camisa del mismo color para un uniforme completo. Amplia carta de colores.</p>",
    {"Tela": "Dril", "Bolsillos": "4 bolsillos", "Tallas": "28 a 48", "Colores": "Más de 8 colores disponibles"},
)

# -------------------------------------------------------------- overoles
add(
    "Overol en dril de dos piezas",
    "overoles-y-enterizos",
    "p05-2.png",
    "Conjunto de camisa y pantalón en dril que funciona como overol de dos piezas, más fácil de usar y lavar que un enterizo.",
    "<p>Overol de dos piezas en dril: camisa de manga larga con dos bolsillos y pantalón a juego. Al ser separable resulta "
    "más práctico que un enterizo, porque el trabajador puede quitarse la camisa en descansos y reemplazar solo la pieza "
    "que se desgasta.</p>"
    "<p>Se fabrica en tallas de pantalón 28 a 40 y camisa S a 5XL, con amplia carta de colores para señalizar áreas o contratistas.</p>",
    {"Tela": "Dril", "Tallas pantalón": "28 a 40", "Tallas camisa": TALLAS_ROPA, "Colores": "Más de 8 colores disponibles"},
    gallery=["p05-4.png"],
)
add(
    "Overol en dril enterizo",
    "overoles-y-enterizos",
    "p06-2.png",
    "Overol enterizo en dril con seis bolsillos y tres cremalleras, para mecánica, industria y mantenimiento.",
    "<p>Overol enterizo en dril con seis bolsillos y tres cremalleras. Al cubrir el cuerpo en una sola pieza evita que entre "
    "suciedad, viruta o grasa por la cintura, algo frecuente cuando se trabaja acostado o bajo equipos.</p>"
    "<p>Es la prenda estándar en talleres mecánicos, montajes industriales y mantenimiento de maquinaria. "
    "Disponible en tallas S a 5XL y 36 a 50.</p>",
    {"Tela": "Dril", "Bolsillos": "6 bolsillos, 3 cremalleras", "Tallas": "S a 5XL / 36 a 50"},
)
add(
    "Overol en dril tipo piloto",
    "overoles-y-enterizos",
    "p06-1.png",
    "Overol enterizo tipo piloto con catorce cremalleras, la referencia más completa en almacenamiento y ajuste.",
    "<p>Overol enterizo tipo piloto con catorce cremalleras: bolsillos de pecho, muslo y pierna, más aperturas de ventilación "
    "y ajuste. Es la referencia elegida por técnicos que cargan mucha herramienta menor y necesitan tenerla a mano.</p>"
    "<p>El cierre frontal completo facilita ponérselo y quitárselo sobre la ropa de calle. Tallas S a 5XL y 36 a 50.</p>",
    {"Tela": "Dril", "Cremalleras": "14 cremalleras", "Tallas": "S a 5XL / 36 a 50"},
)
add(
    "Overol descartable para químicos",
    "overoles-y-enterizos",
    "p25-1.png",
    "Traje de protección química descartable con capucha, cremallera frontal y refuerzo en costuras.",
    "<p>Traje de protección descartable con capucha para manipulación de químicos, fumigación y limpieza de derrames. "
    "El refuerzo en costuras evita que el líquido penetre por las uniones, que es el punto débil de los trajes económicos.</p>"
    "<p>Se usa una sola vez y se descarta, lo que elimina el riesgo de arrastrar contaminación a las áreas limpias o a la casa "
    "del trabajador. Tallas M a XXXL.</p>",
    {"Referencia": "A70", "Tipo": "Descartable, un uso", "Tallas": "M, L, XL, XXL y XXXL"},
)
add(
    "Overol microtex descartable con zapatones",
    "overoles-y-enterizos",
    "p25-2.png",
    "Overol multiusos descartable en polietileno con capucha y zapatones incorporados, para trabajo con polvo y particulado.",
    "<p>Overol multiusos descartable en polietileno con cremallera frontal, capucha y zapatones. Protege contra polvo, "
    "material particulado, pintura y salpicaduras leves.</p>"
    "<p>Es la opción económica para visitas a planta, trabajos de pintura, aislamientos y labores donde solo se necesita "
    "una barrera temporal. Tallas M a XXXL.</p>",
    {"Referencia": "BOT3000", "Material": "Polietileno", "Incluye": "Capucha y zapatones", "Tallas": "M, L, XL, XXL y XXXL"},
    gallery=["p25-3.png"],
)
add(
    "Overol fontanero en PVC",
    "overoles-y-enterizos",
    "p31-5.png",
    "Overol tipo peto en PVC 100% impermeable con refuerzo en entrepierna, para trabajo con agua a presión.",
    "<p>Overol tipo peto en PVC totalmente impermeable, con refuerzo en la entrepierna, que es donde primero se rompen "
    "los petos de trabajo húmedo.</p>"
    "<p>Se usa en plantas de proceso, pesca, lavado con agua a presión, acueducto y alcantarillado. Se combina con bota "
    "en PVC y delantal para completar la protección contra líquidos.</p>",
    {"Material": "PVC", "Impermeabilidad": "100% impermeable", "Color": "Amarillo", "Refuerzo": "Entrepierna"},
)

# -------------------------------------------------------------- chalecos
add(
    "Chaleco antifluido reflectivo",
    "chalecos",
    "p05-1.png",
    "Chaleco antifluido con bandas reflectivas y múltiples bolsillos, repele salpicaduras y da alta visibilidad.",
    "<p>Chaleco en tela antifluido con bandas reflectivas y varios bolsillos con cierre. La tela repele salpicaduras de "
    "líquidos, por lo que se usa en áreas de proceso, salud ocupacional y brigadas.</p>"
    "<p>Combina dos funciones en una prenda: visibilidad para circular por zonas de tránsito de maquinaria y almacenamiento "
    "para radio, libreta y elementos de inspección. Tallas S a 5XL.</p>",
    {"Tela": "Antifluido", "Reflectivo": "Bandas reflectivas", "Tallas": TALLAS_ROPA},
)
add(
    "Chaleco en dril tipo periodista",
    "chalecos",
    "p05-6.png",
    "Chaleco en dril tipo periodista con múltiples bolsillos y cierres, para supervisión, ingeniería y trabajo de campo.",
    "<p>Chaleco en dril tipo periodista con bolsillos amplios de parche, bolsillos con cremallera y porta elementos. "
    "Es la prenda de quienes hacen inspección, interventoría o levantamiento de información en campo.</p>"
    "<p>Se usa sobre la camisa de dotación y admite bordado del logo institucional en el pecho y la espalda. "
    "Amplia carta de colores, tallas S a 5XL.</p>",
    {"Tela": "Dril", "Estilo": "Tipo periodista, multibolsillos", "Tallas": TALLAS_ROPA},
)
add(
    "Chaleco reflectivo con cremallera",
    "chalecos",
    "p25-5.png",
    "Chaleco reflectivo en poliéster de 150 g con bandas en H, cremallera plástica y bolsillo frontal.",
    "<p>Chaleco reflectivo en poliéster de 150 gramos con bandas reflectivas en configuración H, que es la que mejor "
    "define la silueta del cuerpo de noche o con poca luz.</p>"
    "<p>La cremallera plástica permite ponérselo y quitárselo rápido sobre cualquier dotación, y el bolsillo frontal sirve "
    "para carné o radio. Talla única ajustable.</p>",
    {"Referencia": "BVOH", "Material": "Poliéster 150 g", "Cierre": "Cremallera plástica", "Talla": "Única"},
)
add(
    "Chaleco reflectivo con velcro",
    "chalecos",
    "p25-4.png",
    "Chaleco reflectivo económico en poliéster de 60 g con cierre en velcro, para visitantes y contratistas.",
    "<p>Chaleco reflectivo liviano en poliéster de 60 gramos con cierre en velcro. Es la opción de mayor rotación para "
    "visitantes, contratistas temporales y dotación de reposición rápida.</p>"
    "<p>El velcro se abre de un tirón, lo que es una ventaja de seguridad si la prenda queda enganchada en un equipo. "
    "Talla única.</p>",
    {"Referencia": "BVOH", "Material": "Poliéster 60 g", "Cierre": "Velcro", "Talla": "Única"},
)

# ------------------------------------------------------------- soldador
SOLDADOR = "Soldadura y pulimiento"
add(
    "Delantal en carnaza para soldador",
    "dotacion-soldador",
    "p28-4.png",
    "Delantal en carnaza de 89 x 57 cm con cinta ajustable, para soldadura y pulimiento.",
    "<p>Delantal en carnaza de 89 x 57 cm con cinta ajustable al cuerpo. La carnaza es la cara interna del cuero: absorbe "
    "la chispa y no se derrite, a diferencia de cualquier tela sintética.</p>"
    "<p>Cubre torso y muslos, la zona que recibe la mayor parte de la proyección cuando se suelda de pie. "
    "Color natural.</p>",
    {"Material": "Carnaza", "Medida": "89 x 57 cm", "Color": "Natural", "Uso": SOLDADOR},
)
add(
    "Delantal en vaqueta para soldador",
    "dotacion-soldador",
    "p28-1.png",
    "Delantal en vaqueta de 89 x 57 cm, más liso y resistente que la carnaza, para soldadura intensiva.",
    "<p>Delantal en vaqueta de 89 x 57 cm con cinta ajustable. La vaqueta es la flor del cuero bovino: superficie más lisa, "
    "mayor resistencia y vida útil más larga que la carnaza, con un costo superior.</p>"
    "<p>Se recomienda para soldadores de jornada completa y procesos con alta proyección de material fundido. "
    "Color amarillo característico.</p>",
    {"Material": "Vaqueta", "Medida": "89 x 57 cm", "Color": "Amarillo", "Uso": SOLDADOR},
)
add(
    "Mangas en carnaza",
    "dotacion-soldador",
    "p28-2.png",
    "Par de mangas en carnaza con hebillas graduables, protegen brazos y antebrazos de chispa y calor radiante.",
    "<p>Par de mangas en carnaza con hebillas graduables que las fijan sin apretar la circulación. Protegen brazo y antebrazo, "
    "la zona más expuesta cuando se suelda con el electrodo por encima del hombro.</p>"
    "<p>Se usan con delantal para cubrir el frente completo sin el calor de una chaqueta entera, algo que se agradece "
    "en climas cálidos.</p>",
    {"Material": "Carnaza", "Ajuste": "Hebillas graduables", "Color": "Natural", "Uso": SOLDADOR},
)
add(
    "Mangas en vaqueta",
    "dotacion-soldador",
    "p28-3.png",
    "Par de mangas en vaqueta con hebillas graduables, de mayor durabilidad que las de carnaza.",
    "<p>Par de mangas en vaqueta con hebillas graduables. Al ser cuero de flor resisten más ciclos de exposición al calor "
    "antes de endurecerse o agrietarse.</p>"
    "<p>Son la opción para talleres donde las mangas se usan todos los días y el reemplazo frecuente resulta más costoso "
    "que invertir en vaqueta. Color amarillo.</p>",
    {"Material": "Vaqueta", "Ajuste": "Hebillas graduables", "Color": "Amarillo", "Uso": SOLDADOR},
)
add(
    "Polainas en carnaza",
    "dotacion-soldador",
    "p29-4.png",
    "Par de polainas en carnaza con hebillas ajustables, cubren empeine y pantorrilla contra escoria y chispa.",
    "<p>Par de polainas en carnaza con hebillas ajustables. Cubren empeine, tobillo y pantorrilla, donde cae la escoria "
    "por gravedad y donde una bota sola no alcanza a proteger.</p>"
    "<p>El faldón sobre el empeine impide que las partículas incandescentes entren por la lengüeta de la bota, "
    "una de las quemaduras más comunes en soldadura.</p>",
    {"Material": "Carnaza", "Ajuste": "Hebillas ajustables", "Color": "Natural", "Uso": SOLDADOR},
)
add(
    "Polainas en vaqueta",
    "dotacion-soldador",
    "p29-1.png",
    "Par de polainas en vaqueta con hebillas ajustables, más resistentes al calor continuo que la carnaza.",
    "<p>Par de polainas en vaqueta con hebillas ajustables, para soldadura y pulimiento intensivo. La vaqueta mantiene "
    "la forma y no se endurece tan rápido con el calor repetido.</p>"
    "<p>Recomendadas para posiciones de soldadura de piso, donde la escoria cae directamente sobre el calzado. "
    "Color amarillo.</p>",
    {"Material": "Vaqueta", "Ajuste": "Hebillas ajustables", "Color": "Amarillo", "Uso": SOLDADOR},
)
add(
    "Pantalón en carnaza para soldador",
    "dotacion-soldador",
    "p29-3.png",
    "Pantalón en carnaza cosido en hilos de alta resistencia, protección completa de piernas en soldadura.",
    "<p>Pantalón en carnaza cosido en hilos de alta resistencia que no se funden con el calor. Protege la pierna completa, "
    "lo que es necesario en soldadura de posición, en espacios confinados o sobre estructuras.</p>"
    "<p>Se usa sobre el pantalón de dotación y se retira al terminar la tarea. Color natural.</p>",
    {"Material": "Carnaza", "Costura": "Hilos de alta resistencia", "Color": "Natural", "Uso": SOLDADOR},
)
add(
    "Pantalón en vaqueta para soldador",
    "dotacion-soldador",
    "p29-2.png",
    "Pantalón en vaqueta cosido en hilos de alta resistencia, para soldadura de alta exigencia.",
    "<p>Pantalón en vaqueta cosido en hilos de alta resistencia, la versión más duradera de la protección de piernas "
    "para soldadura y pulimiento.</p>"
    "<p>Indicado para procesos de alta deposición y para soldadores que trabajan sentados o arrodillados, donde el "
    "pantalón recibe roce y calor al mismo tiempo. Color amarillo.</p>",
    {"Material": "Vaqueta", "Costura": "Hilos de alta resistencia", "Color": "Amarillo", "Uso": SOLDADOR},
)
add(
    "Chaqueta larga para soldador",
    "dotacion-soldador",
    "p30-2.png",
    "Chaqueta larga tipo levantina en vaqueta, cubre desde el cuello hasta debajo de la rodilla.",
    "<p>Chaqueta larga tipo levantina que cubre desde el cuello hasta debajo de la rodilla. Es la protección máxima "
    "para soldadura en altura, sobre andamios o en posiciones donde la chispa cae sobre el cuerpo completo.</p>"
    "<p>Cosida en hilos de alta resistencia, con cuello alto que protege la nuca junto con la capucha. Color amarillo.</p>",
    {"Material": "Vaqueta", "Cobertura": "Cuello a debajo de la rodilla", "Color": "Amarillo", "Uso": SOLDADOR},
)
add(
    "Chaqueta en carnaza para soldador",
    "dotacion-soldador",
    "p30-4.png",
    "Chaqueta en carnaza a la cintura, cosida en hilos de alta resistencia, para soldadura y pulimiento.",
    "<p>Chaqueta en carnaza de largo a la cintura, la referencia más usada en talleres de estructura metálica. "
    "Protege torso y brazos completos con menos calor acumulado que una chaqueta larga.</p>"
    "<p>Cosida en hilos de alta resistencia y con cierre frontal. Color natural.</p>",
    {"Material": "Carnaza", "Costura": "Hilos de alta resistencia", "Color": "Natural", "Uso": SOLDADOR},
)
add(
    "Chaqueta en vaqueta para soldador",
    "dotacion-soldador",
    "p30-1.png",
    "Chaqueta en vaqueta para soldadura y pulimiento, con mayor vida útil que la carnaza.",
    "<p>Chaqueta en vaqueta para soldadura y pulimiento. La superficie lisa del cuero de flor hace que la escoria resbale "
    "en lugar de adherirse, y prolonga la vida de la prenda.</p>"
    "<p>Cosida en hilos de alta resistencia, con cierre frontal y puños ajustados. Color amarillo.</p>",
    {"Material": "Vaqueta", "Costura": "Hilos de alta resistencia", "Color": "Amarillo", "Uso": SOLDADOR},
)
add(
    "Capucha en carnaza para soldador",
    "dotacion-soldador",
    "p30-3.png",
    "Capucha en carnaza que protege cabeza, nuca y hombros debajo de la careta de soldar.",
    "<p>Capucha en carnaza que se usa debajo de la careta de soldar. Protege cabeza, orejas, nuca y hombros de la chispa "
    "que rebota, especialmente al soldar en techo o en espacios cerrados.</p>"
    "<p>Cosida en hilos de alta resistencia y compatible con casco y careta. Color natural.</p>",
    {"Material": "Carnaza", "Cobertura": "Cabeza, nuca y hombros", "Color": "Natural", "Uso": SOLDADOR},
)

# -------------------------------------------- impermeables y cuarto frío
add(
    "Chaqueta para cuarto frío",
    "impermeables-y-cuarto-frio",
    "p26-1.png",
    "Chaqueta térmica con capa interior de guata y exterior semi-impermeable, para cavas y neveras industriales.",
    "<p>Chaqueta con capa interior de guata que retiene el calor corporal y capa externa semi-impermeable que corta el aire "
    "frío y resiste la condensación.</p>"
    "<p>Está pensada para cavas, neveras industriales, plantas de alimentos y laboratorios donde el personal entra y sale "
    "de ambientes fríos varias veces por turno. Tallas S a XL.</p>",
    {"Aislamiento": "Capa interior de guata", "Exterior": "Semi-impermeable", "Tallas": "S, M, L y XL", "Colores": "Blanco y azul"},
)
add(
    "Pantalón para cuarto frío",
    "impermeables-y-cuarto-frio",
    "p26-2.png",
    "Pantalón térmico con capa interior de guata, complemento de la chaqueta para trabajo prolongado en frío.",
    "<p>Pantalón con capa interior de guata y exterior semi-impermeable. Completa el conjunto de cuarto frío: la chaqueta "
    "sola no basta cuando la permanencia en la cava supera unos minutos.</p>"
    "<p>El corte holgado permite usarlo sobre el pantalón de dotación. Tallas S a XL.</p>",
    {"Aislamiento": "Capa interior de guata", "Exterior": "Semi-impermeable", "Tallas": "S, M, L y XL", "Colores": "Blanco y azul"},
)
add(
    "Impermeable en PVC de cuatro piezas",
    "impermeables-y-cuarto-frio",
    "p31-3.png",
    "Traje impermeable en PVC de cuatro piezas con bandas reflectivas: chaqueta, pantalón, capucha y bolso.",
    "<p>Traje impermeable en PVC de cuatro piezas con bandas reflectivas. Incluye chaqueta con capucha, pantalón y bolso "
    "de transporte, lo que permite guardarlo en el vehículo o el puesto de trabajo.</p>"
    "<p>Es el equipo indicado para cuadrillas de energía, obra civil y servicios públicos que trabajan bajo lluvia de noche, "
    "porque suma impermeabilidad y visibilidad. Tallas S a XL.</p>",
    {"Material": "PVC", "Piezas": "4 piezas", "Reflectivo": "Bandas reflectivas", "Tallas": "S, M, L y XL"},
)
add(
    "Impermeable en PVC de dos piezas",
    "impermeables-y-cuarto-frio",
    "p31-1.png",
    "Traje impermeable en PVC de dos piezas con bandas reflectivas, chaqueta con capucha y pantalón.",
    "<p>Traje impermeable en PVC de dos piezas con bandas reflectivas: chaqueta con capucha y pantalón. Es la configuración "
    "más común para dotación de lluvia por su relación entre protección y precio.</p>"
    "<p>El PVC no absorbe agua, así que la prenda no gana peso durante la jornada. Disponible en negro y amarillo, "
    "tallas S a XL.</p>",
    {"Material": "PVC", "Piezas": "2 piezas", "Reflectivo": "Bandas reflectivas", "Tallas": "S, M, L y XL"},
)
add(
    "Capa impermeable en PVC",
    "impermeables-y-cuarto-frio",
    "p31-2.png",
    "Capa tipo poncho en PVC con capucha, protección rápida contra lluvia para desplazamientos.",
    "<p>Capa tipo poncho en PVC con capucha. Se pone y se quita en segundos sobre cualquier dotación, lo que la hace ideal "
    "para desplazamientos, recorridos de inspección y personal de portería.</p>"
    "<p>Ocupa poco espacio plegada, así que se puede llevar permanentemente en el morral o el vehículo. "
    "Disponible en negro y amarillo.</p>",
    {"Material": "PVC", "Tipo": "Capa tipo poncho con capucha", "Colores": "Negro y amarillo"},
)
add(
    "Zapatón de lluvia",
    "impermeables-y-cuarto-frio",
    "p31-4.png",
    "Zapatón impermeable con suela en caucho que se usa sobre el calzado de dotación en días de lluvia.",
    "<p>Zapatón 100% impermeable con suela en caucho que se coloca sobre el calzado de dotación. Evita tener que cambiar "
    "de bota cuando llueve y mantiene seco el zapato de seguridad.</p>"
    "<p>La suela en caucho aporta agarre sobre piso mojado, donde el zapatón plástico simple resbala. Color negro.</p>",
    {"Impermeabilidad": "100% impermeable", "Suela": "Caucho", "Color": "Negro"},
)

# ------------------------------------------ mangas, capuchas, delantales
add(
    "Capucha en jean",
    "mangas-capuchas-delantales",
    "p07-3.png",
    "Capucha industrial en jean con dos botones, protege cabeza y cuello de chispa y esquirlas.",
    "<p>Capucha industrial en jean con dos botones de cierre. El denim frena las esquirlas y la chispa que rebota, y cubre "
    "cabeza, orejas, cuello y parte de los hombros.</p>"
    "<p>Se usa debajo del casco o la careta en labores de pulimiento, esmerilado y soldadura ligera. Talla estándar.</p>",
    {"Tela": "Jean", "Cierre": "2 botones", "Talla": "Estándar"},
)
add(
    "Capucha en dril",
    "mangas-capuchas-delantales",
    "p07-1.png",
    "Capucha industrial en dril con dos botones, liviana y fresca para uso continuo bajo el casco.",
    "<p>Capucha industrial en dril con dos botones. Más liviana y fresca que la de jean, es la que se usa cuando la molestia "
    "principal es el polvo, el sol o el roce, no la chispa.</p>"
    "<p>Protege también el cuello del sudor y facilita la limpieza del casco. Talla estándar.</p>",
    {"Tela": "Dril", "Cierre": "2 botones", "Talla": "Estándar"},
)
add(
    "Capucha licrada",
    "mangas-capuchas-delantales",
    "p07-2.png",
    "Capucha licrada sin botones, ajuste elástico al rostro para trabajo con polvo y ambientes fríos.",
    "<p>Capucha licrada sin botones que se ajusta al rostro por elasticidad. Al no tener aperturas evita el ingreso de polvo "
    "fino, y por eso se usa en molienda, cementeras y manejo de material particulado.</p>"
    "<p>También sirve como capa térmica bajo el casco en clima frío o en cuartos fríos. Talla estándar.</p>",
    {"Tela": "Licra", "Cierre": "Sin botones, ajuste elástico", "Talla": "Estándar"},
)
add(
    "Manga en dril",
    "mangas-capuchas-delantales",
    "p27-3.png",
    "Par de mangas en dril que protegen los brazos de rayos UV, chispa eléctrica, grasas e hidrocarburos.",
    "<p>Par de mangas en dril para proteger los brazos en ambientes con exposición a rayos UV, chispa eléctrica, grasas "
    "e hidrocarburos.</p>"
    "<p>Son la solución cuando el operario necesita mantener la camisa de manga corta por el calor pero no puede dejar "
    "los brazos descubiertos. Talla única.</p>",
    {"Tela": "Dril", "Protege de": "Rayos UV, chispa eléctrica, grasas e hidrocarburos", "Talla": "Única"},
)
add(
    "Manga en jean",
    "mangas-capuchas-delantales",
    "p27-2.png",
    "Par de mangas en jean para actividades con contacto de chispas o proyección de esquirlas.",
    "<p>Par de mangas en jean, con mayor gramaje que las de dril, para actividades donde hay contacto con chispas "
    "o proyección de esquirlas.</p>"
    "<p>Son la opción intermedia entre la manga textil liviana y la manga en carnaza: más protección que el dril, "
    "más movilidad y menos calor que el cuero. Talla única.</p>",
    {"Tela": "Jean", "Protege de": "Chispas y proyección de esquirlas", "Talla": "Única"},
)
add(
    "Manga en franela",
    "mangas-capuchas-delantales",
    "p27-1.png",
    "Par de mangas en franela ambidiestras, protección contra rayos UV para trabajo a cielo abierto.",
    "<p>Par de mangas en franela ambidiestras, pensadas específicamente para bloquear la radiación solar en trabajo "
    "a cielo abierto: obra, agricultura, vías y montaje.</p>"
    "<p>El tejido es elástico y transpirable, así que puede usarse toda la jornada sin acumular calor. Talla única.</p>",
    {"Tela": "Franela", "Protege de": "Rayos UV", "Característica": "Ambidiestra", "Talla": "Única"},
)
add(
    "Delantal en TPU",
    "mangas-capuchas-delantales",
    "p26-4.png",
    "Delantal impermeable en poliuretano antifluido, apto para manipulación de alimentos.",
    "<p>Delantal impermeable en poliuretano termoplástico, antifluido y apto para manipulación de alimentos. "
    "La superficie no porosa se lava y desinfecta sin retener residuos.</p>"
    "<p>Es el delantal que exigen las plantas de alimentos, cocinas industriales y áreas de proceso húmedo con "
    "requisitos sanitarios. Disponible en varios colores para separar zonas.</p>",
    {"Referencia": "BYAPR", "Material": "Poliuretano (TPU)", "Uso": "Antifluido y manipulación de alimentos", "Colores": "6 colores disponibles"},
)
add(
    "Delantal en PVC",
    "mangas-capuchas-delantales",
    "p26-3.png",
    "Delantal impermeable en PVC, flexible y fácil de limpiar, con buena resistencia física.",
    "<p>Delantal impermeable en PVC, suave y flexible, con buena resistencia al rasgado y al roce. Se limpia con un paño "
    "y no absorbe olores.</p>"
    "<p>Es la opción de mayor rotación para lavado de piezas, plantas de proceso, pesca y servicios generales. "
    "Disponible en blanco y amarillo.</p>",
    {"Material": "PVC", "Impermeabilidad": "Impermeable", "Colores": "Blanco y amarillo"},
)

# --------------------------------------------------------------- calzado
add(
    "Bota Ram Classic",
    "calzado-industrial",
    "p34-4.png",
    "Bota de seguridad con puntera resistente al impacto y suela y plantilla en poliuretano, tallas 35 a 45.",
    "<p>Bota de seguridad con puntera resistente al impacto y construcción en poliuretano tanto en suela como en plantilla, "
    "lo que reduce el peso frente a una bota de suela de caucho.</p>"
    "<p>Es la referencia de entrada más equilibrada para dotación general: cumple con la protección de puntera exigida "
    "en industria y obra sin un costo alto. Tallas 35 a 45.</p>",
    {"Puntera": "Resistente al impacto", "Suela": "Poliuretano", "Plantilla": "Poliuretano", "Tallas": "35 a 45"},
)
add(
    "Bota Trooper Light",
    "calzado-industrial",
    "p34-1.png",
    "Bota de seguridad con puntera anatómica contra impacto y compresión, antideslizante y resistente a hidrocarburos.",
    "<p>Bota de seguridad con puntera anatómica que protege contra impacto y compresión, con suela antideslizante "
    "resistente a hidrocarburos y abrasión.</p>"
    "<p>La horma es más ancha en la zona de los dedos que en una bota tradicional, lo que reduce la fatiga en jornadas "
    "de pie prolongadas. Tallas 35 a 46.</p>",
    {"Puntera": "Anatómica, impacto y compresión", "Suela": "Antideslizante, resistente a hidrocarburos", "Tallas": "35 a 46"},
)
add(
    "Bota de seguridad blanca",
    "calzado-industrial",
    "p34-2.png",
    "Bota de seguridad blanca dieléctrica con puntera en composite, para plantas de alimentos y áreas limpias.",
    "<p>Bota de seguridad blanca con suela en poliuretano resistente a hidrocarburos, dieléctrica y con puntera en composite. "
    "Al no tener partes metálicas pasa detectores y no genera puntos de conducción.</p>"
    "<p>El color blanco es requisito en plantas de alimentos, farmacéuticas y áreas limpias, donde permite ver de inmediato "
    "cuándo el calzado necesita lavado. Tallas 35 a 44.</p>",
    {"Puntera": "Composite", "Suela": "Poliuretano, resistente a hidrocarburos", "Característica": "Dieléctrica", "Tallas": "35 a 44"},
)
add(
    "Bota ingeniera dama",
    "calzado-industrial",
    "p34-3.png",
    "Bota de seguridad con horma femenina en cuero y poliuretano, dieléctrica y con puntera metálica.",
    "<p>Bota de seguridad diseñada sobre horma femenina, en cuero y poliuretano, resistente a hidrocarburos, dieléctrica, "
    "con plantilla anatómica y puntera metálica.</p>"
    "<p>Resuelve un problema real de las dotaciones: usar tallas pequeñas de bota masculina causa rozaduras y lesiones "
    "porque el ancho y el arco no corresponden. Tallas 34 a 41.</p>",
    {"Puntera": "Metálica", "Material": "Cuero y poliuretano", "Característica": "Dieléctrica, plantilla anatómica", "Tallas": "34 a 41"},
)
add(
    "Bota Revolution",
    "calzado-industrial",
    "p35-5.png",
    "Botín deportivo de seguridad en lona balística, cuero y caucho, resistente a la abrasión y dieléctrico.",
    "<p>Botín de corte deportivo fabricado en lona balística, cuero y caucho, resistente a la abrasión y dieléctrico. "
    "Pesa menos que una bota de cuero completo y ventila mejor.</p>"
    "<p>Es la referencia preferida por personal técnico que camina mucho durante el turno: mantenimiento, instalaciones "
    "y servicio en campo. Tallas 36 a 44.</p>",
    {"Material": "Lona balística, cuero y caucho", "Característica": "Dieléctrica, resistente a la abrasión", "Tallas": "36 a 44"},
)
add(
    "Bota Solver Pro",
    "calzado-industrial",
    "p35-2.png",
    "Calzado de seguridad en cuero encerado y nobuck resistente al agua y las manchas, para personal administrativo.",
    "<p>Calzado de seguridad en cuero encerado y nobuck siena, resistente al agua y a las manchas. Tiene aspecto de bota "
    "casual pero cumple con los requisitos de protección para circular por planta.</p>"
    "<p>Es la opción para ingenieros, supervisores y personal administrativo que alterna oficina y recorridos de planta "
    "sin cambiar de calzado. Tallas 35 a 46.</p>",
    {"Material": "Cuero encerado y nobuck", "Característica": "Resistente al agua y manchas", "Tallas": "35 a 46"},
)
add(
    "Bota Ram Nitro",
    "calzado-industrial",
    "p35-3.png",
    "Bota con suela en PU, puntera composite y tela externa de lona impermeable con forro interno naranja.",
    "<p>Bota con suela en poliuretano, puntera en composite y exterior en lona impermeable combinada con microfibra. "
    "El forro interno naranja mejora la visibilidad al calzarse y ayuda a identificar la referencia en almacén.</p>"
    "<p>La puntera composite la hace apta para trabajo eléctrico y para sitios con control de metales. Tallas 35 a 45.</p>",
    {"Puntera": "Composite", "Suela": "Poliuretano", "Exterior": "Lona impermeable y microfibra", "Tallas": "35 a 45"},
)
add(
    "Bota Biker Waterproof caballero Brahma",
    "calzado-industrial",
    "p35-4.png",
    "Bota waterproof para caballero con suela de caucho y PU y plantilla anatómica de alto confort.",
    "<p>Bota waterproof de caballero con suela en caucho y poliuretano y plantilla anatómica de alto confort. "
    "El tratamiento impermeable mantiene el pie seco en lluvia y lavado de pisos.</p>"
    "<p>Es una bota de uso mixto: sirve como dotación y como calzado de traslado, muy usada por personal de reparto "
    "y motorizados. Tallas 38 a 43.</p>",
    {"Característica": "Waterproof", "Suela": "Caucho y poliuretano", "Plantilla": "Anatómica", "Tallas": "38 a 43"},
)
add(
    "Bota Biker Waterproof dama Brahma",
    "calzado-industrial",
    "p36-3.png",
    "Bota waterproof para dama con suela de polímeros de alta resistencia y plantilla plana de alta comodidad.",
    "<p>Bota waterproof en horma de dama, con suela de polímeros de alta resistencia, forro en material textil y plantilla "
    "plana de alta comodidad.</p>"
    "<p>Está pensada para jornadas largas de caminata y clima húmedo, con el ajuste correcto para el pie femenino. "
    "Tallas 34 a 39.</p>",
    {"Característica": "Waterproof", "Suela": "Polímeros de alta resistencia", "Tallas": "34 a 39", "Línea": "Dama"},
)
add(
    "Mocasín de dama",
    "calzado-industrial",
    "p36-1.png",
    "Zapato mocasín de dotación para dama en cuero y suela negros, para áreas administrativas y comerciales.",
    "<p>Zapato mocasín de dotación para dama, fabricado en cuero con suela negra. Es el calzado que acompaña el uniforme "
    "administrativo cuando no se requiere protección de puntera.</p>"
    "<p>El diseño sin cordones facilita ponerlo y quitarlo y mantiene una presentación uniforme en equipos de atención "
    "al cliente. Tallas 34 a 40.</p>",
    {"Material": "Cuero", "Suela": "Negra", "Uso": "Dotación administrativa", "Tallas": "34 a 40"},
)
add(
    "Mocasín caballero",
    "calzado-industrial",
    "p36-2.png",
    "Zapato mocasín de dotación para caballero en cuero y suela negros, para personal administrativo.",
    "<p>Zapato mocasín de dotación para caballero en cuero con suela negra, de línea sobria y fácil mantenimiento.</p>"
    "<p>Se usa en dotaciones de portería, conducción, atención al cliente y personal administrativo, donde el requisito "
    "es presentación y no protección mecánica. Tallas 38 a 43.</p>",
    {"Material": "Cuero", "Suela": "Negra", "Uso": "Dotación administrativa", "Tallas": "38 a 43"},
)
add(
    "Bota soldador Terrano",
    "calzado-industrial",
    "p37-3.png",
    "Bota para soldador en cuero natural con costuras antillama, puntera composite, dieléctrica y resistente a altas temperaturas.",
    "<p>Bota para soldador en cuero natural de alta calidad con costuras antillama, suela en poliuretano de inyección directa, "
    "plantilla en PU y puntera en composite.</p>"
    "<p>El caño alto y la ausencia de cordones expuestos evitan que la escoria se aloje en la lengüeta. Es dieléctrica y "
    "resiste hidrocarburos, químicos y altas temperaturas. Tallas 36 a 44.</p>",
    {"Puntera": "Composite", "Costuras": "Antillama", "Característica": "Dieléctrica, resistente a altas temperaturas", "Tallas": "36 a 44"},
)
add(
    "Bota en PVC negra",
    "calzado-industrial",
    "p37-4.png",
    "Bota en PVC negra 100% impermeable con puntera no metálica y diseño ergonómico de inyección directa.",
    "<p>Bota en PVC negra con puntera no metálica y diseño ergonómico de inyección directa, de alta resistencia y "
    "flexibilidad. Es totalmente impermeable.</p>"
    "<p>Es la bota de uso general para agua, barro y lavado de pisos: construcción, acueducto, agricultura y plantas "
    "de proceso. Tallas 38 a 42.</p>",
    {"Material": "PVC", "Puntera": "No metálica", "Impermeabilidad": "100% impermeable", "Tallas": "38 a 42"},
)
add(
    "Bota en PVC blanca",
    "calzado-industrial",
    "p37-2.png",
    "Bota en PVC blanca resistente a ácidos grasos, sangre y detergentes, para plantas de alimentos.",
    "<p>Bota en PVC blanca, impermeable y de alta resistencia a ácidos grasos, sangre, grasas animales, detergentes "
    "y cambios de temperatura.</p>"
    "<p>Es el calzado obligatorio en frigoríficos, plantas de sacrificio, procesamiento de lácteos y cualquier área "
    "con protocolo sanitario estricto. Tallas 38 a 42.</p>",
    {"Material": "PVC", "Resistencia": "Ácidos grasos, sangre, detergentes", "Uso": "Plantas de alimentos", "Tallas": "38 a 42"},
)
add(
    "Bota en PVC amarilla",
    "calzado-industrial",
    "p37-1.png",
    "Bota en PVC bicolor antideslizante, resistente a hidrocarburos, derivados del petróleo y grasas.",
    "<p>Bota inyectada en PVC bicolor, antideslizante y resistente al agua, a hidrocarburos, derivados del petróleo "
    "y grasas vegetales y animales.</p>"
    "<p>El color amarillo aporta visibilidad en zonas de tránsito y es el estándar en obra civil, petróleo y minería "
    "a cielo abierto. Tallas 38 a 42.</p>",
    {"Material": "PVC bicolor", "Resistencia": "Hidrocarburos y derivados del petróleo", "Suela": "Antideslizante", "Tallas": "38 a 42"},
)

# --------------------------------------------------------------- guantes
add(
    "Guante Orange en hilaza con puntos en PVC",
    "guantes-de-seguridad",
    "p08-3.png",
    "Guante en hilaza naranja con puntos en PVC en la palma, mejora el agarre en manipulación general.",
    "<p>Guante tejido en hilaza naranja con puntos de PVC en la palma. Los puntos aumentan la fricción sin encarecer "
    "el guante, así que resuelve bien la manipulación de cajas, sacos y piezas secas.</p>"
    "<p>Es el guante de mayor rotación en bodega y logística por su bajo costo por par. Talla L o única.</p>",
    {"Referencia": "BG1400", "Material": "Hilaza con puntos en PVC", "Uso": "Manipulación general", "Talla": "L / Única"},
)
add(
    "Guante Firt en poliéster con palma en látex rugoso",
    "guantes-de-seguridad",
    "p08-6.png",
    "Guante en poliéster con palma en látex rugoso, buen agarre en superficies húmedas y ambientes de obra.",
    "<p>Guante en poliéster con palma recubierta en látex rugoso. La textura del látex agarra bien incluso cuando la pieza "
    "está mojada, algo que el PVC en puntos no logra.</p>"
    "<p>Se usa en construcción, manejo de material de obra, tubería y estructuras. Tallas 8, 9 y 10.</p>",
    {"Referencia": "BG184", "Material": "Poliéster con palma en látex rugoso", "Tallas": "8, 9 y 10"},
)
add(
    "Guante Rob en hilaza con palma en látex laminado",
    "guantes-de-seguridad",
    "p08-1.png",
    "Guante en hilaza con palma en látex laminado, resistente al roce y con buen agarre en trabajo pesado.",
    "<p>Guante en hilaza con palma en látex laminado. La capa continua de látex protege mejor contra el roce que los puntos "
    "de PVC y aporta cierta resistencia a la humedad.</p>"
    "<p>Es el guante de trabajo pesado en obra, cargue y descargue y manejo de escombro. Talla única.</p>",
    {"Referencia": "BG177R", "Material": "Hilaza con palma en látex laminado", "Talla": "Única"},
)
add(
    "Guante Comfort PU",
    "guantes-de-seguridad",
    "p08-5.png",
    "Guante en poliéster nylon recubierto en poliuretano, delgado y sensible para trabajo de precisión.",
    "<p>Guante en poliéster nylon de alta calidad recubierto en poliuretano. Es delgado y se ajusta como una segunda piel, "
    "por lo que conserva la sensibilidad de los dedos.</p>"
    "<p>Es el guante indicado para ensamble electrónico, inspección de calidad, manipulación de piezas pequeñas y "
    "cualquier tarea donde un guante gruesa estorba. Tallas 7, 8, 9 y 10.</p>",
    {"Referencia": "BG172CH", "Material": "Poliéster nylon recubierto en PU", "Uso": "Trabajo de precisión", "Tallas": "7, 8, 9 y 10"},
)
add(
    "Guante NT con palma en espuma de nitrilo",
    "guantes-de-seguridad",
    "p09-2.png",
    "Guante en poliéster con palma en espuma de nitrilo, agarre en piezas aceitadas y resistencia a grasas.",
    "<p>Guante en poliéster con palma en espuma de nitrilo. La espuma canaliza el aceite y mantiene el agarre en piezas "
    "engrasadas, donde el látex resbala.</p>"
    "<p>Es el guante estándar en mecánica, ensamble automotriz y mantenimiento de maquinaria. Tallas 7, 8, 9 y 10.</p>",
    {"Referencia": "BG174BF", "Material": "Poliéster con palma en espuma de nitrilo", "Uso": "Piezas aceitadas y grasas", "Tallas": "7, 8, 9 y 10"},
)
add(
    "Guante anticorte Cut 7 en HDPE",
    "guantes-de-seguridad",
    "p10-1.png",
    "Guante anticorte en fibra HDPE reforzada con palma en poliuretano, nivel de corte alto para manejo de lámina y vidrio.",
    "<p>Guante en fibra HDPE reforzada, resistente al corte, con palma en poliuretano. La fibra de alto peso molecular "
    "resiste el filo sin necesidad de malla metálica, lo que mantiene la destreza.</p>"
    "<p>Es el guante para manejo de lámina, vidrio, cuchillas y piezas troqueladas. Tallas 8, 9 y 10.</p>",
    {"Referencia": "BG199BA7", "Material": "Fibra HDPE reforzada con palma en PU", "Uso": "Riesgo de corte alto", "Tallas": "8, 9 y 10"},
)
add(
    "Guante anticorte Smooth en HDPE con nitrilo arenado",
    "guantes-de-seguridad",
    "p10-4.png",
    "Guante anticorte en fibra HDPE con palma en nitrilo arenado reforzado, une resistencia al corte y agarre en húmedo.",
    "<p>Guante en fibra HDPE resistente al corte con palma en nitrilo arenado reforzado. Combina protección contra filo "
    "con agarre en superficies húmedas o aceitadas.</p>"
    "<p>Se usa en industria de alimentos, metalmecánica y plantas de proceso donde el riesgo de corte convive con líquidos. "
    "Tallas 8, 9 y 10.</p>",
    {"Referencia": "BG200S", "Material": "Fibra HDPE con palma en nitrilo arenado", "Uso": "Corte y agarre en húmedo", "Tallas": "8, 9 y 10"},
)
add(
    "Guante Meca 3 en nitrilo con interior en jersey",
    "guantes-de-seguridad",
    "p11-4.png",
    "Guante en nitrilo liso con puño abierto e interior en jersey, para manejo de aceites y solventes ligeros.",
    "<p>Guante en nitrilo liso con puño abierto e interior en jersey de algodón. El jersey absorbe el sudor y el nitrilo "
    "bloquea aceites, grasas y solventes ligeros.</p>"
    "<p>El puño abierto facilita quitárselo rápido si entra líquido, y ventila la mano en jornadas largas. "
    "Talla única.</p>",
    {"Referencia": "BG44R", "Material": "Nitrilo liso con interior en jersey", "Puño": "Abierto", "Talla": "Única"},
)
add(
    'Guante desechable NI 10" en nitrilo',
    "guantes-de-seguridad",
    "p12-2.png",
    "Guante desechable en nitrilo calibre 8 mm con textura diamantada, caja por 25 pares.",
    "<p>Guante desechable en nitrilo calibre 8 mm con textura diamantada, que mejora el agarre respecto al nitrilo liso. "
    "Se entrega en caja por 25 pares.</p>"
    "<p>Se usa en talleres, laboratorios y mantenimiento donde el guante se contamina y debe descartarse. "
    "El nitrilo no genera alergia al látex. Tallas M, L y XL.</p>",
    {"Referencia": "BG001DB", "Material": "Nitrilo calibre 8 mm, diamantado", "Presentación": "Caja por 25 pares", "Tallas": "M, L y XL"},
)
add(
    "Guante Calf en vaqueta certificada",
    "guantes-de-seguridad",
    "p13-3.png",
    "Guante en cuero bovino amarillo certificado, resistente al roce y al calor moderado.",
    "<p>Guante en cuero bovino amarillo certificado. El cuero de flor resiste el roce, el calor moderado y el corte por "
    "arrastre mucho mejor que cualquier tejido recubierto.</p>"
    "<p>Es el guante para manejo de estructura, tubería, madera y material con aristas. Talla única.</p>",
    {"Material": "Cuero bovino certificado", "Uso": "Roce y calor moderado", "Talla": "Única"},
)
add(
    "Guante V/C en vaqueta y carnaza extralargo",
    "guantes-de-seguridad",
    "p13-4.png",
    "Guante extralargo en vaqueta y carnaza que protege mano y antebrazo en soldadura y fundición.",
    "<p>Guante extralargo combinando vaqueta en la palma y carnaza en el puño. Protege mano, muñeca y antebrazo, "
    "la zona expuesta cuando se suelda con el brazo extendido.</p>"
    "<p>Es el guante habitual en soldadura, fundición y manejo de piezas calientes. Talla única.</p>",
    {"Material": "Vaqueta y carnaza", "Largo": "Extralargo", "Uso": "Soldadura y piezas calientes", "Talla": "Única"},
)
add(
    'Guante Red 14" en carnaza con refuerzo',
    "guantes-de-seguridad",
    "p14-1.png",
    "Guante en carnaza roja de 14 pulgadas con refuerzo, para soldadura y manejo de material caliente.",
    "<p>Guante en carnaza roja de 14 pulgadas con refuerzo en palma y pulgar, los puntos que primero se abren en un guante "
    "de soldador.</p>"
    "<p>El largo cubre buena parte del antebrazo y se complementa con las mangas en carnaza. Talla única.</p>",
    {"Referencia": "BG3511A", "Material": "Carnaza roja con refuerzo", "Largo": "14 pulgadas", "Talla": "Única"},
)
add(
    'Guante Blue 16" en carnaza con hilos kevlar',
    "guantes-de-seguridad",
    "p14-5.png",
    "Guante en carnaza bicolor de 16 pulgadas con costuras en hilo kevlar, para soldadura de alta temperatura.",
    "<p>Guante en carnaza bicolor de 16 pulgadas con refuerzo de hilos kevlar. El kevlar no se funde con el calor, así que "
    "las costuras no se abren cuando el guante trabaja cerca del arco.</p>"
    "<p>Es la referencia para soldadura de alta deposición y manejo de piezas recién soldadas. Talla única.</p>",
    {"Referencia": "BG3515C", "Material": "Carnaza bicolor con hilos kevlar", "Largo": "16 pulgadas", "Talla": "Única"},
)
add(
    'Guante en carnaza 16"',
    "guantes-de-seguridad",
    "p14-3.png",
    "Guante en carnaza sencilla color natural de 16 pulgadas, protección económica para calor y roce.",
    "<p>Guante en carnaza sencilla color natural de 16 pulgadas. Es la opción económica de guante largo para calor "
    "y roce, con buena relación entre protección y precio por par.</p>"
    "<p>Se usa en soldadura ligera, manejo de escoria, hornos y fundición pequeña. Talla única.</p>",
    {"Material": "Carnaza natural", "Largo": "16 pulgadas", "Talla": "Única"},
)

# ------------------------------------------------------ protección visual
UV_AF = "Filtro UV y tratamiento antiempaño"
add(
    "Gafas de seguridad Capitán lente claro",
    "proteccion-visual-facial",
    "p15-2.png",
    "Gafas de seguridad con marco en PVC y lente claro en policarbonato con filtro UV y antiempaño.",
    "<p>Gafas de seguridad con marco en PVC y lente claro en policarbonato, con tratamiento antiempaño y filtro UV. "
    "El policarbonato resiste el impacto de partículas a alta velocidad sin astillarse.</p>"
    "<p>El lente claro es para trabajo en interiores y con poca luz. Es el modelo de mayor rotación en dotación general.</p>",
    {"Referencia": "BYG001-RBW", "Lente": "Policarbonato claro", "Marco": "PVC", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Capitán lente oscuro",
    "proteccion-visual-facial",
    "p15-1.png",
    "Gafas de seguridad con marco en PVC y lente oscuro en policarbonato con filtro UV y antiempaño.",
    "<p>Versión de lente oscuro de las gafas Capitán, con marco en PVC y policarbonato con antiempaño y filtro UV.</p>"
    "<p>Es para trabajo a cielo abierto: obra, vías, patios de maniobra y cualquier labor con exposición solar directa "
    "donde el lente claro genera fatiga visual.</p>",
    {"Referencia": "BYG001-RBB", "Lente": "Policarbonato oscuro", "Marco": "PVC", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Spy lente claro",
    "proteccion-visual-facial",
    "p15-3.png",
    "Gafas de seguridad con marco y lente en policarbonato claro, monopieza con antiempaño y filtro UV.",
    "<p>Gafas de seguridad monopieza con marco y lente en policarbonato claro, antiempaño y filtro UV. "
    "Al ser una sola pieza no tiene uniones donde entren partículas.</p>"
    "<p>El diseño envolvente cubre también la visión lateral, lo que la hace apta para esmerilado ligero y limpieza "
    "con aire comprimido.</p>",
    {"Referencia": "BYG002WCAF", "Lente": "Policarbonato claro", "Diseño": "Monopieza envolvente", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Spy lente oscuro",
    "proteccion-visual-facial",
    "p15-4.png",
    "Gafas de seguridad monopieza en policarbonato oscuro con antiempaño y filtro UV, para exteriores.",
    "<p>Versión oscura de las gafas Spy, monopieza en policarbonato con antiempaño y filtro UV.</p>"
    "<p>Cubre visión frontal y lateral, y reduce el reflejo en superficies metálicas y concreto claro. "
    "Indicada para trabajo continuo a cielo abierto.</p>",
    {"Referencia": "BYG002WGAF", "Lente": "Policarbonato oscuro", "Diseño": "Monopieza envolvente", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad V30",
    "proteccion-visual-facial",
    "p16-1.png",
    "Gafas de seguridad con marco deportivo en nylon y lente claro en policarbonato con antiempaño y filtro UV.",
    "<p>Gafas de seguridad con marco deportivo en nylon, más liviano y flexible que el PVC, y lente claro en policarbonato "
    "con antiempaño y filtro UV.</p>"
    "<p>El marco flexible se adapta a distintos anchos de rostro y aguanta caídas sin quebrarse, lo que reduce el "
    "reemplazo por rotura.</p>",
    {"Referencia": "25688", "Lente": "Policarbonato claro", "Marco": "Nylon deportivo", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Venom",
    "proteccion-visual-facial",
    "p16-4.png",
    "Gafas de seguridad de marco deportivo en nylon con lente en policarbonato claro, antiempaño y filtro UV.",
    "<p>Gafas de seguridad de marco deportivo en nylon con lente en policarbonato claro, antiempaño y filtro UV. "
    "El puente y las patillas tienen apoyos siliconados que evitan el deslizamiento con el sudor.</p>"
    "<p>Es una buena opción cuando el trabajador se queja de que las gafas se le caen al inclinarse.</p>",
    {"Referencia": "BYG30AFW", "Lente": "Policarbonato claro", "Marco": "Nylon deportivo", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Eco lente claro",
    "proteccion-visual-facial",
    "p16-2.png",
    "Gafas de seguridad económicas de unilente claro en policarbonato con antiempaño y filtro UV.",
    "<p>Gafas de seguridad de unilente claro en policarbonato con antiempaño y filtro UV. Es la referencia de menor costo "
    "por unidad, pensada para entrega masiva y reposición frecuente.</p>"
    "<p>Se usa mucho para visitantes, contratistas y stock de reposición en planta.</p>",
    {"Referencia": "BYG002W", "Lente": "Unilente policarbonato claro", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Eco lente oscuro",
    "proteccion-visual-facial",
    "p16-3.png",
    "Gafas de seguridad económicas de unilente oscuro en policarbonato con antiempaño y filtro UV.",
    "<p>Versión oscura de las gafas Eco, con unilente en policarbonato, antiempaño y filtro UV.</p>"
    "<p>Cubre la necesidad de protección solar básica en cuadrillas grandes y trabajo temporal a cielo abierto "
    "con un costo mínimo por unidad.</p>",
    {"Referencia": "BYG002W", "Lente": "Unilente policarbonato oscuro", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad X5 marco dual",
    "proteccion-visual-facial",
    "p17-2.png",
    "Gafas de seguridad con marco dual en nylon y espuma en poliuretano, sellan el contorno del ojo.",
    "<p>Gafas de seguridad con marco dual en nylon y espuma en poliuretano en el contorno. La espuma sella contra el rostro "
    "y bloquea el polvo fino que entra por los costados de una gafa convencional.</p>"
    "<p>Es la opción intermedia entre gafa y monogafa: protege más que la primera y es menos calurosa que la segunda.</p>",
    {"Referencia": "352451700154", "Marco": "Dual en nylon con espuma en PU", "Tratamiento": UV_AF},
)
add(
    "Gafas de seguridad Orestes",
    "proteccion-visual-facial",
    "p17-3.png",
    "Gafas de seguridad de marco deportivo bicolor en nylon con lente en policarbonato, antiempaño y filtro UV.",
    "<p>Gafas de seguridad de marco deportivo bicolor en nylon con lente en policarbonato, antiempaño y filtro UV. "
    "El diseño envolvente y el bajo peso las hacen cómodas para uso continuo.</p>"
    "<p>Los colores de contraste ayudan a identificar visualmente el tipo de gafa asignado a cada área.</p>",
    {"Referencia": "GB-9302", "Marco": "Nylon deportivo bicolor", "Lente": "Policarbonato", "Tratamiento": UV_AF},
)
add(
    "Gafas para oxicorte con filtro IR5",
    "proteccion-visual-facial",
    "p17-1.png",
    "Gafas con marco en PVC resistente al calor y lente redondo de 50 mm en policarbonato con filtro IR5.",
    "<p>Gafas con marco en PVC resistente al calor y lente redondo de 50 mm en policarbonato con filtro infrarrojo IR5. "
    "El filtro IR5 es el requerido para oxicorte y soldadura con oxiacetileno.</p>"
    "<p>Una gafa oscura común no sirve para esta tarea: no bloquea la radiación infrarroja que daña la retina. "
    "Los lentes son reemplazables.</p>",
    {"Referencia": "HF 113", "Filtro": "IR5", "Lente": "Policarbonato redondo 50 mm", "Uso": "Oxicorte y oxiacetileno"},
)
add(
    "Monogafa Zex",
    "proteccion-visual-facial",
    "p18-1.png",
    "Monogafa con marco bicolor en TPR siliconado, lente claro con antiempaño y antirrayadura.",
    "<p>Monogafa con marco bicolor en TPR siliconado que sella contra el rostro, con lente claro tratado contra empañamiento "
    "y rayadura.</p>"
    "<p>El sello completo la hace apta para manejo de químicos con riesgo de salpicadura y para ambientes con polvo fino, "
    "donde una gafa abierta no protege.</p>",
    {"Referencia": "352450110001", "Marco": "TPR siliconado", "Lente": "Claro, antiempaño y antirrayadura"},
)
add(
    "Monogafa Hawk",
    "proteccion-visual-facial",
    "p18-4.png",
    "Monogafa con marco transparente en PVC, banda ajustable y lente en policarbonato con antiempaño y filtro UV.",
    "<p>Monogafa con marco transparente en PVC, banda elástica ajustable y lente en policarbonato claro con antiempaño "
    "y filtro UV.</p>"
    "<p>La banda ajustable permite usarla sobre gafas formuladas y bajo el casco, y el marco transparente no reduce "
    "la visión periférica.</p>",
    {"Referencia": "BYG3008", "Marco": "PVC transparente", "Ajuste": "Banda elástica", "Tratamiento": UV_AF},
)
add(
    "Careta para soldar",
    "proteccion-visual-facial",
    "p20-2.png",
    "Careta para soldar con lente frontal y cubierta resistente al calor, protege rostro y cuello del arco eléctrico.",
    "<p>Careta para soldar que protege rostro y cuello de la radiación del arco, la chispa y el calor. La cubierta es "
    "resistente al calor y el lente frontal es reemplazable.</p>"
    "<p>Se ofrece en versión de sombra fija de uso manual y en versión fotosensible, que oscurece automáticamente al "
    "encender el arco y no requiere baterías. Consulte la referencia que necesita.</p>",
    {"Versiones": "Sombra fija manual o fotosensible", "Protege": "Rostro y cuello", "Lente": "Frontal reemplazable"},
)

# ------------------------------------------------ protección respiratoria
add(
    "Media máscara premium siliconada",
    "proteccion-respiratoria",
    "p21-3.png",
    "Media máscara respiradora en silicona premium con doble filtro, sello cómodo para uso prolongado.",
    "<p>Media máscara respiradora en silicona de línea premium para doble filtro. La silicona se adapta al rostro y "
    "mantiene el sello sin presión excesiva, lo que permite usarla varias horas.</p>"
    "<p>Es reutilizable: se cambia solo el filtro según el contaminante. Requiere prueba de ajuste. Tallas S, M y L.</p>",
    {"Referencia": "SM7501", "Material": "Silicona premium", "Tipo": "Media máscara, doble filtro", "Tallas": "S, M y L"},
)
add(
    "Media máscara elastomérica",
    "proteccion-respiratoria",
    "p21-6.png",
    "Media máscara respiradora elastomérica de doble filtro, opción económica y reutilizable.",
    "<p>Media máscara respiradora elastomérica de doble filtro. Es la alternativa económica a la silicona, con el mismo "
    "principio de funcionamiento y filtros intercambiables.</p>"
    "<p>Recomendada para exposiciones intermitentes o cuando se necesita dotar a muchos trabajadores. "
    "Tallas S, M y L.</p>",
    {"Referencia": "BYM410", "Material": "Elastómero", "Tipo": "Media máscara, doble filtro", "Tallas": "S, M y L"},
)
add(
    "Respirador de cara completa premium",
    "proteccion-respiratoria",
    "p21-4.png",
    "Respirador de cara completa en silicona premium, protege vías respiratorias, ojos y rostro a la vez.",
    "<p>Respirador de cara completa en silicona premium. Protege vías respiratorias, ojos y rostro con un solo equipo, "
    "lo que elimina el problema de empañamiento entre gafa y máscara.</p>"
    "<p>Es obligatorio con gases irritantes de ojos y en fumigación con productos de alta toxicidad. Talla única.</p>",
    {"Referencia": "70070709", "Material": "Silicona premium", "Tipo": "Cara completa", "Talla": "Única"},
)
add(
    "Respirador de cara completa",
    "proteccion-respiratoria",
    "p21-5.png",
    "Respirador de cara completa siliconado con visor panorámico en policarbonato, para gases y vapores.",
    "<p>Respirador de cara completa siliconado con visor panorámico en policarbonato. El visor amplio conserva la visión "
    "periférica, importante cuando se trabaja en espacios confinados.</p>"
    "<p>Admite filtros para gases, vapores y material particulado según la tarea. Talla única.</p>",
    {"Referencia": "FSM9800", "Material": "Silicona", "Visor": "Panorámico en policarbonato", "Talla": "Única"},
)
add(
    "Respirador desechable con válvula",
    "proteccion-respiratoria",
    "p22-3.png",
    "Respirador desechable preformado con válvula de exhalación frontal, reduce el calor y la humedad interna.",
    "<p>Respirador desechable de diseño preformado con válvula de exhalación frontal. La válvula evacúa el aire caliente "
    "y húmedo, lo que hace más soportable el uso continuo.</p>"
    "<p>La copa rígida mantiene la forma y el sello aunque el trabajador hable o se mueva. Referencia 9319V.</p>",
    {"Referencia": "9319V", "Tipo": "Desechable preformado", "Válvula": "Exhalación frontal"},
)
add(
    "Respirador desechable para partículas",
    "proteccion-respiratoria",
    "p22-2.png",
    "Respirador desechable plegable sin válvula de exhalación, para material particulado y polvo.",
    "<p>Respirador desechable plegable sin válvula de exhalación, para material particulado y polvo. Al plegarse ocupa "
    "poco espacio y se puede llevar en el bolsillo.</p>"
    "<p>Es el respirador de mayor rotación para labores cortas con polvo: barrido, corte de material seco y "
    "manejo de cemento. Referencia 9510-1.</p>",
    {"Referencia": "9510-1", "Tipo": "Desechable plegable", "Válvula": "Sin válvula"},
)
add(
    "Respirador desechable plegable con válvula",
    "proteccion-respiratoria",
    "p22-1.png",
    "Respirador desechable plegable con válvula de exhalación lateral, más cómodo en jornadas largas.",
    "<p>Respirador desechable plegable con válvula de exhalación lateral. Combina la comodidad de guardado del plegable "
    "con la ventilación de la válvula.</p>"
    "<p>Es la opción para jornadas completas con exposición a polvo, donde un respirador sin válvula acumula calor "
    "y humedad. Referencia 2011-1.</p>",
    {"Referencia": "2011-1", "Tipo": "Desechable plegable", "Válvula": "Exhalación lateral"},
)
add(
    "Filtro bayoneta P100 con carbón activado",
    "proteccion-respiratoria",
    "p23-3.png",
    "Filtro de bayoneta de alta eficiencia P100 y P3 con carbón activado, para partículas y olores molestos.",
    "<p>Filtro de bayoneta de alta eficiencia P100 y P3 con carbón activado. Retiene material particulado y además "
    "adsorbe olores molestos, gases ácidos y vapores orgánicos en baja concentración.</p>"
    "<p>Se instala en medias máscaras y respiradores de cara completa con conexión de bayoneta. Referencia P9500C.</p>",
    {"Referencia": "P9500C", "Eficiencia": "P100 y P3", "Adicional": "Carbón activado", "Conexión": "Bayoneta"},
)
add(
    "Filtro para partículas P100 y P3",
    "proteccion-respiratoria",
    "p23-4.png",
    "Filtro de alta eficiencia P100 y P3 para partículas y humos metálicos, indicado para soldadura.",
    "<p>Filtro de alta eficiencia P100 y P3 para filtración de partículas y humos metálicos. Es el filtro correcto para "
    "soldadura, corte y esmerilado de metal.</p>"
    "<p>Los humos metálicos son partículas ultrafinas: un filtro de menor eficiencia las deja pasar. Referencia P8500.</p>",
    {"Referencia": "P8500", "Eficiencia": "P100 y P3", "Uso": "Partículas y humos metálicos"},
)

# --------------------------------------------------- cabeza y protección auditiva
add(
    "Casco industrial EPI",
    "proteccion-cabeza-auditiva",
    "p32-2.png",
    "Casco tipo I clase E dieléctrico en polietileno de alta densidad con ajuste rachet, en siete colores.",
    "<p>Casco industrial tipo I clase E dieléctrico, en polietileno de alta densidad y con ajuste rachet, que se gradúa "
    "con una sola mano sin quitárselo.</p>"
    "<p>La clase E soporta tensión eléctrica, requisito en obra y en cualquier sitio con riesgo eléctrico. "
    "Disponible en siete colores para diferenciar cargos, contratistas o brigadas.</p>",
    {"Referencia": "10-P03", "Tipo": "Tipo I, clase E, dieléctrico", "Material": "Polietileno de alta densidad", "Ajuste": "Rachet"},
)
add(
    "Casco industrial reflectivo",
    "proteccion-cabeza-auditiva",
    "p32-3.png",
    "Casco tipo I clase E dieléctrico en polietileno con reflectivo lateral y ajuste rachet.",
    "<p>Casco industrial tipo I clase E dieléctrico en polietileno, con banda reflectiva lateral y ajuste rachet.</p>"
    "<p>El reflectivo hace visible al trabajador cuando solo se ve su cabeza por encima de una zanja, una baranda o "
    "una pila de material, situación común en obra y logística nocturna.</p>",
    {"Referencia": "1188811", "Tipo": "Tipo I, clase E, dieléctrico", "Reflectivo": "Banda lateral", "Ajuste": "Rachet"},
)
add(
    "Casco industrial para trabajo en alturas",
    "proteccion-cabeza-auditiva",
    "p32-1.png",
    "Casco tipo II clase E dieléctrico en ABS con banda reflectiva trasera, barbuquejo y ajuste rachet.",
    "<p>Casco tipo II clase E dieléctrico en ABS, con banda reflectiva trasera y ajuste rachet. El tipo II protege también "
    "contra impacto lateral, no solo contra caída de objetos.</p>"
    "<p>Es el casco exigido para trabajo en alturas y rescate, donde el golpe puede venir de cualquier dirección. "
    "Incluye puntos de anclaje para barbuquejo.</p>",
    {"Referencia": "BYHSF12", "Tipo": "Tipo II, clase E, dieléctrico", "Material": "ABS", "Uso": "Trabajo en alturas"},
)
add(
    "Casco industrial Samurai con ala enteriza",
    "proteccion-cabeza-auditiva",
    "p32-4.png",
    "Casco tipo I clase E dieléctrico con ala enteriza y barbuquejo, protege del sol en todo el contorno.",
    "<p>Casco industrial tipo I clase E dieléctrico en polietileno, con ala enteriza en todo el contorno y ajuste rachet, "
    "con barbuquejo incluido.</p>"
    "<p>El ala completa da sombra a cara y nuca, por lo que es el casco preferido en obra a cielo abierto, agricultura "
    "y trabajo en vías bajo sol directo.</p>",
    {"Referencia": "CA-P-D21", "Tipo": "Tipo I, clase E, dieléctrico", "Diseño": "Ala enteriza con barbuquejo", "Ajuste": "Rachet"},
)
add(
    "Casco de rescate con doble visor",
    "proteccion-cabeza-auditiva",
    "p33-2.png",
    "Casco de rescate en policarbonato con visor frontal de seguridad y lente oscuro abatible.",
    "<p>Casco de rescate tipo I clase E dieléctrico en policarbonato, con banda reflectiva lateral, visor frontal de "
    "seguridad y lente oscuro abatible.</p>"
    "<p>Reúne casco, gafa y protección solar en un equipo, lo que agiliza la respuesta de brigadas de emergencia "
    "y cuadrillas de rescate.</p>",
    {"Referencia": "BYHSF06I02", "Tipo": "Tipo I, clase E, dieléctrico", "Material": "Policarbonato", "Incluye": "Visor frontal y lente oscuro abatible"},
)
add(
    "Protector auditivo Pino de 3 membranas",
    "proteccion-cabeza-auditiva",
    "p38-1.png",
    "Protector auditivo de inserción de tres membranas, presentación en caja individual.",
    "<p>Protector auditivo de inserción con tres membranas escalonadas que atenúan el ruido de manera progresiva. "
    "Es reutilizable y se entrega en caja individual.</p>"
    "<p>Al no ser de espuma no se contamina tan rápido y se puede lavar, lo que reduce el costo por trabajador "
    "frente al tapón desechable.</p>",
    {"Referencia": "BEG3", "Tipo": "Inserción, 3 membranas", "Presentación": "Caja individual"},
)
add(
    "Protector auditivo Bullet en espuma de PU",
    "proteccion-cabeza-auditiva",
    "p38-2.png",
    "Tapón auditivo de inserción en espuma de poliuretano, presentación en bolsa individual.",
    "<p>Tapón auditivo de inserción en espuma de poliuretano. La espuma se comprime y luego se expande dentro del canal "
    "auditivo, logrando el mejor sello de todos los protectores de inserción.</p>"
    "<p>Se entrega en bolsa individual para mantener la higiene en la entrega masiva. Es desechable.</p>",
    {"Referencia": "BEO", "Tipo": "Inserción en espuma de PU", "Presentación": "Bolsa individual"},
)
add(
    "Protector auditivo Bullet Blue con cordón",
    "proteccion-cabeza-auditiva",
    "p38-3.png",
    "Tapón auditivo en espuma de PU color naranja con cordón, evita pérdidas y contaminación.",
    "<p>Tapón auditivo de inserción en espuma de poliuretano color naranja con cordón. El cordón permite dejarlo colgando "
    "al cuello cuando se sale del área ruidosa, en lugar de guardarlo en el bolsillo.</p>"
    "<p>El color naranja facilita la verificación visual de uso por parte del supervisor. Bolsa individual.</p>",
    {"Referencia": "BEOB", "Tipo": "Inserción en espuma de PU con cordón", "Presentación": "Bolsa individual"},
)
add(
    "Protector auditivo de diadema Ronin",
    "proteccion-cabeza-auditiva",
    "p38-4.png",
    "Protector auditivo tipo copa de diadema con ajuste 360 grados y alta visibilidad.",
    "<p>Protector auditivo tipo copa con diadema de ajuste 360 grados y acabado de alta visibilidad. La copa cubre "
    "la oreja completa y atenúa más que un tapón en ambientes de ruido intenso.</p>"
    "<p>Es la opción para trabajadores que entran y salen del área ruidosa, porque se pone y se quita en un segundo "
    "sin higiene comprometida.</p>",
    {"Referencia": "BYHF601", "Tipo": "Copa con diadema", "Ajuste": "360 grados", "Característica": "Alta visibilidad"},
)
add(
    "Protector auditivo F700 para casco",
    "proteccion-cabeza-auditiva",
    "p38-5.png",
    "Protector auditivo tipo copa acoplable al casco, con ajuste anatómico.",
    "<p>Protector auditivo tipo copa que se acopla directamente a las ranuras laterales del casco, con ajuste anatómico.</p>"
    "<p>Resuelve la incompatibilidad entre casco y protector de diadema: al ir montado en el casco no desplaza el "
    "arnés ni rompe el sello sobre la oreja.</p>",
    {"Referencia": "BYHF606-1", "Tipo": "Copa acoplable a casco", "Ajuste": "Anatómico"},
)

# --------------------------------------------------------- trabajo en alturas
add(
    "Punto de anclaje móvil",
    "trabajo-en-alturas",
    "p39-5.png",
    "Adaptador de anclaje móvil con argolla metálica, crea un punto de anclaje certificado sobre estructura.",
    "<p>Adaptador de anclaje móvil con argolla metálica que permite crear un punto de anclaje sobre viga, columna o "
    "estructura donde no existe uno fijo.</p>"
    "<p>Es el primer elemento del sistema contra caídas: sin un anclaje adecuado, el arnés y la eslinga no protegen. "
    "Referencia BYB022.</p>",
    {"Referencia": "BYB022", "Tipo": "Anclaje móvil con argolla metálica", "Uso": "Trabajo en alturas"},
)
add(
    "Eslinga de restricción con absorbedor",
    "trabajo-en-alturas",
    "p39-1.png",
    "Eslinga sencilla regulable de 1,8 m con absorbedor de impacto, limita la fuerza de frenado en una caída.",
    "<p>Eslinga sencilla regulable de 1,8 metros con absorbedor de impacto. El absorbedor se despliega durante la caída "
    "y reduce la fuerza que llega al cuerpo a niveles tolerables.</p>"
    "<p>Se conecta entre la argolla dorsal del arnés y el punto de anclaje. Referencia BYB029MAD.</p>",
    {"Referencia": "BYB029MAD", "Longitud": "1,8 m regulable", "Incluye": "Absorbedor de impacto"},
)
add(
    "Eslinga de restricción en Y",
    "trabajo-en-alturas",
    "p39-3.png",
    "Eslinga doble regulable de 1,80 m con absorbedor de impacto, permite desplazarse sin quedar desconectado.",
    "<p>Eslinga doble en Y regulable de 1,80 metros con absorbedor de impacto, gancho de 3/4 y dos ganchos de 1/8.</p>"
    "<p>Los dos ramales permiten avanzar por una estructura conectando uno antes de liberar el otro, de modo que el "
    "trabajador nunca queda sin protección durante el desplazamiento.</p>",
    {"Referencia": "IV8601-EV8601", "Longitud": "1,80 m regulable", "Tipo": "Doble en Y con absorbedor", "Ganchos": "1 de 3/4 y 2 de 1/8"},
)
add(
    "Eslinga de posicionamiento",
    "trabajo-en-alturas",
    "p39-4.png",
    "Eslinga sencilla regulable de 1,20 m con ganchos pequeños, mantiene al trabajador en posición de trabajo.",
    "<p>Eslinga sencilla regulable de 1,20 metros con ganchos pequeños, para posicionamiento en el puesto de trabajo. "
    "Sujeta al trabajador contra la estructura y le libera las manos.</p>"
    "<p>No sustituye la eslinga con absorbedor: el posicionamiento se usa junto con un sistema de detención de caídas. "
    "Referencia BYB027.</p>",
    {"Referencia": "BYB027", "Longitud": "1,20 m regulable", "Uso": "Posicionamiento en el puesto de trabajo"},
)
add(
    "Arrestador de caída para cuerda",
    "trabajo-en-alturas",
    "p41-5.png",
    "Freno de doble bloqueo para cuerda de 14 a 16 mm, se desplaza con el usuario y bloquea ante una caída.",
    "<p>Arrestador de caída con freno de doble bloqueo para cuerda de 14 a 16 milímetros. Se desliza libremente cuando "
    "el trabajador sube o baja y se bloquea de inmediato ante una aceleración.</p>"
    "<p>El doble bloqueo evita la apertura accidental, que es la falla más peligrosa en este equipo. "
    "Referencia BYB019.</p>",
    {"Referencia": "BYB019", "Compatibilidad": "Cuerda de 14 a 16 mm", "Tipo": "Freno de doble bloqueo"},
)
add(
    "Arrestador de caída con eslinga incorporada",
    "trabajo-en-alturas",
    "p41-3.png",
    "Freno para línea de vida con eslinga y gancho incorporados, listo para conectar al arnés.",
    "<p>Arrestador de caída con eslinga y gancho pequeño incorporados. Al venir preensamblado se elimina el riesgo de "
    "conexión incorrecta entre freno y eslinga.</p>"
    "<p>Es la configuración recomendada cuando el equipo lo usan varios trabajadores o cuadrillas rotativas.</p>",
    {"Tipo": "Freno con eslinga incorporada", "Incluye": "Gancho pequeño", "Uso": "Ascenso y descenso en línea de vida"},
)
add(
    "Línea de vida en poliéster de 16 mm",
    "trabajo-en-alturas",
    "p41-4.png",
    "Cuerda de 16 mm en poliéster de alta calidad, soporta una carga mínima de 5.000 libras en ensayos de caída.",
    "<p>Línea de vida en cuerda de 16 milímetros de poliéster de alta calidad, capaz de soportar una carga mínima de "
    "5.000 libras en ensayos de caída dinámica y estática.</p>"
    "<p>El poliéster resiste mejor la abrasión y la radiación UV que el nylon, lo que importa cuando la línea queda "
    "instalada a la intemperie.</p>",
    {"Diámetro": "16 mm", "Material": "Poliéster de alta calidad", "Resistencia": "Mínimo 5.000 lb en ensayo de caída"},
)
add(
    "Kit de línea de vida",
    "trabajo-en-alturas",
    "p41-1.png",
    "Kit con contrapeso, línea de vida, gancho soporte elevador y bolsa de lona, listo para instalar.",
    "<p>Kit de línea de vida que incluye contrapeso, línea de vida, gancho soporte elevador y bolsa de lona para "
    "transporte y almacenamiento.</p>"
    "<p>Es la solución para trabajos temporales en altura donde no hay línea de vida instalada: llega completo y "
    "se monta sin obra adicional.</p>",
    {"Incluye": "Contrapeso, línea de vida, gancho soporte elevador y bolsa de lona", "Uso": "Instalación temporal"},
)

data = {"categories": CATEGORIES, "products": PRODUCTS}

with open(os.path.join(OUT, "catalog.json"), "w", encoding="utf-8") as fh:
    json.dump(data, fh, ensure_ascii=False, indent=1)

by_cat = {}
for product in PRODUCTS:
    by_cat[product["cat"]] = by_cat.get(product["cat"], 0) + 1

print(f"categorias={len(CATEGORIES)} productos={len(PRODUCTS)}")
for category in CATEGORIES:
    print(f"  {category['slug']:<32} {by_cat.get(category['slug'], 0)}")

missing = [p["img"] for p in PRODUCTS if not os.path.exists(os.path.join(OUT, "img", p["img"]))]
missing += [g for p in PRODUCTS for g in p["gallery"] if not os.path.exists(os.path.join(OUT, "img", g))]
print("imagenes faltantes:", missing or "ninguna")
