import qrcode
from PIL import Image, ImageDraw, ImageFont
from fpdf import FPDF

# Datos de ejemplo (puedes leer de CSV si quieres)
unidades = [
    {"id_unidad": 3, "placa": "PUH0688"},
    {"id_unidad": 4, "placa": "PUH0721"},
    {"id_unidad": 5, "placa": "PAA4065"},
    {"id_unidad": 6, "placa": "PAZQ0506"},
    {"id_unidad": 7, "placa": "PZZ0309"},
    {"id_unidad": 8, "placa": "PZU0391"},
    {"id_unidad": 9, "placa": "PAB6013"},
    {"id_unidad": 10, "placa": "PUC0946"},
    {"id_unidad": 11, "placa": "PAC7917"},
    {"id_unidad": 12, "placa": "PAC3160"},
    {"id_unidad": 13, "placa": "TAA1440"},
    {"id_unidad": 14, "placa": "PAA9357"},
    {"id_unidad": 15, "placa": "PAB6444"},
    {"id_unidad": 16, "placa": "PAU0480"},
    {"id_unidad": 17, "placa": "EAI0493"},
    {"id_unidad": 18, "placa": "PAA9642"},
    {"id_unidad": 19, "placa": "PAC9156"},
    {"id_unidad": 20, "placa": "PZQ0576"},
    {"id_unidad": 21, "placa": "PAC1026"},
]

# Cargar el logo
logo = Image.open("logo.png")

# Crear PDF
pdf = FPDF()
pdf.set_auto_page_break(auto=True, margin=15)

for unidad in unidades:
    # Crear QR
    qr = qrcode.QRCode(
        version=4,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=10,
        border=4,
    )
    qr.add_data(str(unidad["id_unidad"]))
    qr.make(fit=True)

    qr_img = qr.make_image(fill_color="black", back_color="white").convert('RGB')

    # Pegar el logo
    qr_width, qr_height = qr_img.size
    factor = 4
    logo_resized = logo.resize((qr_width // factor, qr_height // factor), Image.Resampling.LANCZOS)
    pos = ((qr_width - logo_resized.width) // 2, (qr_height - logo_resized.height) // 2)
    qr_img.paste(logo_resized, pos, mask=logo_resized if logo_resized.mode == 'RGBA' else None)

    # Crear nueva imagen con espacio para texto debajo
    total_height = qr_height + 50
    final_img = Image.new("RGB", (qr_width, total_height), "white")
    final_img.paste(qr_img, (0, 0))

    # Escribir la placa debajo
    draw = ImageDraw.Draw(final_img)
    try:
        font = ImageFont.truetype("arial.ttf", 28)  # usa una fuente de sistema
    except:
        font = ImageFont.load_default()
    text = unidad["placa"]
    bbox = draw.textbbox((0, 0), text, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]

    text_x = (qr_width - text_width) // 2
    draw.text((text_x, qr_height + 10), text, fill="black", font=font)

    # Guardar temporalmente
    image_path = f"qr_{unidad['id_unidad']}.png"
    final_img.save(image_path)

    # Añadir al PDF
    pdf.add_page()
    pdf.set_font("Arial", "B", 16)
    pdf.image(image_path, x=40, y=30, w=130)

# Guardar PDF final
pdf.output("qrs_con_placas.pdf")
print("PDF generado exitosamente ✅")
