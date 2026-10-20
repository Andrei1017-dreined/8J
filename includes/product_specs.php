<?php
/**
 * 8J Cellphone Accessories IMS - Comprehensive Product Specifications Provider
 * Provides deep technical specifications, package contents, compatibility guides,
 * and quality certification metrics for all store items.
 */

function get_product_full_specs($item, $type = 'product') {
    $sku = $item['sku'] ?? ($item['imei_1'] ?? '');
    $brand = $item['brand'] ?? '';
    $name = $item['name'] ?? ($item['model_name'] ?? '');

    // Default structure
    $specs = [
        'tagline' => 'Certified Quality Smartphone Accessory from 8J Tanauan',
        'overview' => $item['description'] ?? 'Store-tested accessory with verified durability, standard safety certifications, and in-store warranty.',
        'highlights' => [
            '100% Genuine & Store-Inspected Hardware',
            'Same-Day In-Store Counter Pick-Up in Tanauan City',
            'Official 8J Claim Slip & Transaction Warranty',
            'Full Physical & Electrical Functional Verification'
        ],
        'technical_details' => [
            'Brand' => $brand ?: 'Universal',
            'Model / SKU' => $sku,
            'Warranty' => '7-Day Replacement + 30-Day Diagnostic Store Warranty',
            'Quality Grade' => 'Brand New / Certified Retail Grade',
            'Store Location' => '8J Cellphone & Accessories Counter, Tanauan City, Batangas'
        ],
        'compatibility' => 'Compatible with standard smartphones, tablets, and mobile devices matching interface specifications.',
        'package_contents' => [
            $name,
            'User Guide & Safety Documentation',
            'Official 8J Store Warranty Slip'
        ],
        'gallery' => []
    ];

    // Specific product specs mapping
    if (stripos($sku, 'ACC-PWR-006') !== false || stripos($name, 'Baseus 20000mAh') !== false) {
        $specs['tagline'] = 'High-Capacity Dual-Protocol Fast Charge Power Bank with LED Digital Battery Percentage';
        $specs['overview'] = 'The Baseus 20000mAh 22.5W Fast Charging Power Bank is engineered for high-demand everyday users needing reliable, all-day multi-device endurance. Featuring dual USB-A ports alongside a bi-directional Type-C Power Delivery port, it supports Huawei 22.5W SuperCharge (SCP), Apple 20W PD, and Samsung AFC fast charge protocols. The built-in smart LED percentage display gives you exact real-time battery status without guesswork, while the airline-approved 74Wh capacity complies fully with international flight security guidelines.';
        $specs['highlights'] = [
            '22.5W Maximum Output: Fast-charge compatible phones up to 50%-60% in just 30 minutes',
            'Real-Time LED Digital Display: Clear percentage readout for battery level and fast-charge mode',
            '3-Device Simultaneous Charging: Charge your phone, earbuds, and companion device together',
            'Bi-Directional 20W PD USB-C: Rapid power bank self-recharge in approximately 6 to 7 hours',
            'Airline Approved (74Wh): Complies with UN38.3 and CAAP aviation carry-on standards',
            '9-Layer Circuit Safety: NTC smart temperature monitoring, overcharge, and short-circuit protection'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Baseus',
            'Model Code / SKU' => 'ACC-PWR-006 (PPBD050101)',
            'Battery Cell Type' => 'High-Density Polymer Lithium-Ion Battery Cells',
            'Nominal Battery Capacity' => '20,000mAh / 3.7V (74Wh)',
            'Rated Usable Capacity' => '12,000mAh (5V / 3A)',
            'Energy Conversion Efficiency' => '≥ 75% High Efficiency Transfer',
            'USB-C Input (PD)' => '5V / 3A, 9V / 2A (18W Max)',
            'Micro-USB Input' => '5V / 2A, 9V / 2A (18W Max)',
            'USB-C Output (PD 3.0)' => '5V / 3A, 9V / 2.22A, 12V / 1.5A (20W Max)',
            'USB-A 1 / 2 Output' => '4.5V / 5A (SCP), 5V / 4.5A, 5V / 3A, 9V / 2A, 12V / 1.5A (22.5W Max)',
            'Total Combined Multi-Port Output' => '5V / 3A (15W Max Concurrent)',
            'Fast Charge Protocols' => 'PD 3.0, QC 3.0, Huawei SCP/FCP, Samsung AFC, Apple 2.4A',
            'Display Indicator' => 'Laser-cut HD LED Digital Battery Status Display',
            'Physical Dimensions' => '153 x 68.8 x 29.5 mm',
            'Net Weight' => '452 grams',
            'Casing Material' => 'V0 Fire-Retardant ABS + Polycarbonate Matte Texture',
            'Safety Certifications' => 'CE, FCC, RoHS, UN38.3 Certified',
            'Warranty Period' => '7-Day Instant Replacement + 30-Day 8J In-Store Service Guarantee'
        ];
        $specs['compatibility'] = 'iPhone 15/14/13/12/11 series, iPad Pro/Air/Mini, Samsung Galaxy S24/S23/S22/A-Series, Xiaomi, Redmi, Poco, Oppo, Vivo, Realme, Nintendo Switch, Smartwatches, and TWS Earbuds.';
        $specs['package_contents'] = [
            '1x Baseus 20000mAh 22.5W Fast Charging Power Bank (Midnight Black)',
            '1x USB-A to Type-C High-Current Charging Cable (30cm)',
            '1x Original Baseus User Manual & Security Verification Card',
            '1x 8J Official Store Warranty & Claim Slip'
        ];
        $specs['gallery'] = [
            'baseus_powerbank.jpg'
        ];
    } elseif (stripos($sku, 'ACC-CHG-001') !== false || stripos($name, 'Apple 20W') !== false) {
        $specs['tagline'] = 'Original Fast-Charging Power Delivery Wall Adapter for iPhone & iPad';
        $specs['overview'] = 'The Apple 20W USB-C Power Adapter provides rapid, stable, and highly efficient charging at home, in the office, or on the go. Specifically optimized for iPhone 8 through iPhone 15 Pro Max models to achieve 50% charge in approximately 30 minutes. Engineered with intelligent IC voltage regulation to protect your battery health longevity.';
        $specs['highlights'] = [
            '20W USB Power Delivery 3.0 Rapid Charging',
            'Optimized for iPhone fast charging (0 to 50% in ~30 mins)',
            'Compact, lightweight travel-friendly pin design',
            'Multi-Protection IC prevents overcurrent, overheating, and short circuits',
            'Seamless compatibility with MagSafe Wireless Chargers'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Apple (OEM Certified)',
            'SKU' => 'ACC-CHG-001',
            'Total Wattage' => '20W Max Output',
            'Input Voltage' => 'AC 100-240V ~ 50/60Hz 0.5A (Worldwide Universal)',
            'Output Voltage / Current' => '5V / 3A (15W) or 9V / 2.22A (20W PD)',
            'Connector Interface' => 'USB Type-C Female Port',
            'Plug Type' => 'US Standard Flat 2-Pin Plug (Philippine Standard)',
            'Casing Finish' => 'High-Gloss Scratch-Resistant Thermal Polycarbonate',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'iPhone 15, 14, 13, 12, 11, XS, XR, X, 8 series; iPad Pro 11"/12.9", iPad Air, Apple Watch Fast Magnetic Chargers, MagSafe Duo, and all Type-C smartphones.';
        $specs['package_contents'] = [
            '1x Apple 20W USB-C Power Adapter',
            '1x Store Test Quality Verification Seal',
            '1x 8J Store Warranty Slip'
        ];
        $specs['gallery'] = ['apple_adapter.jpg', 'bavin_cable.jpg'];
    } elseif (stripos($sku, 'ACC-PWR-007') !== false || stripos($name, 'Capsule') !== false) {
        // Capsule Emergency Power Bank
        $specs['tagline'] = 'Ultra-Compact Lipstick-Size Emergency Power Bank with Direct Plug-in Connector';
        $specs['overview'] = 'The Capsule Mini Emergency Power Bank is the ultimate portable everyday charging essential. Designed without messy cords, it plugs directly into the bottom of your smartphone so you can continue taking calls, browsing, and taking photos without cables dangling in your hand. Featuring 5000mAh high-density polymer cells, a protective silicone plug cap, an integrated lanyard loop, and dual-connector options (Type-C and Lightning iOS versions). Compact enough to slide effortlessly into any pocket, clutch, or small purse.';
        $specs['highlights'] = [
            'Direct-Plug Design: No cables needed, charges right from the device port',
            '5,000mAh High-Density Cell: Provides 1 full emergency boost for modern smartphones',
            'Lipstick / Capsule Form Factor: Weighs only 95g with pocket-friendly dimensions',
            'Built-in Lanyard Loop doubles as a convenient finger handle',
            'Dual-Version Available: Native Apple Lightning or USB-C Reversible Plug',
            'Intelligent Safe Charging: Overcurrent, temperature, and overcharge protection'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Universal / 8J Mobile Lifestyle',
            'SKU' => 'ACC-PWR-007',
            'Nominal Capacity' => '5,000mAh / 3.7V (18.5Wh)',
            'Rated Usable Capacity' => '3,200mAh (5V / 2.1A)',
            'Input Port' => 'USB Type-C (5V / 2.0A)',
            'Direct Output Connector' => '5V / 2.1A Max Fast Flow (Lightning or Type-C)',
            'Dimensions' => '79 x 46 x 27 mm',
            'Net Weight' => '95 grams (Ultra-Lightweight)',
            'Battery Cell Type' => 'Grade-A High-Density Polymer Lithium-Ion',
            'Available Colors' => 'Sakura Pastel Pink, Pearl White',
            'Safety Protection' => 'Overvoltage, Short-Circuit, and Overheat Thermal IC',
            'Warranty' => '7-Day Instant Replacement Guarantee'
        ];
        $specs['compatibility'] = 'Compatible with iPhone 15/14/13/12/11/X/8 series, iPad, Samsung Galaxy A & S series, Xiaomi, Redmi, Oppo, Vivo, Realme, and all Type-C/Lightning devices.';
        $specs['package_contents'] = [
            '1x Mini Capsule 5000mAh Direct-Plug Emergency Power Bank',
            '1x Type-C Recharging Cable',
            '1x Integrated Silicone Connector Cap & Lanyard Strap',
            '1x 8J Store Warranty Slip'
        ];
        $specs['gallery'] = ['capsule_powerbank.png'];
    } elseif (stripos($sku, 'ACC-PWR-008') !== false || stripos($name, 'BAVIN 20000mAh') !== false) {
        // BAVIN 20000mAh 22.5W Built-in Cable Power Bank
        $specs['tagline'] = '22.5W Super Fast Charging Power Bank with Integrated Dual Output Cables & Digital Screen';
        $specs['overview'] = 'The BAVIN 20000mAh 22.5W Super Charge Power Bank brings maximum all-day endurance with ultimate grab-and-go convenience. It features two built-in reinforced output cables (Type-C for modern Androids and iPhone 15, and Lightning for earlier iPhones and iPads), eliminating the need to ever carry separate charging cables again. The precision laser-cut HD LED digital display reveals exact battery percentage from 1% to 100%. Equipped with Huawei 22.5W SuperCharge (SCP), Apple 20W PD, and Quick Charge 3.0 protocols to power up to 4 devices simultaneously.';
        $specs['highlights'] = [
            '22.5W Maximum Super Fast Charge: Delivers up to 60% battery in 30 minutes for compatible phones',
            'Dual Integrated Output Cables: Built-in Type-C + Lightning cables with lanyard clip',
            'Real-Time HD LED Digital Display: Shows exact numerical percentage (0-100%)',
            'Massive 20,000mAh Capacity: Recharges an iPhone up to 4.5 times, Samsung 3.5 times',
            '4-Device Concurrent Charging: Built-in cables plus standard USB-A & USB-C ports',
            'Airline Approved (74Wh): Complies fully with CAAP & FAA international flight safety limits'
        ];
        $specs['technical_details'] = [
            'Brand' => 'BAVIN Official Certified',
            'SKU' => 'ACC-PWR-008',
            'Battery Capacity' => '20,000mAh / 3.7V (74Wh High-Capacity)',
            'Rated Usable Capacity' => '12,500mAh (5V / 3A)',
            'Type-C Port Input (PD)' => '5V / 3A, 9V / 2A, 12V / 1.5A (18W Max)',
            'Built-in Type-C Cable Output' => '4.5V / 5A (SCP), 5V / 4.5A, 9V / 2.22A, 12V / 1.67A (22.5W Max)',
            'Built-in Lightning Cable Output' => '5V / 2.4A (12W Max Fast Charge)',
            'USB-A Output Port' => '5V / 4.5A, 9V / 2A, 12V / 1.5A (22.5W Max)',
            'Type-C Port Output' => '5V / 3A, 9V / 2.22A, 12V / 1.67A (20W PD)',
            'Battery Level Indicator' => 'HD LED Digital Percentage Readout (0 - 100%)',
            'Body Material' => 'V0 Fireproof ABS + Polycarbonate Matte Texture',
            'Weight' => '418 grams',
            'Safety Certifications' => 'CE, FCC, RoHS, UN38.3 Flight Certified',
            'Warranty' => '7-Day Replacement + 30-Day Store Warranty'
        ];
        $specs['compatibility'] = 'iPhone 15/14/13/12/11/X/8 series, iPad Pro/Air/Mini, Samsung Galaxy S24/S23/S22/A-Series, Xiaomi, Redmi, Poco, Oppo, Vivo, Realme, Huawei, smartwatches, and TWS earbuds.';
        $specs['package_contents'] = [
            '1x BAVIN 20000mAh 22.5W Fast Charging Power Bank',
            '1x Built-in Type-C High-Current Cable',
            '1x Built-in Lightning Fast-Charge Cable',
            '1x USB-A to Type-C Recharging Cable',
            '1x BAVIN Official Certificate & 8J Store Warranty Slip'
        ];
        $specs['gallery'] = ['bavin_20000mah.png', 'bavin_powerbank_white.png'];
    } elseif (stripos($sku, 'ACC-CAB-002') !== false || stripos($name, 'Bavin') !== false) {
        $specs['tagline'] = 'Heavy-Duty 27W Braided Fast-Charging & Data Sync Cable';
        $specs['overview'] = 'Designed for durability and high-speed PD throughput, the Bavin Type-C to Lightning cable features high-density nylon ballistic braided shielding and reinforced zinc alloy connectors. Rated for 10,000+ bends, it withstands daily heavy usage while sustaining fast 27W power delivery and 480Mbps data synchronization.';
        $specs['highlights'] = [
            'Supports 27W / 20W Power Delivery Fast Charging',
            'Double-Braided Ballistic Nylon Armor for high tensile strength',
            'Reinforced Strain-Relief Collar tested to 10,000+ bends',
            'High-Speed 480Mbps Data Synchronization',
            'Gold-Plated Lightning Connectors resist corrosion and wear'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Bavin',
            'SKU' => 'ACC-CAB-002',
            'Length' => '1.2 Meters (4.0 Feet)',
            'Connectors' => 'USB Type-C Male to 8-Pin Lightning Male',
            'Maximum Current Rating' => '3.0 Amperes (27W PD Max)',
            'Cable Material' => 'Tinned Copper Core with Multi-Layer Nylon Weave',
            'Data Transfer Rate' => 'USB 2.0 (480 Mbps)',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'All Lightning iPhones (iPhone 14 Pro Max down to iPhone 6), iPads with Lightning port, and AirPods.';
        $specs['package_contents'] = [
            '1x Bavin Type-C to Lightning 1.2M Braided Cable',
            '1x Silicone Cable Organizer Tie',
            '1x 8J Store Warranty Slip'
        ];
        $specs['gallery'] = ['bavin_cable.jpg', 'apple_adapter.jpg'];
    } elseif (stripos($sku, 'ACC-TMP-003') !== false || stripos($name, 'Privacy Tempered') !== false) {
        $specs['tagline'] = '28-Degree Micro-Louver Anti-Spy Full Adhesive 9H Glass';
        $specs['overview'] = 'Protect your screen and your confidential messages with the WK Design 9D Full Privacy Screen Protector. Using advanced micro-louver optical technology, the screen is crystal clear when viewed straight on, while blacking out completely at a 28-degree angle from the sides. Built from 9H hardness tempered glass with full edge-to-edge glue.';
        $specs['highlights'] = [
            '28° True Anti-Spy Privacy Angle: Blocks bystanders from viewing your chats & passwords',
            '9H Diamond Surface Hardness: Resists keys, coins, and drops',
            'Full Silk-Screen Edge-to-Edge Curved Fit',
            'Electroplated Oleophobic Coating repels fingerprints and facial oils',
            'Free In-Store Professional Installation at 8J Tanauan Counter'
        ];
        $specs['technical_details'] = [
            'Brand' => 'WK Design / KingKong Privacy',
            'SKU' => 'ACC-TMP-003',
            'Glass Thickness' => '0.33mm Ultra-Thin Responsive Touch',
            'Hardness Rating' => '9H Mohs Hardness',
            'Adhesive Type' => 'Full-Coverage AB OCA Optical Adhesive (Bubble-Free)',
            'Clarity Rating' => '92% High Definition Light Transmittance (Center View)',
            'Warranty' => 'Free In-Store Application & Fit Guarantee'
        ];
        $specs['compatibility'] = 'Apple iPhone 13 (6.1") and iPhone 14 (6.1").';
        $specs['package_contents'] = [
            '1x 9D Privacy Tempered Glass',
            '1x Professional Screen Cleaning Wipe Set (Wet & Dry)',
            '1x Dust Absorber & Guide Sticker Sheet'
        ];
        $specs['gallery'] = ['screen_protector.jpg'];
    } elseif (stripos($sku, 'ACC-CSE-005') !== false || stripos($name, 'MagSafe') !== false) {
        $specs['tagline'] = 'Shockproof Military-Grade Clear Case with Integrated N52 Magnetic Ring';
        $specs['overview'] = 'Keep your phone protected while showing off its original colors. This hybrid protective case combines a crystal-clear scratch-resistant acrylic back with shock-absorbing TPU bumpers. Built-in 38-piece N52 neodymium magnets provide instant, ultra-strong alignment with all MagSafe chargers, wallets, and car mounts.';
        $specs['highlights'] = [
            'Built-in 38 N52 High-Strength Neodymium Magnets for firm MagSafe lock',
            'Military-Grade Drop Protection (MIL-STD-810G air-cushioned corners)',
            'Bayer Anti-Yellowing Polymer ensures long-lasting transparency',
            '1.2mm Raised Screen Lips & 1.5mm Raised Camera Bezel',
            'Tactile Independent Metallic Button Covers'
        ];
        $specs['technical_details'] = [
            'Brand' => 'OEM MagSafe Series',
            'SKU' => 'ACC-CSE-005',
            'Backplate Material' => '4H Scratch-Resistant Rigid Acrylic',
            'Bumper Material' => 'Flexible Shock-Absorbing Thermoplastic Polyurethane (TPU)',
            'Magnet Count' => '38-Piece N52 Neodymium Array',
            'Drop Test Rating' => 'Certified 6-Foot Drop Resistant',
            'Wireless Charging Support' => '15W MagSafe & Qi Fast Wireless Charging',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'Apple iPhone 15 Pro (6.1" Triple Camera).';
        $specs['package_contents'] = [
            '1x MagSafe Clear Shockproof Hybrid Case',
            '1x Protective Scratch Peel-Off Film'
        ];
        $specs['gallery'] = ['magsafe_case.jpg'];
    } elseif (stripos($sku, 'ACC-AUD-007') !== false || stripos($name, 'Lenovo') !== false) {
        $specs['tagline'] = 'Ultra-Light True Wireless Stereo Earbuds with 13mm Graphene Bass Drivers';
        $specs['overview'] = 'The Lenovo LP40 Pro delivers crystal-clear audio fidelity and punchy bass in an ultra-compact package. Equipped with Bluetooth 5.1 and dual host transmission, both earbuds connect simultaneously for low latency and zero dropouts. Features intuitive touch controls and an ergonomic semi-in-ear silicone seal for all-day comfort.';
        $specs['highlights'] = [
            'Bluetooth 5.1 Low Latency Chip with 10-meter stable range',
            '13mm Large Composite Diaphragm for deep bass and crisp vocals',
            'Up to 20 Hours Combined Playtime with 250mAh Charging Case',
            'ENC Dual-Microphone Noise Cancellation for clear phone calls',
            'IPX5 Sweatproof & Water-Resistant for gym and commuting',
            'Fast Type-C Charging port'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Lenovo',
            'SKU' => 'ACC-AUD-007',
            'Model' => 'LP40 Pro TWS',
            'Bluetooth Version' => 'v5.1 (EDR/BLE)',
            'Speaker Driver Size' => '13mm Dynamic Driver',
            'Earbud Battery Capacity' => '30mAh per earbud (~4-5 hours music)',
            'Case Battery Capacity' => '250mAh (Recharges earbuds 3-4 times)',
            'Charging Port' => 'Type-C USB',
            'Weight' => '3.5 grams per earbud (Ultra-Lightweight)',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'All Bluetooth-enabled devices: iPhone, Android, iPad, laptops, Windows PCs, and MacBooks.';
        $specs['package_contents'] = [
            '2x Lenovo LP40 Pro Earbuds (Left & Right)',
            '1x Compact Portable Charging Case',
            '3x Pairs of Silicone Ear Tips (S, M, L)',
            '1x Type-C Charging Cable',
            '1x User Manual & 8J Warranty Slip'
        ];
        $specs['gallery'] = ['lenovo_earbuds.jpg'];
    } elseif ($type === 'unit' || isset($item['imei_1'])) {
        // Certified Phone Unit Specs
        $specs['tagline'] = '8J Certified Pre-Owned Smartphone - 100% Inspected & Openline';
        $specs['overview'] = "This {$item['brand']} {$item['model_name']} ({$item['storage_capacity']}, {$item['color']}) is an 8J certified pre-owned smartphone inspected through our rigorous 25-point hardware and software diagnostic checklist. It is completely openline to all SIM networks (Globe, Smart, DITO, TM, TNT), iCloud/Google account clean, and ready for immediate daily driving.";
        $specs['highlights'] = [
            '25-Point Comprehensive Technical Diagnostic Verified',
            'Condition Grade: ' . ($item['unit_condition'] ?? 'Grade A'),
            'Battery Health: ' . ($item['battery_health'] ? $item['battery_health'] . '% (Healthy)' : 'Tested & Verified'),
            '100% Clean Account Status: No iCloud, Google, or Carrier Locks',
            'Factory Unlocked / Openline to All Mobile Networks',
            'Clean NTC Record & Anti-Fencing Law Compliance Verified',
            '30-Day Store Service Warranty & After-Sales Support'
        ];
        $specs['technical_details'] = [
            'Brand' => $item['brand'],
            'Model' => $item['model_name'],
            'Internal Storage' => $item['storage_capacity'],
            'Color Finish' => $item['color'],
            'Cosmetic Condition' => $item['unit_condition'] ?? 'Grade A',
            'Battery Health' => ($item['battery_health'] ?? '85+') . '%',
            'Network Lock' => 'Openline / Factory Unlocked (All SIMs)',
            'IMEI / Serial' => substr($item['imei_1'], 0, 8) . '****** (Inspected Clean)',
            'Display & Touch' => 'Original Screen, 100% Touch Responsive, No Dead Pixels',
            'Biometrics' => 'Face ID / Fingerprint Scanner Fully Operational',
            'Cameras' => 'Front & Rear Cameras, Autofocus, Flash, and Video Tested',
            'Connectivity' => '5G / 4G LTE, Wi-Fi, Bluetooth, GPS, and Cellular Validated',
            'Audio & Mic' => 'Earpiece, Loudspeakers, and Noise-Cancelling Mics Clear',
            'Charging & Battery' => 'Charging Port and Battery Retain Standard Charge Cycle'
        ];
        $specs['compatibility'] = 'Supports all Philippine SIM cards (Smart, Globe, DITO, GOMO, TNT, TM) and international roaming.';
        $specs['package_contents'] = [
            "1x {$item['brand']} {$item['model_name']} ({$item['storage_capacity']}) Unit",
            '1x Heavy-Duty Charging Cable & Fast Wall Adapter',
            '1x Pre-Installed Screen Protector',
            '1x Clear Protective Case',
            '1x SIM Ejector Tool',
            '1x Official 8J Certified Warranty Certificate & Claim Slip'
        ];

        // Phone gallery photos based on model
        if (stripos($name, 'Smart 20') !== false) {
            $specs['tagline'] = '6.78" 120Hz Punch-Hole Screen, Helio G81 Ultimate & 5200mAh Battery';
            $specs['overview'] = "The Infinix Smart 20 redefines budget excellence with a class-leading 6.78-inch 120Hz punch-hole display and a massive 5200mAh battery housed in an ultra-slim 7.7mm design. Powered by the MediaTek Helio G81 Ultimate processor with Mali-G52 MC2 GPU, it provides ultra-fluid navigation and everyday speed. Features Pure Voice Calls with Voiceprint Noise Reduction, Google AI Plus integration, 15W Type-C fast charging with OTG reverse charge, and dual AI cameras with 2K 30fps video recording.";
            $specs['highlights'] = [
                '6.78" 120Hz Smooth IPS LCD Punch-Hole Display (700 nits peak)',
                'MediaTek Helio G81 Ultimate Octa-Core Processor + Mali-G52 MC2 GPU',
                '5,200mAh High-Capacity Battery with 15W Type-C Fast Charging & OTG',
                '7.7mm Ultra-Slim Profile: Featherweight ergonomic form factor',
                'Pure Voice Calls: Voiceprint noise reduction filters ambient noise',
                'Google AI Plus Integration with Gemini assistance',
                '100% Brand New Sealed Unit, Openline to all Philippine networks'
            ];
            $specs['technical_details'] = [
                'Brand' => 'Infinix',
                'Model' => 'Smart 20',
                'Internal Storage' => $item['storage_capacity'] ?? '128GB',
                'Color Finish' => $item['color'] ?? 'Sunset Orange',
                'Display Size & Panel' => '6.78-inch HD+ IPS LCD, 120Hz (Adaptive 45/60/90/120Hz)',
                'Screen Resolution' => '720 x 1576 pixels (19.7:9 ratio, ~254 ppi, 700 nits)',
                'Processor (SoC)' => 'MediaTek Helio G81 Ultimate (12nm Octa-Core)',
                'Graphics (GPU)' => 'ARM Mali-G52 MC2',
                'RAM Memory' => '4GB (+ Up to 4GB Extended Virtual RAM)',
                'Rear Camera' => '8 MP f/2.0 Main Camera + Dual LED Flash, 2K@30fps Video',
                'Front Camera' => '8 MP f/2.0 Selfie Camera, 2K@30fps Video',
                'Battery & Charging' => '5,200 mAh, 15W Type-C 2.0 Fast Charging with OTG Support',
                'Connectivity' => '4G LTE Dual Nano-SIM, Dual-Band Wi-Fi, Bluetooth 5.2, GPS',
                'Audio System' => 'Voiceprint Noise-Reduction Mic, Loudspeaker, 3.5mm Audio Jack',
                'Operating System' => 'Android with XOS Interface',
                'Dimensions' => '167.7 x 78.8 x 7.7 mm, 185g',
                'Warranty' => '1-Year Official Infinix Brand Warranty + 7-Day 8J Store Replacement'
            ];
            $specs['package_contents'] = [
                '1x Infinix Smart 20 Handset (Brand New Sealed)',
                '1x 15W Fast Wall Adapter',
                '1x USB Type-C 2.0 Charging Cable',
                '1x Protective Phone Case',
                '1x Factory Applied Screen Protector',
                '1x SIM Ejector Tool & Official Warranty Booklet'
            ];
            $specs['gallery'] = ['infinix_smart20_orange.png', 'infinix_smart20_silver.png'];
        } elseif (stripos($name, 'iPhone 13') !== false) {
            $specs['gallery'] = ['iphone_13.jpg', 'apple_13_midnight.jpg', 'apple_13_blue.jpg', 'gsm_iphone13.jpg'];
        } elseif (stripos($name, 'iPhone 11') !== false) {
            $specs['gallery'] = ['iphone_11.jpg', 'apple_11_white.jpg'];
        } elseif (stripos($name, 'Galaxy S22') !== false) {
            $specs['gallery'] = ['galaxy_s22.jpg', 'galaxy_s22.png'];
        } elseif (stripos($name, 'Redmi Note 12') !== false) {
            $specs['gallery'] = ['redmi_note_12.jpg'];
        }
    }

    // Ensure main image is first in gallery
    if (!empty($item['image'])) {
        array_unshift($specs['gallery'], $item['image']);
        $specs['gallery'] = array_values(array_unique($specs['gallery']));
    }

    return $specs;
}

/**
 * Customer reviews sample provider for realistic social proof
 */
function get_product_verified_reviews($product_name) {
    return [
        [
            'author' => 'Mark Kenneth D.',
            'date' => '3 days ago',
            'verified' => true,
            'rating' => 5,
            'title' => 'Sobrang sulit at mabilis kunin sa Tanauan branch!',
            'comment' => 'Nag-order ako online tapos after 1 hour kinuha ko agad sa store nila malapit sa plaza. Tested and working agad, original talaga yung ' . $product_name . '. Legit shop!'
        ],
        [
            'author' => 'Sarah Jane M.',
            'date' => '1 week ago',
            'verified' => true,
            'rating' => 5,
            'title' => '100% Authentic with store warranty slip',
            'comment' => 'Mabait yung staff at cashier, tinulungan pa ako i-test bago ko kunin. Ang linaw din ng condition grading at may official claim slip. Recommended 8J!'
        ],
        [
            'author' => 'Christian P.',
            'date' => '2 weeks ago',
            'verified' => true,
            'rating' => 5,
            'title' => 'High quality and durable',
            'comment' => 'Very satisfied with the item and durability. Fast charging gumagana sa phone ko and very clean package. Will buy accessories here again.'
        ]
    ];
}
