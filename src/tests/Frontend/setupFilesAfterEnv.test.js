// Test to verify setupFilesAfterEnv is working correctly
describe('Jest setupFilesAfterEnv Configuration', () => {
  test('global router mocks are available', () => {
    // These mocks should be available from setup.js
    expect(global.$router).toBeDefined();
    expect(global.$router.push).toBeDefined();
    expect(global.$router.replace).toBeDefined();
    expect(global.$router.back).toBeDefined();
    expect(global.$router.forward).toBeDefined();
    
    // Verify they are Jest functions
    expect(jest.isMockFunction(global.$router.push)).toBe(true);
    expect(jest.isMockFunction(global.$router.replace)).toBe(true);
    expect(jest.isMockFunction(global.$router.back)).toBe(true);
    expect(jest.isMockFunction(global.$router.forward)).toBe(true);
  });

  test('global route mock is available', () => {
    expect(global.$route).toBeDefined();
    expect(global.$route.params).toBeDefined();
    expect(global.$route.query).toBeDefined();
    expect(global.$route.path).toBe('/');
    expect(global.$route.meta).toBeDefined();
  });

  test('window.matchMedia mock is available', () => {
    expect(window.matchMedia).toBeDefined();
    expect(jest.isMockFunction(window.matchMedia)).toBe(true);
    
    // Test the mock implementation
    const mediaQuery = window.matchMedia('(min-width: 768px)');
    expect(mediaQuery).toBeDefined();
    expect(mediaQuery.matches).toBe(false);
    expect(mediaQuery.media).toBe('(min-width: 768px)');
    expect(jest.isMockFunction(mediaQuery.addListener)).toBe(true);
    expect(jest.isMockFunction(mediaQuery.removeListener)).toBe(true);
  });

  test('IntersectionObserver mock is available', () => {
    expect(global.IntersectionObserver).toBeDefined();
    
    // Test that we can instantiate it
    const observer = new global.IntersectionObserver();
    expect(observer).toBeDefined();
    expect(typeof observer.disconnect).toBe('function');
    expect(typeof observer.observe).toBe('function');
    expect(typeof observer.unobserve).toBe('function');
  });
});