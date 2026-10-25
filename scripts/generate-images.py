#!/usr/bin/env python3
"""Generate the demo catalogue artwork (no external image dependencies)."""
from __future__ import annotations

import math
import os
import random
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "seed-assets"
PRODUCTS = OUT / "products"
CATEGORIES = OUT / "categories"
PRODUCTS.mkdir(parents=True, exist_ok=True)
CATEGORIES.mkdir(parents=True, exist_ok=True)

W, H = 900, 1125
S = 2  # supersampling factor

PALETTES = {
    "sand": ("#f5f0e6", "#e8ddcd", "#b45309", "#8a4b2c", "#e3d5c0", "#efe5d5"),
    "sage": ("#eef1ea", "#dbe2d6", "#3f6212", "#55603f", "#cdd6c3", "#e4e9dc"),
    "clay": ("#f6ece5", "#e9d6c9", "#9a3412", "#8a4b3a", "#e0c8b8", "#f1ddd0"),
    "stone": ("#f4f2ee", "#e4e0d9", "#3f3f46", "#57534e", "#d6d1c8", "#e9e4dc"),
    "teal": ("#eef3f1", "#d8e4df", "#0f766e", "#3d5a54", "#c6d8d2", "#dcebe5"),
    "ink": ("#f1f0ee", "#ddd9d3", "#1c1917", "#44403c", "#d2cbc2", "#e4dfd7"),
}


def font(size: int):
    try:
        return ImageFont.load_default(size=size)
    except Exception:
        return ImageFont.load_default()


def rgba(color: str, alpha: int):
    color = color.lstrip("#")
    return tuple(int(color[i : i + 2], 16) for i in (0, 2, 4)) + (alpha,)


def gradient(top: str, bottom: str, width: int = W, height: int = H) -> Image.Image:
    top_rgb = tuple(int(top[i : i + 2], 16) for i in (1, 3, 5))
    bottom_rgb = tuple(int(bottom[i : i + 2], 16) for i in (1, 3, 5))
    img = Image.new("RGB", (width * S, height * S), top_rgb)
    px = img.load()
    for y in range(height * S):
        t = y / max(1, height * S - 1)
        color = tuple(round(a + (b - a) * t) for a, b in zip(top_rgb, bottom_rgb))
        for x in range(0, width * S, 8):
            for dx in range(8):
                if x + dx < width * S:
                    px[x + dx, y] = color
    return img.convert("RGBA")


