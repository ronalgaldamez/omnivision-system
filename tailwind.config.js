{import("tailwindcss").Config}
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./app/Livewire/**/*.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                "sans": ["Inter", "system-ui", "sans-serif"],
                "mono": ["JetBrains Mono", "Fira Code", "monospace"],
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
    ],
}