"""Build a labelled contact sheet of downloaded photos: python3 tools/contact-sheet.py out.png slug..."""
import sys
from PIL import Image, ImageDraw

out = sys.argv[1]
slugs = sys.argv[2:]
cols, cell = 5, 320
rows = (len(slugs) + cols - 1) // cols
sheet = Image.new('RGB', (cols * cell, rows * (cell + 26)), 'white')
draw = ImageDraw.Draw(sheet)
for i, slug in enumerate(slugs):
    try:
        im = Image.open(f'/tmp/shots/{slug}.jpg').convert('RGB')
    except Exception as exc:
        print('skip', slug, exc)
        continue
    im.thumbnail((cell, cell))
    x = (i % cols) * cell
    y = (i // cols) * (cell + 26)
    sheet.paste(im, (x + (cell - im.width) // 2, y))
    draw.text((x + 6, y + cell + 6), f'{slug}  {im.width}x{im.height}', fill='black')
sheet.save(out)
print('wrote', out, sheet.size)