def grain(img: Image.Image, amount: int = 5) -> Image.Image:
    rng = random.Random(4)
    overlay = Image.new("L", img.size, 0)
    d = ImageDraw.Draw(overlay)
    for _ in range((img.size[0] * img.size[1]) // 260):
        x = rng.randrange(img.size[0])
        y = rng.randrange(img.size[1])
        d.point((x, y), fill=rng.randrange(20, 90))
    noise = overlay.filter(ImageFilter.GaussianBlur(1.1))
    black = Image.new("RGB", img.size, (20, 18, 16))
    white = Image.new("RGB", img.size, (255, 252, 246))
    img = Image.composite(black, img, noise.point(lambda p: p * amount // 100))
    img = Image.composite(white, img, noise.point(lambda p: p * amount // 140))
    return img


def shadow(d, shape, blur: int = 30, alpha: int = 70):
    layer = getattr(d, "_image", None) or getattr(d, "im", None)
    if layer is None:
        return
    sh = Image.new("RGBA", layer.size, (0, 0, 0, 0))
    ImageDraw.Draw(sh).rounded_rectangle(shape[:4], radius=shape[4], fill=(18, 15, 12, alpha))
    sh = sh.filter(ImageFilter.GaussianBlur(blur))
    layer.alpha_composite(sh)


def steam(d: ImageDraw.ImageDraw, points, color, width):
    d.line(points, fill=color, width=width, joint="curve")


def draw_mug(d, cx, cy, s, p):
    body, line, light = p[3], p[4], p[5]
    shadow(d, (cx - 150 * s, cy + 185 * s, cx + 150 * s, cy + 250 * s, 50 * s), int(22 * s), 55)
    # handle
    d.ellipse((cx + 60 * s, cy - 35 * s, cx + 245 * s, cy + 140 * s), outline=body, width=int(30 * s))
    d.ellipse((cx + 78 * s, cy - 17 * s, cx + 227 * s, cy + 122 * s), fill=p[0])
    # body
    d.rounded_rectangle((cx - 155 * s, cy - 125 * s, cx + 120 * s, cy + 200 * s), radius=38 * s, fill=body, outline=line, width=int(4 * s))
    d.ellipse((cx - 155 * s, cy - 165 * s, cx + 120 * s, cy - 80 * s), fill=light, outline=line, width=int(4 * s))
    d.ellipse((cx - 128 * s, cy - 148 * s, cx + 93 * s, cy - 95 * s), fill=body)
    # logo mark
    d.rounded_rectangle((cx - 55 * s, cy + 20 * s, cx + 20 * s, cy + 95 * s), radius=10 * s, outline=(255, 255, 255, 150), width=int(3 * s))
    # steam
    steam(d, [(cx - 70 * s, cy - 210 * s), (cx - 40 * s, cy - 250 * s), (cx - 70 * s, cy - 285 * s)], (255, 255, 255, 150) if len(p) < 7 else p[5], int(5 * s))
    steam(d, [(cx + 20 * s, cy - 215 * s), (cx + 50 * s, cy - 255 * s), (cx + 20 * s, cy - 295 * s)], (255, 255, 255, 130), int(4 * s))


def draw_notebook(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 190 * s, cy + 200 * s, cx + 200 * s, cy + 270 * s, 40 * s), int(26 * s), 60)
    d.rounded_rectangle((cx - 180 * s, cy - 250 * s, cx + 210 * s, cy + 220 * s), radius=18 * s, fill=p[5], outline=line, width=int(3 * s))
    d.rounded_rectangle((cx - 215 * s, cy - 225 * s, cx + 170 * s, cy + 245 * s), radius=18 * s, fill=body, outline=line, width=int(4 * s))
    d.rectangle((cx - 195 * s, cy - 225 * s, cx - 160 * s, cy + 245 * s), fill=accent)
    d.rounded_rectangle((cx - 95 * s, cy - 90 * s, cx - 15 * s, cy - 10 * s), radius=6 * s, fill=p[0])
    for i in range(4):
        y = cy + 30 * s + i * 26 * s
        d.line((cx - 80 * s, y, cx + 110 * s, y), fill=(255, 255, 255, 90), width=int(3 * s))
    d.arc((cx - 120 * s, cy + 80 * s, cx + 120 * s, cy + 200 * s), 20, 160, fill=accent, width=int(5 * s))


def draw_speaker(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 150 * s, cy + 245 * s, cx + 160 * s, cy + 300 * s, 45 * s), int(24 * s), 60)
    d.rounded_rectangle((cx - 145 * s, cy - 280 * s, cx + 150 * s, cy + 270 * s), radius=45 * s, fill=body, outline=line, width=int(4 * s))
    d.rounded_rectangle((cx - 110 * s, cy - 235 * s, cx + 115 * s, cy + 130 * s), radius=30 * s, fill=p[0])
    cx0, cy0 = cx + 2 * s, cy - 55 * s
    for ring in range(3):
        r = (45 + ring * 33) * s
        d.ellipse((cx0 - r, cy0 - r, cx0 + r, cy0 + r), outline=line, width=int(3 * s))
    d.ellipse((cx0 - 28 * s, cy0 - 28 * s, cx0 + 28 * s, cy0 + 28 * s), fill=accent)
    d.ellipse((cx - 22 * s, cy + 190 * s, cx + 22 * s, cy + 232 * s), fill=accent, outline=line, width=int(2 * s))
    d.rounded_rectangle((cx - 60 * s, cy + 248 * s, cx + 60 * s, cy + 255 * s), radius=4 * s, fill=line)


def draw_lamp(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    glow = Image.new("RGBA", (W * S, H * S), (0, 0, 0, 0))
    gd = ImageDraw.Draw(glow)
    gd.ellipse((cx - 330 * s, cy - 300 * s, cx + 330 * s, cy + 380 * s), fill=(255, 214, 140, 60))
    glow = glow.filter(ImageFilter.GaussianBlur(55))
    base = getattr(d, "_image", None) or getattr(d, "im", None)
    if base is not None:
        base.alpha_composite(glow)
    shadow(d, (cx - 190 * s, cy + 300 * s, cx + 210 * s, cy + 360 * s, 40 * s), int(26 * s), 55)
    d.ellipse((cx - 150 * s, cy + 290 * s, cx + 170 * s, cy + 335 * s), fill=body, outline=line, width=int(4 * s))
    d.line((cx + 10 * s, cy + 300 * s, cx + 10 * s, cy - 90 * s), fill=line, width=int(10 * s))
    d.line((cx + 10 * s, cy - 90 * s, cx - 200 * s, cy - 160 * s), fill=line, width=int(10 * s))
    d.polygon([(cx - 290 * s, cy - 70 * s), (cx - 105 * s, cy - 245 * s), (cx + 40 * s, cy - 240 * s), (cx + 120 * s, cy - 85 * s)], fill=body, outline=line)
    d.ellipse((cx - 150 * s, cy - 235 * s, cx + 10 * s, cy - 120 * s), fill=(255, 240, 200, 160))
    d.ellipse((cx - 100 * s, cy + 298 * s, cx + 100 * s, cy + 322 * s), fill=accent)


def draw_tote(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 190 * s, cy + 270 * s, cx + 200 * s, cy + 330 * s, 35 * s), int(24 * s), 55)
    d.arc((cx - 185 * s, cy - 220 * s, cx - 20 * s, cy + 30 * s), 180, 360, fill=line, width=int(20 * s))
    d.arc((cx + 20 * s, cy - 220 * s, cx + 185 * s, cy + 30 * s), 180, 360, fill=line, width=int(20 * s))
    d.polygon([(cx - 220 * s, cy - 10 * s), (cx + 220 * s, cy - 10 * s), (cx + 185 * s, cy + 290 * s), (cx - 185 * s, cy + 290 * s)], fill=body, outline=line)
    for i in range(3):
        x = cx - 120 * s + i * 120 * s
        d.line((x, cy + 5 * s, x - 10 * s, cy + 275 * s), fill=(255, 255, 255, 45), width=int(3 * s))
    d.rounded_rectangle((cx - 55 * s, cy + 85 * s, cx + 55 * s, cy + 185 * s), radius=8 * s, outline=accent, width=int(5 * s))
    d.line((cx - 20 * s, cy + 135 * s, cx + 20 * s, cy + 135 * s), fill=accent, width=int(5 * s))


def draw_candle(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 160 * s, cy + 235 * s, cx + 170 * s, cy + 290 * s, 35 * s), int(24 * s), 55)
    d.rounded_rectangle((cx - 150 * s, cy - 150 * s, cx + 150 * s, cy + 270 * s), radius=28 * s, fill=(235, 220, 200, 190), outline=line, width=int(3 * s))
    d.rounded_rectangle((cx - 125 * s, cy + 20 * s, cx + 125 * s, cy + 245 * s), radius=18 * s, fill=body, outline=line, width=int(3 * s))
    d.ellipse((cx - 150 * s, cy - 185 * s, cx + 150 * s, cy - 115 * s), fill=(245, 235, 220), outline=line, width=int(3 * s))
    d.line((cx, cy - 140 * s, cx, cy - 190 * s), fill=line, width=int(5 * s))
    d.polygon([(cx, cy - 285 * s), (cx + 45 * s, cy - 210 * s), (cx, cy - 165 * s), (cx - 45 * s, cy - 210 * s)], fill=accent)
    d.ellipse((cx - 24 * s, cy - 225 * s, cx + 24 * s, cy - 180 * s), fill=(255, 240, 190))
    d.rounded_rectangle((cx - 80 * s, cy + 80 * s, cx + 80 * s, cy + 180 * s), radius=4 * s, outline=(255, 255, 255, 160), width=int(2 * s))
    for i in range(3):
        d.line((cx - 55 * s, cy + 115 * s + i * 20 * s, cx + 55 * s, cy + 115 * s + i * 20 * s), fill=(255, 255, 255, 100), width=int(2 * s))


def draw_glass(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 150 * s, cy + 240 * s, cx + 160 * s, cy + 300 * s, 40 * s), int(24 * s), 55)
    d.polygon([(cx - 135 * s, cy - 230 * s), (cx + 135 * s, cy - 230 * s), (cx + 105 * s, cy + 270 * s), (cx - 105 * s, cy + 270 * s)], fill=(245, 245, 240, 70), outline=line)
    d.polygon([(cx - 120 * s, cy - 40 * s), (cx + 120 * s, cy - 40 * s), (cx + 98 * s, cy + 255 * s), (cx - 98 * s, cy + 255 * s)], fill=body)
    d.polygon([(cx - 120 * s, cy - 40 * s), (cx + 120 * s, cy - 40 * s), (cx + 124 * s, cy - 20 * s), (cx - 124 * s, cy - 20 * s)], fill=(255, 255, 255, 110))
    d.ellipse((cx - 100 * s, cy + 30 * s, cx - 20 * s, cy + 100 * s), fill=(255, 255, 255, 55))
    d.ellipse((cx + 10 * s, cy + 120 * s, cx + 80 * s, cy + 180 * s), fill=(255, 255, 255, 45))
    d.line((cx - 70 * s, cy - 230 * s, cx - 58 * s, cy + 268 * s), fill=(255, 255, 255, 120), width=int(4 * s))
    d.ellipse((cx - 105 * s, cy + 270 * s, cx + 105 * s, cy + 292 * s), fill=(240, 240, 235, 120))


def draw_planner(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 190 * s, cy + 215 * s, cx + 195 * s, cy + 280 * s, 40 * s), int(26 * s), 60)
    d.rounded_rectangle((cx - 195 * s, cy - 255 * s, cx + 190 * s, cy + 235 * s), radius=16 * s, fill=p[5], outline=line, width=int(3 * s))
    d.rounded_rectangle((cx - 210 * s, cy - 235 * s, cx + 175 * s, cy + 255 * s), radius=16 * s, fill=body, outline=line, width=int(4 * s))
    d.line((cx - 170 * s, cy - 180 * s, cx + 120 * s, cy - 180 * s), fill=(255, 255, 255, 120), width=int(4 * s))
    d.rounded_rectangle((cx - 160 * s, cy - 140 * s, cx + 120 * s, cy + 210 * s), radius=6 * s, outline=(255, 255, 255, 80), width=int(2 * s))
    for row in range(4):
        for col in range(5):
            x = cx - 130 * s + col * 50 * s
            y = cy - 100 * s + row * 60 * s
            d.ellipse((x - 5 * s, y - 5 * s, x + 5 * s, y + 5 * s), fill=(255, 255, 255, 90))
    d.rectangle((cx - 210 * s, cy - 235 * s, cx - 185 * s, cy + 255 * s), fill=accent)


