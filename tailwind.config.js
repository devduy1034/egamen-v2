/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/views/**/*.{php,js}"],
  darkMode: ['variant', '&:not(.light-style *)'],
  theme: {
    extend: {},
    animatedSettings: {
      animatedSpeed: 1000,
      heartBeatSpeed: 500,
      hingeSpeed: 2000,
      bounceInSpeed: 750,
      bounceOutSpeed: 750,
      animationDelaySpeed: 500,
      classes: ['bounce', 'bounceIn']
    }
  },
  plugins: [
    require('@tailwindcss/line-clamp'),
    require('@tailwindcss/forms'),
    require('tailwindcss-animatecss'),
  ],
}

