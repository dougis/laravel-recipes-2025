module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true
    },
    extends: [
        'eslint:recommended'
    ],
    plugins: ['vue'],
    parser: 'vue-eslint-parser',
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module'
    },
    globals: {
        window: 'readonly',
        document: 'readonly',
        localStorage: 'readonly',
        console: 'readonly',
        require: 'readonly',
        navigator: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly'
    },
    ignorePatterns: [
        'vendor/**/*',
        'node_modules/**/*',
        'bootstrap/cache/**/*',
        'storage/**/*'
    ],
    rules: {
        'no-unused-vars': 'warn',
        'no-console': 'warn'
    }
};