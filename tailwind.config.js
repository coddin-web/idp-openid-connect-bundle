const colors = require('tailwindcss/colors');

module.exports = {
    content: [
        './templates/**/*.html.twig'
    ],
    theme: {
        extend: {
            colors: {
                'blue-gray': colors.slate,
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
