/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
  theme: {
    extend: {
      colors: {
        background: '#030305',
        foreground: '#FFFFFF',
        card: '#0A0A0F',
        'card-foreground': '#FFFFFF',
        primary: '#FF0099',
        'primary-foreground': '#FFFFFF',
        secondary: '#00F0FF',
        'secondary-foreground': '#000000',
        accent: '#7000FF',
        'accent-foreground': '#FFFFFF',
        muted: '#1A1A24',
        'muted-foreground': '#A1A1AA',
        border: '#27272A',
        input: '#18181B',
        ring: '#FF0099',
        success: '#00FF94',
        warning: '#FFD600',
        error: '#FF0055',
      },
      fontFamily: {
        unbounded: ['Unbounded', 'sans-serif'],
        outfit: ['Outfit', 'sans-serif'],
        mono: ['Space Mono', 'monospace'],
        syne: ['Syne', 'sans-serif'],
      },
      borderRadius: {
        lg: '1rem',
        md: '0.75rem',
        sm: '0.5rem',
      },
      boxShadow: {
        'neon-pink': '0 0 20px rgba(255, 0, 153, 0.5)',
        'neon-blue': '0 0 20px rgba(0, 240, 255, 0.5)',
        'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.37)',
      },
    },
  },
  plugins: [],
}