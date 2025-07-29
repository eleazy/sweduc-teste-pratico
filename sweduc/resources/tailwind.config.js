const colors = require('tailwindcss/colors')

module.exports = {
  content: [
    './js/**/*.{js,ts,jsx}',
    './js/**/*.tsx',
    '!../public/clientes',
    '../public/js/index.js',
    '../public/*.html',
    '../public/*.php',
    '../public/**/*.html',
    '../public/**/*.php',
    '../src/View/**/*.php',
    '../src/**/*',
  ],
  theme: {
    extend: {
      colors: {
        green: colors.emerald,
        yellow: colors.amber,
        purple: colors.violet,
      },
      screens: {
        'print': { 'raw': 'print' },
      }
    },
  },
  plugins: [],
}
