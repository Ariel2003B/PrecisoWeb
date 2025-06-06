import qrcode
from PIL import Image, ImageDraw, ImageFont

# URL a codificar
url = "https://www.youtube.com/@PrecisoGPS"

# Cargar logo
logo = Image.open("logo.png")

# Crear QR
qr = qrcode.QRCode(
    version=4,
    error_correction=qrcode.constants.ERROR_CORRECT_H,
    box_size=15,
    border=2,
)
qr.add_data(url)
qr.make(fit=True)

qr_img = qr.make_image(fill_color="black", back_color="white").convert('RGBA')

# Redimensionar logo
qr_width, qr_height = qr_img.size
factor = 4
logo_resized = logo.resize((qr_width // factor, qr_height // factor), Image.LANCZOS)
if logo_resized.mode != 'RGBA':
    logo_resized = logo_resized.convert('RGBA')

# Pegar el logo en el centro
pos = ((qr_width - logo_resized.width) // 2, (qr_height - logo_resized.height) // 2)
combined = Image.new("RGBA", qr_img.size)
combined.paste(qr_img, (0, 0))
combined.paste(logo_resized, pos, mask=logo_resized)
combined = combined.convert("RGB")

# Añadir texto debajo del QR
total_height = qr_height + 60
final_img = Image.new("RGB", (qr_width, total_height), "white")
final_img.paste(combined, (0, 0))
draw = ImageDraw.Draw(final_img)

try:
    font = ImageFont.truetype("arial.ttf", 20)
except:
    font = ImageFont.load_default()

text = "YouTube - @PrecisoGPS"
bbox = draw.textbbox((0, 0), text, font=font)
text_width = bbox[2] - bbox[0]
text_x = (qr_width - text_width) // 2
draw.text((text_x, qr_height + 10), text, fill="black", font=font)

# Guardar como imagen
final_img.save("qr_youtube_precisogps.png")
print("QR generado exitosamente ✅")
