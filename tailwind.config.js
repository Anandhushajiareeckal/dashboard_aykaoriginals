/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        navy: { DEFAULT: '#0B132B', light: '#1a2a4a', dark: '#060c1a' },
        slate: { DEFAULT: '#5E6472' },
        gold:  { DEFAULT: '#C9A96E', light: '#E8C882' },
      },
      fontFamily: {
        sans: ['DM Sans', 'sans-serif'],
        display: ['Syne', 'sans-serif'],
      },
    },
  },
  plugins: [require('@tailwindcss/forms')],
}