def draw_headphones(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 220 * s, cy + 235 * s, cx + 220 * s, cy + 295 * s, 40 * s), int(26 * s), 55)
    d.arc((cx - 245 * s, cy - 320 * s, cx + 245 * s, cy + 180 * s), 190, 350, fill=line, width=int(38 * s))
    d.arc((cx - 245 * s, cy - 320 * s, cx + 245 * s, cy + 180 * s), 190, 350, fill=body, width=int(22 * s))
    for side in (-1, 1):
        x = cx + side * 200 * s
        d.rounded_rectangle((x - 75 * s, cy - 80 * s, x + 75 * s, cy + 215 * s), radius=45 * s, fill=body, outline=line, width=int(4 * s))
        d.rounded_rectangle((x - 45 * s, cy - 45 * s, x + 45 * s, cy + 175 * s), radius=32 * s, fill=accent, outline=line, width=int(3 * s))
    d.line((cx - 195 * s, cy + 100 * s, cx - 195 * s, cy + 220 * s), fill=line, width=int(12 * s))


def draw_shirt(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 215 * s, cy + 240 * s, cx + 220 * s, cy + 300 * s, 40 * s), int(26 * s), 55)
    pts = [
        (cx - 90 * s, cy - 260 * s), (cx - 225 * s, cy - 195 * s), (cx - 300 * s, cy - 40 * s),
        (cx - 190 * s, cy + 15 * s), (cx - 160 * s, cy - 60 * s), (cx - 170 * s, cy + 260 * s),
        (cx + 170 * s, cy + 260 * s), (cx + 160 * s, cy - 60 * s), (cx + 190 * s, cy + 15 * s),
        (cx + 300 * s, cy - 40 * s), (cx + 225 * s, cy - 195 * s), (cx + 90 * s, cy - 260 * s),
        (cx + 45 * s, cy - 205 * s), (cx - 45 * s, cy - 205 * s),
    ]
    d.polygon(pts, fill=body, outline=line)
    d.arc((cx - 70 * s, cy - 275 * s, cx + 70 * s, cy - 175 * s), 0, 180, fill=line, width=int(5 * s))
    d.line((cx - 45 * s, cy - 205 * s, cx - 45 * s, cy + 220 * s), fill=(255, 255, 255, 40), width=int(3 * s))
    d.rounded_rectangle((cx - 42 * s, cy + 50 * s, cx + 42 * s, cy + 90 * s), radius=4 * s, fill=accent)


