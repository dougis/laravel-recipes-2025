module.exports = {
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/resources/js', '<rootDir>/tests/Frontend'],
  testMatch: [
    '**/__tests__/**/*.{js,vue}',
    '**/*.(test|spec).{js,vue}'
  ],
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.js$': 'babel-jest'
  },
  moduleFileExtensions: ['js', 'vue', 'json'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1'
  },
  collectCoverageFrom: [
    'resources/js/**/*.{js,vue}',
    '!resources/js/app.js',
    '!resources/js/bootstrap.js',
    '!**/node_modules/**'
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['lcov', 'text', 'html'],
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  },
  setupFilesAfterEnv: ['<rootDir>/tests/Frontend/setup.js']
};