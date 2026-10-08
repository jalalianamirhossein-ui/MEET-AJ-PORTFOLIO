const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const test = require('node:test');

test('vendored Swiper rejects prototype keys even when Array.indexOf is modified', () => {
  const context = vm.createContext({ console });
  const source = fs.readFileSync('resources/assets/vendor/swiper/swiper-bundle.min.js', 'utf8');
  vm.runInContext(source, context);
  // Exercise GHSA-hmx5-qpq5-p643 in an isolated JS realm.
  vm.runInContext(`
    Array.prototype.indexOf = () => -1;
    Swiper.extendDefaults(JSON.parse('{"__proto__":{"polluted":true}}'));
    Swiper.extendDefaults({constructor: {prototype: {polluted: true}}});
    if ({}.polluted !== undefined) throw new Error('Prototype pollution');
  `, context);
  assert.equal(fs.readFileSync('public/assets/vendor/swiper/swiper-bundle.min.js', 'utf8'), source);
});