def draw_plate(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 250 * s, cy + 170 * s, cx + 250 * s, cy + 265 * s, 50 * s), int(28 * s), 55)
    d.ellipse((cx - 290 * s, cy - 175 * s, cx + 290 * s, cy + 210 * s), fill=body, outline=line, width=int(4 * s))
    d.ellipse((cx - 230 * s, cy - 135 * s, cx + 230 * s, cy + 170 * s), fill=p[0], outline=line, width=int(3 * s))
    d.ellipse((cx - 140 * s, cy - 80 * s, cx + 140 * s, cy + 110 * s), outline=accent, width=int(4 * s))
    d.arc((cx - 200 * s, cy - 100 * s, cx + 60 * s, cy + 150 * s), 200, 320, fill=(255, 255, 255, 90), width=int(5 * s))


def draw_vase(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 190 * s, cy + 265 * s, cx + 200 * s, cy + 325 * s, 40 * s), int(26 * s), 55)
    # stems and leaves
    stems = [(-90, -170, -140, -420), (-20, -190, 30, -470), (55, -180, 150, -390)]
    for x1, y1, x2, y2 in stems:
        d.line((cx + x1 * s, cy + y1 * s, cx + x2 * s, cy + y2 * s), fill=accent, width=int(5 * s))
    for lx, ly, size, ang in [(-145, -360, 55, -30), (30, -430, 65, 20), (150, -340, 48, 30), (-40, -300, 40, -20)]:
        ex, ey = cx + lx * s, cy + ly * s
        r = size * s
        d.ellipse((ex - r, ey - r * 0.5, ex + r, ey + r * 0.5), fill=p[5], outline=accent, width=int(3 * s))
    # vase silhouette
    pts = [
        (cx - 105 * s, cy - 190 * s), (cx + 105 * s, cy - 190 * s), (cx + 150 * s, cy - 70 * s),
        (cx + 175 * s, cy + 110 * s), (cx + 115 * s, cy + 270 * s), (cx - 115 * s, cy + 270 * s),
        (cx - 175 * s, cy + 110 * s), (cx - 150 * s, cy - 70 * s),
    ]
    d.polygon(pts, fill=body, outline=line)
    d.ellipse((cx - 105 * s, cy - 210 * s, cx + 105 * s, cy - 170 * s), fill=p[0], outline=line, width=int(4 * s))
    d.arc((cx - 150 * s, cy - 30 * s, cx + 30 * s, cy + 190 * s), 250, 60, fill=(255, 255, 255, 80), width=int(5 * s))
    d.line((cx - 60 * s, cy + 120 * s, cx + 60 * s, cy + 120 * s), fill=accent, width=int(5 * s))


