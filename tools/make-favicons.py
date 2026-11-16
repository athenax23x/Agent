"""Regenerate the placeholder favicon set: python3 tools/make-favicons.py"""
import os
from PIL import Image, ImageDraw, ImageFont

BASE = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'assets', 'img')
FONT = '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf'

for size in (48, 192):
    scale = size * 8
    image = Image.new('RGB', (scale, scale), (17, 17, 17))
    draw = ImageDraw.Draw(image)
    margin = int(scale * 0.08)
    draw.rectangle([margin, margin, scale - margin, scale - margin],
                   outline=(240, 240, 238), width=max(2, int(scale * 0.006)))
    font = ImageFont.truetype(FONT, 300)
    box = draw.textbbox((0, 0), 'WP', font=font)
    draw.text(((scale - (box[2] - box[0])) / 2 - box[0],
               (scale - (box[3] - box[1])) / 2 - box[1] - int(scale * 0.02)),
              'WP', font=font, fill=(246, 245, 242))
    image.resize((size, size), Image.LANCZOS).save(os.path.join(BASE, f'favicon-{size}.png'), optimize=True)
    print(f'wrote favicon-{size}.png')
