import type { Config } from 'tailwindcss';

const config: Config = {
  darkMode: 'class',
  content: [
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './src/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          red: '#ff2a2a',
          'red-hover': '#e01f1f',
          'red-dark': '#991111',
          glow: 'rgba(255, 42, 42, 0.35)',
        },
        dark: {
          950: '#000000',
          900: '#070708',
          850: '#0f1013',
          800: '#15161b',
          700: '#22252c',
          600: '#343844',
        },
      },
      fontFamily: {
        heading: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        script: ['Caveat', 'cursive'],
      },
      boxShadow: {
        'brand-glow': '0 0 25px rgba(255, 42, 42, 0.35)',
        'pass-glow': '0 0 35px rgba(255, 42, 42, 0.25)',
      },
    },
  },
  plugins: [],
};

export default config;

