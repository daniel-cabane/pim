/**
 * Convert hex color to RGB
 * @param {string} hex - Hex color code (e.g., '#FF0000')
 * @returns {object} Object with r, g, b properties
 */
export function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : { r: 0, g: 0, b: 0 };
}

/**
 * Convert RGB to hex color
 * @param {number} r - Red value (0-255)
 * @param {number} g - Green value (0-255)
 * @param {number} b - Blue value (0-255)
 * @returns {string} Hex color code
 */
export function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

/**
 * Calculate relative luminance of a color (WCAG standard)
 * @param {string} hex - Hex color code
 * @returns {number} Luminance value between 0 and 1
 */
export function getColorLuminance(hex) {
    const { r, g, b } = hexToRgb(hex);
    
    // Normalize to 0-1 range
    const [rs, gs, bs] = [r, g, b].map(val => {
        val = val / 255;
        return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
    });
    
    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
}

/**
 * Check if a color is considered "bright" or "dark"
 * @param {string} hex - Hex color code
 * @returns {boolean} True if bright, false if dark
 */
export function isBrightColor(hex) {
    return getColorLuminance(hex) > 0.5;
}

/**
 * Adjust color brightness (lighten or darken)
 * @param {string} hex - Hex color code
 * @param {number} percent - Percentage to adjust (-100 to 100, negative darkens, positive lightens)
 * @returns {string} Adjusted hex color code
 */
export function adjustColorBrightness(hex, percent) {
    const { r, g, b } = hexToRgb(hex);
    
    const factor = 1 + percent / 100;
    const newR = Math.min(255, Math.max(0, Math.round(r * factor)));
    const newG = Math.min(255, Math.max(0, Math.round(g * factor)));
    const newB = Math.min(255, Math.max(0, Math.round(b * factor)));
    
    return rgbToHex(newR, newG, newB);
}

/**
 * Get adaptive color based on current theme
 * Ensures color stands out against the background
 * @param {string} serieColor - The serie's original hex color
 * @param {boolean} isDarkTheme - Whether the current theme is dark
 * @returns {string} Adapted hex color code
 */
export function getAdaptiveSerieColor(serieColor, isDarkTheme) {
    const isBright = isBrightColor(serieColor);
    
    // Light theme + bright color = darken it
    if (!isDarkTheme && isBright) {
        return adjustColorBrightness(serieColor, -40); // Darken by 40%
    }
    
    // Dark theme + dark color = lighten it significantly
    if (isDarkTheme && !isBright) {
        return adjustColorBrightness(serieColor, 40); // Lighten by 70%
    }
    
    // Dark theme + bright color = keep it bright (already good)
    return serieColor;
}
