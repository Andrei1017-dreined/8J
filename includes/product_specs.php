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
    } elseif (stripos($sku, 'ACC-AUD-008') !== false || stripos($name, 'AirPods Pro') !== false) {
        $specs['tagline'] = 'Active Noise Cancellation, Adaptive Audio & Personalized Spatial Audio with USB-C';
        $specs['overview'] = 'Apple AirPods Pro (2nd generation) with MagSafe Case delivers up to 2x more Active Noise Cancellation than the previous generation. Adaptive Audio intelligently tailors noise control to your environment, while Transparency mode lets outside sound in so you can hear what is happening around you. Featuring the Apple H2 headphone chip and Apple U1 chip in the MagSafe Charging Case with speaker and lanyard loop.';
        $specs['highlights'] = [
            'Apple H2 Headphone Chip: Custom amplifier delivers crisp highs and rich bass',
            'Up to 2x More Active Noise Cancellation (ANC)',
            'Adaptive Audio & Transparency Mode with Conversation Awareness',
            'Personalized Spatial Audio with Dynamic Head Tracking',
            'MagSafe Charging Case (USB-C) with Built-In Speaker and Precision Finding',
            'IP54 Dust, Sweat, and Water Resistant for both AirPods and Case',
            'Up to 6 hours listening time with ANC on (Up to 30 hours total with case)'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Apple',
            'SKU' => 'ACC-AUD-008',
            'Model' => 'AirPods Pro (2nd Generation)',
            'Chipset' => 'Apple H2 Headphone Chip + Apple U1 in Case',
            'Connectivity' => 'Bluetooth 5.3 Low Energy',
            'Audio Technology' => 'Custom high-excursion Apple driver, personalized spatial audio',
            'Charging Case' => 'MagSafe (USB-C), Apple Watch charger, or Qi-certified chargers',
            'Battery Life' => 'Up to 6 hours (single charge), up to 30 hours (with case)',
            'Water Resistance' => 'IP54 rated (dust, sweat, and water resistant)',
            'Warranty' => '7-Day Replacement + 1-Year Store Warranty'
        ];
        $specs['compatibility'] = 'Compatible with all iPhone models, Apple Watch, iPad, MacBooks, and Android smartphones via Bluetooth 5.3.';
        $specs['package_contents'] = [
            '1x Apple AirPods Pro (Left & Right Earbuds)',
            '1x MagSafe Charging Case (USB-C) with Speaker',
            '4x Pairs of Silicone Ear Tips (XS, S, M, L)',
            '1x USB-C Charge Cable',
            '1x Documentation & 8J Warranty Claim Slip'
        ];
        $specs['gallery'] = ['airpods_pro_2.png', 'airpods_3.png'];
    } elseif (stripos($sku, 'ACC-AUD-009') !== false || stripos($name, 'AirPods (3rd') !== false) {
        $specs['tagline'] = 'Personalized Spatial Audio, Force Sensor Controls & Up to 30 Hours Battery';
        $specs['overview'] = 'AirPods (3rd generation) feature Personalized Spatial Audio with dynamic head tracking that places sound all around you, plus Adaptive EQ that tunes music to your ears. With an all-new contoured design and shorter stem, the force sensor lets you easily control your entertainment and calls.';
        $specs['highlights'] = [
            'Personalized Spatial Audio with dynamic head tracking',
            'Adaptive EQ automatically tunes music to the shape of your ear',
            'Contoured design with intuitive force sensor controls',
            'Sweat and water resistant (IPX4 rating)',
            'Up to 6 hours of listening time with one charge (up to 30 hours with case)',
            'Lightning / MagSafe fast charging support'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Apple',
            'SKU' => 'ACC-AUD-009',
            'Model' => 'AirPods (3rd Generation)',
            'Chipset' => 'Apple H1 Headphone Chip',
            'Connectivity' => 'Bluetooth 5.0',
            'Battery Life' => 'Up to 6 hours listening time, 30 hours with case',
            'Charging' => 'MagSafe, Qi wireless, or Lightning',
            'Resistance' => 'IPX4 sweat and water resistant',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'iPhone 8 through iPhone 15 series, iPad, Apple Watch, and all Bluetooth devices.';
        $specs['package_contents'] = [
            '1x Apple AirPods (3rd generation) Earbuds',
            '1x MagSafe Charging Case',
            '1x Lightning to USB-C Cable',
            '1x Quick Start Guide'
        ];
        $specs['gallery'] = ['airpods_3.png', 'airpods_pro_2.png'];
    } elseif (stripos($sku, 'ACC-CHG-010') !== false || stripos($name, 'MagSafe Fast Wireless Charger') !== false) {
        $specs['tagline'] = 'Snap-On 15W Magnetic Fast Wireless Charger for iPhone 12 to 15 Series';
        $specs['overview'] = 'The Apple MagSafe Charger makes wireless charging a snap. The perfectly aligned magnets attach to your iPhone 12, 13, 14, or 15 to provide faster wireless charging up to 15W. Maintains compatibility with Qi charging so it can be used to wirelessly charge iPhone 8 or later, as well as AirPods models with a wireless charging case.';
        $specs['highlights'] = [
            'Precision N52 Neodymium Magnetic Alignment for 100% stable contact',
            'High-Speed 15W Peak Wireless Output for compatible iPhones',
            'Backwards compatible with Qi-enabled smartphones (iPhone 8+, AirPods)',
            'Integrated 1-meter USB-C cable for convenient reach',
            'Smart thermal management prevents phone overheating during fast charging'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Apple',
            'SKU' => 'ACC-CHG-010',
            'Max Output' => '15W (requires 20W USB-C Power Adapter)',
            'Input Connector' => 'USB Type-C',
            'Cable Length' => '1.0 Meter Integrated Heavy-Duty Cable',
            'Material' => 'Anodized Aluminum Shell with Soft-Touch Pad',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'iPhone 15 / 15 Pro / 15 Pro Max, iPhone 14 series, iPhone 13 series, iPhone 12 series, and AirPods with MagSafe Case.';
        $specs['package_contents'] = [
            '1x Apple MagSafe Charger with 1m Integrated USB-C Cable',
            '1x User Manual & 8J Authenticity Tag'
        ];
        $specs['gallery'] = ['apple_magsafe_charger.png', 'apple_magsafe_battery.png'];
    } elseif (stripos($sku, 'ACC-PWR-011') !== false || stripos($name, 'MagSafe Magnetic Battery Pack') !== false) {
        $specs['tagline'] = 'Compact Magnetic Wireless Battery Pack - Snap & Go Everyday Power';
        $specs['overview'] = 'Attaching the MagSafe Battery Pack is a breeze. Its compact, intuitive design makes on-the-go charging easy. The perfectly aligned magnets keep it attached to your iPhone 12, 13, 14, or 15 providing safe and reliable wireless charging automatically without pressing buttons.';
        $specs['highlights'] = [
            'Snap-and-Charge: Zero cables required while attached to your phone',
            'Up to 70% additional charge on iPhone 12/13/14/15 models',
            'Pass-Through Charging: Charges iPhone and battery pack simultaneously with 20W plug',
            'iOS Battery Widget Integration: View real-time battery percentage on lock screen',
            'Silky soft-touch matte exterior finish with rounded edges'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Apple',
            'SKU' => 'ACC-PWR-011',
            'Capacity' => '5,000mAh Lithium-Polymer',
            'Wireless Output' => '5W on-the-go / Up to 15W when connected to 20W+ adapter',
            'Input Port' => 'Lightning Connector (Pass-Through Charge)',
            'Warranty' => '7-Day Replacement Guarantee'
        ];
        $specs['compatibility'] = 'iPhone 12 mini to iPhone 15 Pro Max, and all MagSafe phone cases.';
        $specs['package_contents'] = [
            '1x Apple MagSafe Magnetic Battery Pack 5000mAh',
            '1x Quick Start Guide'
        ];
        $specs['gallery'] = ['apple_magsafe_battery.png', 'apple_magsafe_charger.png'];
    } elseif (stripos($sku, 'ACC-DEV-012') !== false || stripos($name, 'Watch Ultra') !== false) {
        $specs['tagline'] = '49mm Aerospace Titanium Case, 3000 Nits Brightness & Precision Dual GPS';
        $specs['overview'] = 'Apple Watch Ultra 2 is crafted for unprecedented performance. The lightweight titanium case is rugged and corrosion resistant, and it is raised to protect the sapphire front crystal from edge impacts. Powered by the S9 SiP with a 3000-nit Always-On Retina display and innovative double tap gesture.';
        $specs['highlights'] = [
            '49mm Aerospace-Grade Titanium Case with flat sapphire crystal screen',
            'Ultra-Bright 3,000 Nits Always-On Retina Display with Night Mode',
            'Apple S9 SiP 64-bit Dual-Core Processor + 4-Core Neural Engine',
            'Customizable International Orange Action Button',
            'Precision Dual-Frequency GPS (L1 and L5) with Trackback Compass',
            '100m Water Resistance & EN13319 Certified for Recreational Scuba to 40m',
            'Up to 36 hours normal use (Up to 72 hours in Low Power Mode)'
        ];
        $specs['technical_details'] = [
            'Brand' => 'Apple',
            'SKU' => 'ACC-DEV-012',
            'Case Size' => '49mm x 44mm x 14.4mm, 61.4g',
            'Connectivity' => 'LTE & UMTS, Wi-Fi 4 (802.11n), Bluetooth 5.3, UWB Gen 2',
            'Sensors' => 'Depth gauge, water temperature, ECG, blood oxygen, optical heart rate',
            'Battery Life' => '36 to 72 Hours',
            'Operating System' => 'watchOS',
            'Warranty' => '1-Year Store Service Warranty + 7-Day Replacement'
        ];
        $specs['compatibility'] = 'Requires iPhone Xs or later with iOS 17 or later.';
        $specs['package_contents'] = [
            '1x Apple Watch Ultra 2 (49mm Titanium Case)',
            '1x High-Durability Loop / Ocean Band',
            '1x Apple Watch Magnetic Fast Charger to USB-C Cable (1m)',
            '1x Official Documentation & 8J Warranty Slip'
        ];
        $specs['gallery'] = ['apple_watch_ultra.png'];
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
        } elseif (stripos($name, 'iPhone 15 Pro Max') !== false || stripos($name, '15 Pro Max') !== false) {
            $specs['tagline'] = 'A17 Pro 3nm Chip, Aerospace Titanium Design & 5x Optical Telephoto Zoom';
            $specs['highlights'] = [
                'Forged in Titanium: Lightest, strongest Pro models ever from Apple',
                'Super Retina XDR 6.7" OLED with ProMotion 120Hz & Always-On Display',
                'Apple A17 Pro Chip (3nm) with 6-Core GPU supporting Hardware Ray Tracing',
                'Customizable Action Button: Instant shortcut to Silent, Camera, or Torch',
                '48MP Main Camera + 12MP Ultra-Wide + 5x Optical Periscope Telephoto Lens',
                'USB-C 3.0 Port supporting up to 10Gbps high-speed file transfers',
                'Certified Grade A unit with 98% battery health and clean iCloud'
            ];
            $specs['technical_details']['Processor'] = 'Apple A17 Pro (3nm Hexa-Core)';
            $specs['technical_details']['Display'] = '6.7-inch Super Retina XDR OLED (2796 x 1290, 120Hz ProMotion, 2000 nits)';
            $specs['technical_details']['Rear Cameras'] = '48MP (f/1.8) Main + 12MP (f/2.2) Ultra-Wide + 12MP (f/2.8) 5x Telephoto';
            $specs['technical_details']['Front Camera'] = '12MP TrueDepth (f/1.9) with Autofocus and 4K60 Dolby Vision';
            $specs['technical_details']['Chassis'] = 'Grade 5 Titanium Frame with Textured Matte Glass Back';
            $specs['technical_details']['Connector'] = 'USB Type-C (USB 3, up to 10Gbps) with DisplayPort';
            $specs['gallery'] = ['iphone_15_pro_natural.png', 'iphone_15_pro_blue.png', 'iphone_15_pro_black.png', 'iphone_15_pro_white.png'];
        } elseif (stripos($name, 'iPhone 15 Pro') !== false) {
            $specs['tagline'] = 'A17 Pro 3nm Silicon, Grade 5 Titanium Body & Dynamic Island';
            $specs['highlights'] = [
                'Aerospace-Grade Titanium Frame: Featherweight durability with contoured edges',
                'Super Retina XDR 6.1" OLED with 120Hz ProMotion & Always-On Screen',
                'A17 Pro Flagship Processor: Next-generation gaming performance with ray tracing',
                'Configurable Action Button with haptic feedback',
                'Pro 48MP Camera System with 24MP default ultra-high-resolution shooting',
                'USB-C Port with fast charging & reverse charging support',
                'Full 30-day 8J store service warranty + 7-day direct swap'
            ];
            $specs['technical_details']['Processor'] = 'Apple A17 Pro (3nm Hexa-Core)';
            $specs['technical_details']['Display'] = '6.1-inch Super Retina XDR OLED (2556 x 1179, 120Hz ProMotion, 2000 nits)';
            $specs['technical_details']['Rear Cameras'] = '48MP Main + 12MP Ultra-Wide + 12MP 3x Telephoto Optical Zoom';
            $specs['technical_details']['Front Camera'] = '12MP TrueDepth Camera with Face ID';
            $specs['technical_details']['Connector'] = 'USB Type-C (USB 3)';
            $specs['gallery'] = ['iphone_15_pro_blue.png', 'iphone_15_pro_natural.png', 'iphone_15_pro_black.png', 'iphone_15_pro_white.png'];
        } elseif (stripos($name, 'iPhone 15') !== false) {
            $specs['tagline'] = 'Dynamic Island, 48MP Main Camera, Color-Infused Glass & USB-C';
            $specs['highlights'] = [
                'Dynamic Island: Bubbles up alerts and Live Activities so you never miss them',
                '48MP Main Camera with 2x Telephoto: 4x resolution of iPhone 14',
                'A16 Bionic Chip: Proven pro-level computational photography and smooth gaming',
                'Industry-First Color-Infused Back Glass with textured matte finish',
                'All-Day Battery Life with universal USB-C charging simplicity',
                'Pre-owned Grade A unit inspected and certified 100% genuine'
            ];
            $specs['technical_details']['Processor'] = 'Apple A16 Bionic (4nm Hexa-Core)';
            $specs['technical_details']['Display'] = '6.1-inch Super Retina XDR OLED (2556 x 1179, 2000 nits peak)';
            $specs['technical_details']['Rear Cameras'] = '48MP (f/1.6) Main + 12MP Ultra-Wide with 2x Optical Telephoto';
            $specs['technical_details']['Front Camera'] = '12MP TrueDepth with Autofocus';
            $specs['technical_details']['Connector'] = 'USB Type-C (USB 2.0)';
            $specs['gallery'] = ['iphone_15_pink.png', 'iphone_15_blue.png', 'iphone_15_black.png'];
        } elseif (stripos($name, 'iPhone 14 Pro') !== false) {
            $specs['tagline'] = 'Dynamic Island, Always-On 120Hz ProMotion Display & 48MP Quad-Pixel Sensor';
            $specs['highlights'] = [
                'Magical Dynamic Island: Seamless blend of hardware and software notifications',
                '48MP Quad-Pixel Main Camera: Up to 4x detail in ProRAW shooting',
                'Action Mode for super smooth handheld video stabilization',
                'Super Retina XDR 6.1" OLED with 120Hz ProMotion & Always-On display',
                'Surgical-Grade Stainless Steel Frame with Ceramic Shield front',
                'A16 Bionic 6-Core processor with 5-Core GPU'
            ];
            $specs['technical_details']['Processor'] = 'Apple A16 Bionic (4nm Hexa-Core)';
            $specs['technical_details']['Display'] = '6.1-inch Super Retina XDR (2556 x 1179, 120Hz ProMotion)';
            $specs['technical_details']['Rear Cameras'] = '48MP Main + 12MP Ultra-Wide + 12MP 3x Optical Telephoto';
            $specs['technical_details']['Front Camera'] = '12MP TrueDepth with Autofocus';
            $specs['gallery'] = ['iphone_14_pro_purple.png', 'iphone_14_pro_spaceblack.png', 'iphone_14_pro_gold.png'];
        } elseif (stripos($name, 'iPhone 14') !== false) {
            $specs['tagline'] = 'Crash Detection, Cinematic 4K Video & Upgraded All-Day Endurance';
            $specs['highlights'] = [
                'Dual 12MP Advanced Camera System with Photonic Engine for low-light detail',
                'Cinematic Mode in 4K Dolby Vision at up to 30 fps',
                'Action Mode for gimbal-like smooth handheld video recording',
                'A15 Bionic chip with 5-core GPU for ultra-fluid graphics and gaming',
                'Ceramic Shield front glass tougher than any smartphone glass',
                'Pre-owned Grade A certified unit with original battery health'
            ];
            $specs['gallery'] = ['iphone_14_midnight.png', 'iphone_14_starlight.png', 'iphone_14_blue.png'];
        } elseif (stripos($name, 'iPhone 13 Pro') !== false) {
            $specs['tagline'] = 'ProMotion 120Hz Super Retina XDR, Macro Photography & 3x Optical Zoom';
            $specs['highlights'] = [
                'ProMotion Technology with adaptive refresh rates up to 120Hz',
                'Macro Photography & Videography down to 2cm distance',
                'Triple 12MP Pro Camera System with 3x Optical Zoom Telephoto',
                'A15 Bionic chip with 5-core GPU for lightning-fast performance',
                'Surgical-grade stainless steel with durable Ceramic Shield front',
                'All-day battery life with 100% functional 3uTools score'
            ];
            $specs['gallery'] = ['iphone_13_pro_sierra.png', 'iphone_13_pro_graphite.png'];
        } elseif (stripos($name, 'iPhone 13') !== false) {
            $specs['tagline'] = 'A15 Bionic Speed, Sensor-Shift OIS & Cinematic Depth of Field';
            $specs['highlights'] = [
                'Diagonal Dual 12MP Camera system with Sensor-Shift Optical Image Stabilization',
                'Cinematic mode automatically shifts focus between subjects in 1080p',
                'Super Retina XDR 6.1" OLED with 28% brighter sunlight readability (800 nits)',
                'A15 Bionic chip with 4-core GPU and 16-core Neural Engine',
                'Ceramic Shield front, aerospace-grade aluminum edges, IP68 water resistance',
                'Complete store warranty certificate from 8J Tanauan branch'
            ];
            $specs['gallery'] = ['iphone_13_midnight.png', 'iphone_13_blue.png', 'iphone_13_pink.png', 'iphone_13_starlight.png'];
        } elseif (stripos($name, 'iPhone 12 Pro') !== false) {
            $specs['tagline'] = 'LiDAR Scanner, Pacific Blue Stainless Steel & Dolby Vision HDR Recording';
            $specs['highlights'] = [
                'LiDAR Scanner for instant AR and Night Mode Portrait autofocus',
                'Triple 12MP Camera system with Night mode on all lenses',
                'Apple ProRAW support for professional creative control',
                'A14 Bionic: First 5-nanometer chip in a smartphone',
                'Ceramic Shield front with 4x better drop performance',
                'MagSafe compatibility for 15W snap-on wireless charging'
            ];
            $specs['gallery'] = ['iphone_12_pro_pacific.png', 'iphone_12_black.png'];
        } elseif (stripos($name, 'iPhone 12') !== false) {
            $specs['tagline'] = 'Super Retina XDR OLED, A14 Bionic & MagSafe Fast Snap Charging';
            $specs['highlights'] = [
                'Super Retina XDR OLED display with edge-to-edge vibrant colors',
                'A14 Bionic chip with 16-core Neural Engine for billions of operations per second',
                'Dual 12MP Ultra-Wide and Wide cameras with Night mode on both',
                'MagSafe ecosystem support for quick snap accessories and wireless chargers',
                'Ceramic Shield front cover and aerospace-grade aluminum enclosure',
                '100% inspected, factory unlocked, clean account status'
            ];
            $specs['gallery'] = ['iphone_12_purple.png', 'iphone_12_black.png', 'iphone_12_white.png'];
        } elseif (stripos($name, 'iPhone 11') !== false) {
            $specs['tagline'] = 'Dual 12MP Ultra-Wide Camera, All-Day Battery & Liquid Retina Display';
            $specs['highlights'] = [
                'Dual 12MP Ultra-Wide and Wide cameras with Night mode and 4K60 video',
                '6.1-inch Liquid Retina HD LCD with True Tone color management',
                'A13 Bionic chip with 3rd-generation Neural Engine',
                'Face ID for secure authentication and Apple Pay',
                'All-day battery life with fast-charge capability',
                'Includes 8J charging adapter and cable with store warranty'
            ];
            $specs['gallery'] = ['iphone_11_white.png', 'iphone_11_purple.png', 'iphone_11_black.png'];
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
