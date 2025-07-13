// Example test to verify Jest configuration
describe('Frontend Testing Setup', () => {
  test('Jest is working correctly', () => {
    expect(true).toBe(true);
  });

  test('Math operations work correctly', () => {
    expect(2 + 2).toBe(4);
    expect(10 - 5).toBe(5);
  });

  test('String operations work correctly', () => {
    expect('hello'.toUpperCase()).toBe('HELLO');
    expect('WORLD'.toLowerCase()).toBe('world');
  });

  test('Array operations work correctly', () => {
    const arr = [1, 2, 3];
    expect(arr.length).toBe(3);
    expect(arr.includes(2)).toBe(true);
  });
});