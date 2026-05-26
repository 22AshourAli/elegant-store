/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // سيتم تفعيل الوضع الليلي بإضافة class="dark" إلى <html>
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}