module.exports = {
  env: {
    browser: true,
    node: true,
    es2021: true,
    jest: true
  },
  extends: [
    'eslint:recommended'
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
    parser: '@babel/eslint-parser',
    requireConfigFile: false
  },
  plugins: [],
  rules: {
    // Add custom rules here
    'no-console': 'warn',
    'no-debugger': 'error',
    // Add more rules as needed
  },
  overrides: [
    {
      files: ['*.config.js', '*.config.cjs'],
      env: {
        node: true
      }
    },
    {
      files: ['tests/**/*.js'],
      env: {
        jest: true,
        node: true
      }
    }
  ]
};