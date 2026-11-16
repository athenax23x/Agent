"""Crops and resizes the source photographs into the theme's image slots.

Sources live in /tmp/shots (see tools/fetch-photos.sh). Photos are from the Pexels CDN
under the Pexels License: free for commercial use, no attribution required.
Run: python3 tools/process-images.py
"""
import os
from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
THEME_IMG = os.path.join(os.path.dirname(HERE), 'assets', 'img')
SRC = '/tmp/shots'

# theme file -> (source slug, target width, target height)
PLAN = {
    'hero.jpg': ('hero-shirt', 1200, 1500),
    'editorial.jpg': ('editorial-rack', 1400, 984),
    'p-tee.jpg': ('tee', 900, 1125),
    'p-oxford.jpg': ('oxford', 900, 1125),
    'p-crewneck.jpg': ('crewneck', 900, 1125),
    'p-pocket-tee.jpg': ('pocket-tee', 900, 1125),
    'p-trouser.jpg': ('trouser', 900, 1125),
    'p-denim.jpg': ('denim2', 900, 1125),
    'p-short.jpg': ('short', 900, 1125),
    'p-overcoat.jpg': ('overcoat2', 900, 1125),
    'p-liner.jpg': ('liner', 900, 1125),
    'p-work-jacket.jpg': ('work-jacket', 900, 1125),
    'p-tote.jpg': ('tote', 900, 1125),
    'p-beanie.jpg': ('beanie', 900, 1125),
    'p-belt.jpg': ('belt', 900, 1125),
    'p-socks.jpg': ('socks', 900, 1125),
}


def crop_to(image, width, height):
    """Centre-crop to the target aspect ratio, then resize."""
    target = width / height
    current = image.width / image.height
    if current > target:
        new_width = int(round(image.height * target))
        left = (image.width - new_width) // 2
        image = image.crop((left, 0, left + new_width, image.height))
    elif current < target:
        new_height = int(round(image.width / target))
        top = (image.height - new_height) // 2
        image = image.crop((0, top, image.width, top + new_height))
    return image.resize((width, height), Image.LANCZOS)


def main():
    missing = []
    for name, (slug, width, height) in sorted(PLAN.items()):
        path = os.path.join(SRC, f'{slug}.jpg')
        if not os.path.exists(path):
            missing.append(f'{name} <- {slug}')
            continue
        image = Image.open(path).convert('RGB')
        before = image.size
        image = crop_to(image, width, height)
        out = os.path.join(THEME_IMG, name)
        image.save(out, 'JPEG', quality=82, optimize=True, progressive=True)
        print(f'{name:<20} {before[0]}x{before[1]} -> {width}x{height} '
              f'({os.path.getsize(out) // 1024} KB)')
    if missing:
        print('\nmissing sources:', missing)


main()
