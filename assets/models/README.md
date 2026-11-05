# Real 3D phone models

Put a real 3D model file of a phone in this folder and the site uses it automatically, on the
product page ("3D" tab) and in the homepage Flagship Studio. Until a file exists, the site shows
the generated model from `assets/js/phone-3d-model.js`.

Only a real scan or manufacturer CAD file can be 100% accurate. The generated model gets the
proportions, colour and camera layout roughly right; it can't reproduce exact curves, ring
finishes, button cut-outs or textures.

## Format

- `.glb` (preferred) or `.gltf`. Draco-compressed files are supported.
- Standing upright (Y-up) is ideal. Models exported lying flat or on their side are stood up automatically.
- Any unit/scale is fine: the viewer centres the model and scales it to phone height.
- Keep each file under ~5 MB so the page loads quickly (compress textures, or use Draco /
  `gltf-transform optimize in.glb out.glb`).

## Where to get accurate models

- Buy ready-made models (CGTrader, TurboSquid, Sketchfab Store). Check the licence allows
  use on a commercial website.
- Ask the brand's local distributor or marketing contact for official product renders/CAD.
- Scan a physical unit in the store with a phone photogrammetry app (e.g. Polycam, RealityScan)
  and export as `.glb`. Good results need even lighting and a matte spray or case for glossy glass.

## File names

One file covers every storage / RAM variant and every unit of that model.
The name is brand + model, lowercase, words joined by `-`, `+` written as `plus`.

| Phone | File name |
|---|---|
| Apple iPhone 11 | `apple-iphone-11.glb` |
| Apple iPhone 12 | `apple-iphone-12.glb` |
| Apple iPhone 12 Pro | `apple-iphone-12-pro.glb` |
| Apple iPhone 13 | `apple-iphone-13.glb` |
| Apple iPhone 13 Pro | `apple-iphone-13-pro.glb` |
| Apple iPhone 14 | `apple-iphone-14.glb` |
| Apple iPhone 14 Pro | `apple-iphone-14-pro.glb` |
| Apple iPhone 15 | `apple-iphone-15.glb` |
| Apple iPhone 15 Pro | `apple-iphone-15-pro.glb` |
| Apple iPhone 15 Pro Max | `apple-iphone-15-pro-max.glb` |
| Honor 200 5G | `honor-200-5g.glb` |
| Honor Pad X8b | `honor-pad-x8b.glb` |
| Honor Pad X9b Max | `honor-pad-x9b-max.glb` |
| Honor X5b Plus | `honor-x5b-plus.glb` |
| Honor X6b | `honor-x6b.glb` |
| Honor X7b | `honor-x7b.glb` |
| Honor X8b | `honor-x8b.glb` |
| Honor X9b 5G | `honor-x9b-5g.glb` |
| Honor X9c 5G | `honor-x9c-5g.glb` |
| Infinix GT 10 Pro | `infinix-gt-10-pro.glb` |
| Infinix GT 20 | `infinix-gt-20.glb` |
| Infinix GT 20 Pro | `infinix-gt-20-pro.glb` |
| Infinix Hot 40 Pro | `infinix-hot-40-pro.glb` |
| Infinix Hot 40 Pro+ | `infinix-hot-40-pro-plus.glb` |
| Infinix Hot 50 | `infinix-hot-50.glb` |
| Infinix Note 40 Pro | `infinix-note-40-pro.glb` |
| Infinix Note Edge 5G | `infinix-note-edge-5g.glb` |
| Infinix Smart 20 | `infinix-smart-20.glb` |
| Infinix Xpad 30 Pro | `infinix-xpad-30-pro.glb` |
| Infinix Xpad 30E | `infinix-xpad-30e.glb` |
| Itel A58e | `itel-a58e.glb` |
| Itel A70 | `itel-a70.glb` |
| Itel A70+ | `itel-a70-plus.glb` |
| Itel Vista Tab 11 | `itel-vista-tab-11.glb` |
| Itel Vista Tab 12 | `itel-vista-tab-12.glb` |
| Itel Vista Tab 30s | `itel-vista-tab-30s.glb` |
| Oppo A06 5G | `oppo-a06-5g.glb` |
| Oppo A07 | `oppo-a07.glb` |
| Oppo A17 | `oppo-a17.glb` |
| Oppo A17 5G | `oppo-a17-5g.glb` |
| Oppo A18 5G | `oppo-a18-5g.glb` |
| Oppo A38 5G | `oppo-a38-5g.glb` |
| Oppo A57 5G | `oppo-a57-5g.glb` |
| Realme 13 5G | `realme-13-5g.glb` |
| Realme C65 | `realme-c65.glb` |
| Realme Note 50 | `realme-note-50.glb` |
| Realme Note 60 | `realme-note-60.glb` |
| Samsung Galaxy S22 5G | `samsung-galaxy-s22-5g.glb` |
| TechLife Pad | `techlife-pad.glb` |
| Xiaomi Redmi Note 12 | `xiaomi-redmi-note-12.glb` |

New phones added to the inventory follow the same rule: `config/db.php` → `local_3d_model_slug()`.
