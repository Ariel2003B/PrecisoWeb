import qrcode
from PIL import Image, ImageDraw, ImageFont
from fpdf import FPDF

# Datos de ejemplo
unidades = [
    {"id_unidad": 3, "placa": "PUH0688 (49/0349)"},
    {"id_unidad": 4, "placa": "PUH0721 (50/0350)"},
    {"id_unidad": 5, "placa": "PAA4065 (51/0351)"},
    {"id_unidad": 6, "placa": "PAZQ0506 (52/0352)"},
    {"id_unidad": 7, "placa": "PZZ0309 (53/0353)"},
    {"id_unidad": 8, "placa": "PZU0391 (54/0354)"},
    {"id_unidad": 9, "placa": "PAB6013 (55/0355)"},
    {"id_unidad": 10, "placa": "PUC0946 (56/0356)"},
    {"id_unidad": 11, "placa": "PAC7917 (57/0357)"},
    {"id_unidad": 12, "placa": "PAC3160 (59/0359)"},
    {"id_unidad": 13, "placa": "TAA1440 (60/0360)"},
    {"id_unidad": 14, "placa": "PAA9357 (61/0361)"},
    {"id_unidad": 15, "placa": "PAB6444 (62/0362)"},
    {"id_unidad": 16, "placa": "PAU0480 (63/0363)"},
    {"id_unidad": 17, "placa": "EAI0493 (64/0364)"},
    {"id_unidad": 18, "placa": "PAA9642 (65/0365)"},
    {"id_unidad": 19, "placa": "PAC9156 (66/0366)"},
    {"id_unidad": 20, "placa": "PZQ0576 (67/0367)"},
    {"id_unidad": 21, "placa": "PAC1026 (68/0368)"},
]

# Cargar el logo
logo = Image.open("logo.png")
# Cargar el logo

# Crear PDF
pdf = FPDF()
pdf.set_auto_page_break(auto=True, margin=15)

for unidad in unidades:
    # Crear QR
    qr = qrcode.QRCode(
        version=4,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=15,  # Aumentar para mejor resolución
        border=2,
    )
    qr.add_data(str(unidad["id_unidad"]))
    qr.make(fit=True)

    qr_img = qr.make_image(fill_color="black", back_color="white").convert('RGBA')

    # Pegar el logo en el centro del QR
    qr_width, qr_height = qr_img.size
    factor = 4 # Reducir el tamaño del logo
    logo_resized = logo.resize((qr_width // factor, qr_height // factor), Image.LANCZOS)

    if logo_resized.mode != 'RGBA':
        logo_resized = logo_resized.convert('RGBA')

    pos = ((qr_width - logo_resized.width) // 2, (qr_height - logo_resized.height) // 2)

    combined = Image.new("RGBA", qr_img.size)
    combined.paste(qr_img, (0, 0))
    combined.paste(logo_resized, pos, mask=logo_resized)
    combined = combined.convert("RGB")

    # Crear imagen con espacio adicional para la caja negra con texto
    total_height = qr_height + 60  # Espacio adicional para el texto
    final_img = Image.new("RGB", (qr_width, total_height), "white")
    final_img.paste(combined, (0, 0))

    draw = ImageDraw.Draw(final_img)
    try:
        font = ImageFont.truetype("arial.ttf", 18)  # Letra más pequeña para mayor claridad
    except:
        font = ImageFont.load_default()

    # Dibujar un rectángulo negro con bordes redondeados
    rect_height = 35
    rect_width = qr_width - 20
    rect_x0 = 10
    rect_y0 = qr_height + 5
    rect_x1 = rect_x0 + rect_width
    rect_y1 = rect_y0 + rect_height

    draw.rectangle(
        [(rect_x0, rect_y0), (rect_x1, rect_y1)],
        fill="black",
        outline=None
    )

    # Texto TRANSMETROPOLI y PLACA dentro del rectángulo negro
    text1 = f"TRANSMETROPOLI - {unidad['placa']}"
    bbox1 = draw.textbbox((0, 0), text1, font=font)
    text_width1 = bbox1[2] - bbox1[0]
    text_x1 = (qr_width - text_width1) // 2
    draw.text((text_x1, rect_y0 + 5), text1, fill="white", font=font)

    # Añadir un borde negro grueso alrededor del QR Code
    bordered_width = final_img.width + 20
    bordered_height = final_img.height + 20
    bordered_image = Image.new("RGB", (bordered_width, bordered_height), "black")
    bordered_image.paste(final_img, (10, 10))

    # Guardar imagen temporalmente
    image_path = f"qr_{unidad['id_unidad']}.png"
    bordered_image.save(image_path)

    # Añadir la imagen al PDF
    pdf.add_page()
    pdf.set_font("Arial", "B", 16)
    pdf.image(image_path, x=40, y=30, w=100)

# Guardar PDF final
pdf.output("qrs_con_placas_transmetropoli.pdf")
print("PDF generado exitosamente ✅")