import qrcode
from PIL import Image, ImageDraw, ImageFont
from fpdf import FPDF

# Datos de ejemplo
unidades = [
    # {"id_unidad": 3, "placa": "PUH0688 (49/0349)"},
    # {"id_unidad": 4, "placa": "PUH0721 (50/0350)"},
    # {"id_unidad": 5, "placa": "PAA4065 (51/0351)"},
    # {"id_unidad": 6, "placa": "PAZQ0506 (52/0352)"},
    # {"id_unidad": 7, "placa": "PZZ0309 (53/0353)"},
    # {"id_unidad": 8, "placa": "PZU0391 (54/0354)"},
    # {"id_unidad": 9, "placa": "PAB6013 (55/0355)"},
    # {"id_unidad": 10, "placa": "PUC0946 (56/0356)"},
    # {"id_unidad": 11, "placa": "PAC7917 (57/0357)"},
    # {"id_unidad": 12, "placa": "PAC3160 (59/0359)"},
    # {"id_unidad": 13, "placa": "TAA1440 (60/0360)"},
    # {"id_unidad": 14, "placa": "PAA9357 (61/0361)"},
    # {"id_unidad": 15, "placa": "PAB6444 (62/0362)"},
    # {"id_unidad": 16, "placa": "PAU0480 (63/0363)"},
    # {"id_unidad": 17, "placa": "EAI0493 (64/0364)"},
    # {"id_unidad": 18, "placa": "PAA9642 (65/0365)"},
    # {"id_unidad": 19, "placa": "PAC9156 (66/0366)"},
    # {"id_unidad": 20, "placa": "PZQ0576 (67/0367)"},
    # {"id_unidad": 21, "placa": "PAC1026 (68/0368)"},
    # {"id_unidad": 22, "placa": "PZU0195 (03/0303)"},
    # {"id_unidad": 23, "placa": "PAQ0712 (06/0306)"},
    # {"id_unidad": 24, "placa": "PUD0547 (07/0307)"},
    # {"id_unidad": 25, "placa": "PZU0483 (08/0308)"},
    # {"id_unidad": 26, "placa": "PZV0698 (09/0309)"},
#   {"id_unidad": 27, "placa": "PAE1849 (2310)"},
#   {"id_unidad": 28, "placa": "PAE1850 (2311)"},
#   {"id_unidad": 29, "placa": "PAE1854 (2342)"},
#   {"id_unidad": 30, "placa": "PAE1853 (2346)"},
#   {"id_unidad": 31, "placa": "PAB3662 (3742)"},
#   {"id_unidad": 32, "placa": "PAC9210 (3744)"},
#   {"id_unidad": 33, "placa": "PAB3651 (2347)"},
#   {"id_unidad": 34, "placa": "PAB3695 (3737)"},
#   {"id_unidad": 35, "placa": "PAB3661 (3738)"},
#   {"id_unidad": 36, "placa": "PAB3685 (3739)"},
#   {"id_unidad": 37, "placa": "PAB3660 (3740)"},
#   {"id_unidad": 38, "placa": "PCX7184 (3741)"},
#   {"id_unidad": 39, "placa": "PAB4483 (3743)"}
    
#   {"id_unidad": 40, "placa": "PAB6135 (29/0908)"},
#   {"id_unidad": 41, "placa": "PAC8833 (35/0916)"},
#   {"id_unidad": 42, "placa": "PAB4162 (07/3806)"},
#   {"id_unidad": 43, "placa": "PUH0413 (13/0939)"},
#   {"id_unidad": 44, "placa": "PAC7116 (30/0936)"},
#   {"id_unidad": 45, "placa": "PAC2223 (01/0934)"},
#   {"id_unidad": 46, "placa": "PAC5220 (05/0918)"},
#   {"id_unidad": 47, "placa": "PAB8651 (15/0923)"},
#   {"id_unidad": 48, "placa": "PAC1838 (14/0940)"},
#   {"id_unidad": 49, "placa": "PAC7944 (10/0925)"},
#   {"id_unidad": 50, "placa": "PAC4117 (16/0909)"},
#   {"id_unidad": 51, "placa": "PAC3683 (03/0935)"},
#   {"id_unidad": 52, "placa": "PAC3124 (12/0911)"},
#   {"id_unidad": 53, "placa": "PAC3207 (06/0933)"},
#   {"id_unidad": 54, "placa": "PAC5668 (23/0929)"},
#   {"id_unidad": 55, "placa": "PAC7786 (09/0931)"},
#   {"id_unidad": 56, "placa": "PAE3721 (26/0926)"},
#   {"id_unidad": 57, "placa": "PAC3908 (19/0924)"},
#   {"id_unidad": 58, "placa": "PAC8458 (08/0922)"},
#   {"id_unidad": 59, "placa": "PAE3821 (22/0927)"},
#   {"id_unidad": 60, "placa": "PAC3513 (31/0913)"},
#   {"id_unidad": 61, "placa": "PAC3396 (34/0912)"},
#   {"id_unidad": 62, "placa": "PAC3174 (24/0921)"},
#   {"id_unidad": 63, "placa": "PAC7856 (25/0915)"}


    # {"id_unidad": 65, "placa": "PAA9282 (09/2309)"},
    # {"id_unidad": 66, "placa": "PAC3093 (12/2312)"},
    # {"id_unidad": 67, "placa": "PAA7251 (13/2313)"},
    # {"id_unidad": 68, "placa": "PAC3512 (14/2314)"},
    # {"id_unidad": 69, "placa": "PAA9201 (15/2315)"},
    # {"id_unidad": 70, "placa": "PAC3005 (16/2316)"},
    # {"id_unidad": 71, "placa": "PAC6142 (17/2317)"},
    # {"id_unidad": 72, "placa": "PAA4939 (18/2318)"},
    # {"id_unidad": 73, "placa": "PAC2360 (19/2319)"},
    # {"id_unidad": 74, "placa": "PAC3141 (20/2320)"},
    # {"id_unidad": 75, "placa": "PAB6432 (21/2321)"},
    # {"id_unidad": 76, "placa": "PAC3077 (22/2322)"},
    # {"id_unidad": 77, "placa": "PAC2244 (23/2323)"},
    # {"id_unidad": 78, "placa": "PUH0814 (24/2324)"},
    # {"id_unidad": 79, "placa": "PAB7700 (25/2325)"},
    # {"id_unidad": 80, "placa": "PAC2087 (26/2326)"},
    # {"id_unidad": 81, "placa": "PAC5853 (27/2327)"},
    # {"id_unidad": 82, "placa": "PAA9640 (28/2328)"},
    # {"id_unidad": 83, "placa": "PAC3094 (29/2329)"},
    # {"id_unidad": 84, "placa": "PAA7510 (30/2330)"},
    # {"id_unidad": 85, "placa": "PAU0032 (31/2331)"},
    # {"id_unidad": 86, "placa": "PUC0235 (32/2332)"},
    # {"id_unidad": 87, "placa": "PAA4790 (33/2333)"},
    # {"id_unidad": 88, "placa": "PAB6430 (34/2334)"},
    # {"id_unidad": 89, "placa": "PAB3942 (35/2335)"},
    # {"id_unidad": 90, "placa": "PAB6425 (36/2336)"},
    # {"id_unidad": 91, "placa": "PAA6964 (37/2337)"},
    # {"id_unidad": 92, "placa": "AAA2404 (38/2338)"},
    # {"id_unidad": 93, "placa": "PAC2154 (39/2339)"},
    # {"id_unidad": 94, "placa": "PAC3091 (40/2340)"},
    # {"id_unidad": 95, "placa": "PAC2291 (41/2341)"},
    # {"id_unidad": 96, "placa": "PAC3240 (43/2343)"},
    # {"id_unidad": 97, "placa": "PAC2888 (44/2344)"},
    # {"id_unidad": 98, "placa": "PAC2780 (45/2345)"},
    # {"id_unidad": 99, "placa": "PAC2961 (48/2348)"}
    # {"id_unidad": 100, "placa": "JAA1099 (23/1098)"},
    # {"id_unidad": 101, "placa": "PAA4857 (19/1106)"},
    # {"id_unidad": 102, "placa": "PAB0095 (30/1111)"},
    # {"id_unidad": 103, "placa": "PAB0733 (32/1075)"},
    # {"id_unidad": 104, "placa": "PAC2079 (35/1116)"},
    # {"id_unidad": 105, "placa": "PAB1246 (03/1085)"},
    # {"id_unidad": 106, "placa": "PAC2168 (02/1072)"},
    # {"id_unidad": 107, "placa": "PAC3506 (76/1115)"},
    # {"id_unidad": 108, "placa": "PAC4179 (12/1102)"},
    # {"id_unidad": 109, "placa": "PAC5969 (56/1076)"},
    # {"id_unidad": 110, "placa": "PAC6120 (54/1077)"},
    # {"id_unidad": 111, "placa": "PAU0059 (18/1087)"},
    # {"id_unidad": 112, "placa": "PAU0118 (84/1079)"},
    # {"id_unidad": 113, "placa": "PUA0453 (87/1107)"},
    # {"id_unidad": 114, "placa": "PUA0544 (39/1095)"},
    # {"id_unidad": 115, "placa": "PUB0233 (57/1089)"},
    # {"id_unidad": 116, "placa": "PAB0347 (31/1083)"},
    # {"id_unidad": 117, "placa": "PUB0908 (36/1081)"},
    # {"id_unidad": 118, "placa": "PUC0800 (50/1071)"},
    # {"id_unidad": 119, "placa": "PAB0327 (27/1099)"},
    # {"id_unidad": 120, "placa": "PUG0157 (09/1097)"},
    # {"id_unidad": 121, "placa": "PUG0707 (59/1090)"},
    # {"id_unidad": 122, "placa": "PUH0211 (62/1100)"},
    # {"id_unidad": 123, "placa": "PUJ0590 (08/1112)"},
    # {"id_unidad": 124, "placa": "PZX0336 (65/1101)"},
    # {"id_unidad": 125, "placa": "PZO0395 (20/1118)"},
    # {"id_unidad": 126, "placa": "PZU0526 (15/1110)"},
    # {"id_unidad": 127, "placa": "TAA1131 (80/1103)"}
    {"id_unidad": 128, "placa": "PAC8303 (31/2331)"}

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
    text1 = f"URBANQUITO - {unidad['placa']}"
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
pdf.output("qrs_con_placas_nacional.pdf")
print("PDF generado exitosamente ✅")