def draw_blanket(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 250 * s, cy + 210 * s, cx + 250 * s, cy + 285 * s, 45 * s), int(28 * s), 55)
    for i in range(3):
        y = cy + 150 * s - i * 105 * s
        w = (225 - i * 20) * s
        d.rounded_rectangle((cx - w, y - 75 * s, cx + w, y + 55 * s), radius=32 * s, fill=body, outline=line, width=int(3 * s))
        d.line((cx - w + 45 * s, y - 10 * s, cx + w - 45 * s, y - 10 * s), fill=(255, 255, 255, 50), width=int(4 * s))
    d.line((cx, cy - 190 * s, cx, cy + 185 * s), fill=accent, width=int(4 * s))


def draw_pen(d, cx, cy, s, p):
    body, line, accent = p[3], p[4], p[2]
    shadow(d, (cx - 250 * s, cy + 230 * s, cx + 250 * s, cy + 275 * s, 30 * s), int(22 * s), 50)
    d.line((cx - 230 * s, cy + 190 * s, cx + 220 * s, cy - 230 * s), fill=body, width=int(30 * s))
    d.line((cx - 230 * s, cy + 190 * s, cx - 280 * s, cy + 245 * s), fill=line, width=int(20 * s))
    d.line((cx + 90 * s, cy - 100 * s, cx + 210 * s, cy - 215 * s), fill=accent, width=int(10 * s))
    d.line((cx + 175 * s, cy - 185 * s, cx + 205 * s, cy - 155 * s), fill=line, width=int(6 * s))


