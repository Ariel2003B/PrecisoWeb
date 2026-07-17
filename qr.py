import qrcode
from PIL import Image, ImageDraw, ImageFont
from fpdf import FPDF
import mysql.connector

# =========================
# 1) TU LISTA DE PLACAS (INPUT) - SOLO PLACA
# =========================
placas_input = [
    "PUK0822",
    "PAE1780",
    "PAE3381",
    "PAE2179",
    "PAE3370",
    "PAE2834",
    "PAE1816",
    "PAC8868",
    "PAA6714",
    "PAB6456",
    "PAC5437",
    "PAC4923",
    "PAE3299",
    "PAA9073",
    "PAA9617",
    "PAB5909",
    "PAB6066",
    "PAC3398",
    "PAB6495",
    "PAC9840",
    "PAE1792",
    "PAB3803",
    "PAE2202",
    "PAC7895",
    "PAE1867",
    "PAC8981",
    "PAB9381",
    "PAE3291",
    "PAC1994",
    "PAE1868",
    "PAC3234",
    "PAB2027",

]

# Limpieza por si copias con espacios
placas_input = [p.strip().upper() for p in placas_input if p and p.strip()]

# =========================
# 2) CONEXIÓN A BD (MySQL)
# =========================
DB_HOST = "132.148.176.238"
DB_NAME = "dbPrecisoGps"
DB_USER = "precisogps"
DB_PASS = "Preciso2024!"

cnx = mysql.connector.connect(
    host=DB_HOST,
    user=DB_USER,
    password=DB_PASS,
    database=DB_NAME
)
cur = cnx.cursor(dictionary=True)

# =========================
# 3) TRAER UNIDADES POR PLACA
# =========================
placeholders = ", ".join(["%s"] * len(placas_input))

sql = f"""
SELECT
  id_unidad,
  placa,
  numero_habilitacion
FROM unidades
WHERE placa IN ({placeholders})
ORDER BY id_unidad ASC
"""

cur.execute(sql, placas_input)
rows = cur.fetchall()

cur.close()
cnx.close()

# Agrupar por placa (por si hay duplicadas)
por_placa = {}
for r in rows:
    key = (r["placa"] or "").strip().upper()
    por_placa.setdefault(key, []).append(r)

# Mantener orden y detectar faltantes
unidades = []
no_encontradas = []
duplicadas = []

for p in placas_input:
    if p not in por_placa:
        no_encontradas.append(p)
        continue

    # si hay más de un registro con la misma placa, lo reportamos
    if len(por_placa[p]) > 1:
        duplicadas.append((p, [x["id_unidad"] for x in por_placa[p]]))

    # usamos el primero (id menor por ORDER BY)
    r = por_placa[p][0]

    unidades.append({
        "id_unidad": r["id_unidad"],
        "placa": r["placa"],
        "numero_habilitacion": r["numero_habilitacion"],
        # TEXTO QUE VA EN LA ETIQUETA (elige uno):
        "placa_texto": f"{r['placa']} ({r['numero_habilitacion']})" if r["numero_habilitacion"] else f"{r['placa']}"
        # Si quieres SOLO PLACA, usa:
        # "placa_texto": f"{r['placa']}"
    })

if no_encontradas:
    print("⚠️ Placas NO encontradas en BD:")
    for p in no_encontradas:
        print(" -", p)

if duplicadas:
    print("⚠️ Placas duplicadas en BD (se usó el ID más pequeño):")
    for p, ids in duplicadas:
        print(f" - {p}: {ids}")

if not unidades:
    raise SystemExit("No se encontró ninguna placa. No se generó PDF.")

# =========================
# 4) GENERAR QR + PDF
# =========================
logo = Image.open("logo.png")

pdf = FPDF()
pdf.set_auto_page_break(auto=True, margin=15)

for unidad in unidades:
    qr = qrcode.QRCode(
        version=4,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=15,
        border=2,
    )
    qr.add_data(str(unidad["id_unidad"]))
    qr.make(fit=True)

    qr_img = qr.make_image(fill_color="black", back_color="white").convert("RGBA")

    qr_width, qr_height = qr_img.size
    factor = 4
    logo_resized = logo.resize((qr_width // factor, qr_height // factor), Image.LANCZOS)

    if logo_resized.mode != "RGBA":
        logo_resized = logo_resized.convert("RGBA")

    pos = ((qr_width - logo_resized.width) // 2, (qr_height - logo_resized.height) // 2)

    combined = Image.new("RGBA", qr_img.size)
    combined.paste(qr_img, (0, 0))
    combined.paste(logo_resized, pos, mask=logo_resized)
    combined = combined.convert("RGB")

    total_height = qr_height + 60
    final_img = Image.new("RGB", (qr_width, total_height), "white")
    final_img.paste(combined, (0, 0))

    draw = ImageDraw.Draw(final_img)
    try:
        font = ImageFont.truetype("arial.ttf", 18)
    except:
        font = ImageFont.load_default()

    rect_height = 35
    rect_width = qr_width - 20
    rect_x0 = 10
    rect_y0 = qr_height + 5
    rect_x1 = rect_x0 + rect_width
    rect_y1 = rect_y0 + rect_height

    draw.rectangle([(rect_x0, rect_y0), (rect_x1, rect_y1)], fill="black")

    text1 = f"YARUQUI - {unidad['placa_texto']}"
    bbox1 = draw.textbbox((0, 0), text1, font=font)
    text_width1 = bbox1[2] - bbox1[0]
    text_x1 = (qr_width - text_width1) // 2
    draw.text((text_x1, rect_y0 + 5), text1, fill="white", font=font)

    bordered_width = final_img.width + 20
    bordered_height = final_img.height + 20
    bordered_image = Image.new("RGB", (bordered_width, bordered_height), "black")
    bordered_image.paste(final_img, (10, 10))

    image_path = f"qr_{unidad['id_unidad']}.png"
    bordered_image.save(image_path)

    pdf.add_page()
    pdf.set_font("Arial", "B", 16)
    pdf.image(image_path, x=40, y=30, w=100)

pdf_name = "qrs_con_placas_yaruqui.pdf"
pdf.output(pdf_name)
print(f"PDF generado exitosamente ✅ -> {pdf_name}")