PRODUCT_SCALE = {
    "mug": (1.55, 610),
    "notebook": (1.45, 610),
    "speaker": (1.42, 600),
    "lamp": (1.3, 575),
    "tote": (1.4, 600),
    "candle": (1.45, 610),
    "glass": (1.45, 610),
    "planner": (1.42, 610),
    "headphones": (1.38, 610),
    "shirt": (1.35, 605),
    "plate": (1.45, 600),
    "vase": (1.32, 605),
    "blanket": (1.38, 620),
    "pen": (1.45, 600),
}

DRAWERS = {
    "mug": draw_mug,
    "notebook": draw_notebook,
    "speaker": draw_speaker,
    "lamp": draw_lamp,
    "tote": draw_tote,
    "candle": draw_candle,
    "glass": draw_glass,
    "planner": draw_planner,
    "headphones": draw_headphones,
    "shirt": draw_shirt,
    "plate": draw_plate,
    "vase": draw_vase,
    "blanket": draw_blanket,
    "pen": draw_pen,
}


def render_product(key: str, palette_name: str, filename: str, seed: int = 0):
    p = PALETTES[palette_name]
    base = gradient(p[0], p[1])
    img = Image.new("RGBA", base.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(img, "RGBA")

    # soft background shapes
    d.ellipse((-260 * S, -300 * S, 560 * S, 460 * S), fill=(255, 255, 255, 54))
    d.ellipse((600 * S, 560 * S, 1260 * S, 1260 * S), fill=(255, 255, 255, 42))
    d.ellipse((120 * S, 190 * S, 780 * S, 850 * S), fill=rgba(p[2], 16))
    d.line((60 * S, 960 * S, 840 * S, 960 * S), fill=(60, 50, 40, 30), width=int(2 * S))

    scale, cy = PRODUCT_SCALE.get(key, (1.4, 600))
    DRAWERS[key](d, 450 * S, cy * S, scale * 0.88 * S, p)

    # wordmark
    f = font(int(18 * S))
    d.text((W * S - 55 * S, H * S - 48 * S), "hojreh", font=f, fill=(40, 34, 28, 90), anchor="rs")

    base.alpha_composite(img)
    img = base.resize((W, H), Image.LANCZOS).convert("RGB")
    img = grain(img, 4)
    img.save(PRODUCTS / filename, quality=88, optimize=True, progressive=True)
    print("product", filename)


PRODUCT_SPECS = [
    ("mug", "clay", "mug-sunrise.jpg"),
    ("notebook", "teal", "notebook-linen.jpg"),
    ("speaker", "stone", "speaker-nova.jpg"),
    ("lamp", "sand", "lamp-halo.jpg"),
    ("tote", "sage", "tote-hojreh.jpg"),
    ("candle", "clay", "candle-sandalwood.jpg"),
    ("glass", "ink", "glass-coffee.jpg"),
    ("planner", "sand", "planner-daily.jpg"),
    ("headphones", "stone", "headphones-quiet.jpg"),
    ("shirt", "sage", "shirt-organic.jpg"),
    ("plate", "clay", "plate-handmade.jpg"),
    ("vase", "sand", "vase-terracotta.jpg"),
    ("blanket", "teal", "blanket-wool.jpg"),
    ("pen", "stone", "pen-metal.jpg"),
]


def render_hero():
    hw, hh = 1000, 1200
    base = gradient("#f4ecdd", "#ddcfba", hw, hh)
    img = Image.new("RGBA", base.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(img, "RGBA")
    d.ellipse((-260 * S, 120 * S, 640 * S, 1020 * S), fill=(255, 255, 255, 52))
    d.rounded_rectangle((140 * S, 180 * S, 860 * S, 1040 * S), radius=360 * S, fill=rgba("#0f766e", 14), outline=rgba("#0f766e", 36), width=int(2 * S))
    d.ellipse((650 * S, 110 * S, 930 * S, 390 * S), fill=rgba("#b45309", 24))
    d.rectangle((0, 930 * S, hw * S, hh * S), fill=rgba("#cbb9a3", 150))
    d.line((70 * S, 930 * S, 930 * S, 930 * S), fill=rgba("#3c2f24", 30), width=int(2 * S))
    draw_lamp(d, 705 * S, 500 * S, 0.55 * S, PALETTES["sand"])
    draw_vase(d, 350 * S, 690 * S, 0.72 * S, PALETTES["sand"])
    draw_notebook(d, 570 * S, 755 * S, 0.5 * S, PALETTES["teal"])
    draw_mug(d, 775 * S, 740 * S, 0.5 * S, PALETTES["clay"])
    f = font(int(18 * S))
    d.text((855 * S, 1135 * S), "hojreh — everyday objects", font=f, fill=(40, 34, 28, 120), anchor="rs")
    base.alpha_composite(img)
    img = base.resize((hw, hh), Image.LANCZOS).convert("RGB")
    img = grain(img, 4)
    img.save(OUT / "hero.jpg", quality=88, optimize=True, progressive=True)
    print("hero")


def render_editorial(name: str, palette_name: str):
    p = PALETTES[palette_name]
    base = gradient(p[0], p[1])
    img = Image.new("RGBA", base.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(img, "RGBA")
    d.rounded_rectangle((130 * S, 120 * S, 770 * S, 1000 * S), radius=320 * S, fill=p[2] + "18", outline=p[2] + "44", width=int(3 * S))
    d.ellipse((300 * S, 260 * S, 610 * S, 570 * S), fill=p[2] + "55")
    d.arc((240 * S, 480 * S, 690 * S, 930 * S), 180, 360, fill=p[3], width=int(10 * S))
    d.line((0, 900 * S, W * S, 900 * S), fill=(60, 50, 40, 25), width=int(3 * S))
    f = font(int(18 * S))
    d.text((W * S - 55 * S, H * S - 50 * S), "hojreh journal", font=f, fill=(40, 34, 28, 110), anchor="rs")
    base.alpha_composite(img)
    img = base.resize((W, H), Image.LANCZOS).convert("RGB")
    img = grain(img, 5)
    img.save(OUT / name, quality=86, optimize=True, progressive=True)
    print("editorial", name)


def make_categories():
    mapping = {
        "home-living": "vase-terracotta.jpg",
        "kitchen": "mug-sunrise.jpg",
        "stationery": "notebook-linen.jpg",
        "audio": "headphones-quiet.jpg",
        "wear": "shirt-organic.jpg",
    }
    for slug, src in mapping.items():
        img = Image.open(PRODUCTS / src).convert("RGB")
        target = (4, 3)
        tw, th = img.width, int(img.width * target[1] / target[0])
        if th > img.height:
            th = img.height
            tw = int(th * target[0] / target[1])
        left = (img.width - tw) // 2
        top = (img.height - th) // 2
        img = img.crop((left, top, left + tw, top + th)).resize((900, 675), Image.LANCZOS)
        img.save(CATEGORIES / f"{slug}.jpg", quality=86, optimize=True, progressive=True)
        print("category", slug)


if __name__ == "__main__":
    for key, palette, filename in PRODUCT_SPECS:
        render_product(key, palette, filename)
    render_hero()
    render_editorial("blog-small-space.jpg", "sage")
    render_editorial("blog-materials.jpg", "clay")
    render_editorial("blog-habits.jpg", "stone")
    make_categories()
    print("done")
