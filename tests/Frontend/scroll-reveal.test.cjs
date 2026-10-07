const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const script = fs.readFileSync(path.join(__dirname, '../../resources/assets/js/scroll-reveal.js'), 'utf8');

function setup({ reduced = false, supported = true, navigating = false } = {}) {
  const classes = new Set();
  const events = {};
  let callback, observed = false, stopped = false;
  const element = {
    matches: () => false,
    classList: { add: name => classes.add(name), remove: name => classes.delete(name) },
    parentElement: { closest: () => null }, closest: () => null, contains: () => false,
    getBoundingClientRect: () => ({ top: 1200 }),
    addEventListener: (name, handler) => { events[name] = handler; },
  };
  const document = {
    readyState: 'complete', activeElement: {}, querySelectorAll: () => [element],
    querySelector: () => ({}), addEventListener: () => {},
  };
  const motion = { matches: reduced, addEventListener: (name, handler) => { events.motion = handler; }, removeEventListener: () => {} };
  const window = { meetajNavigationScrolling: navigating, innerHeight: 800, matchMedia: () => motion, addEventListener: () => {}, removeEventListener: () => {} };
  class Observer {
    constructor(handler) { callback = handler; }
    observe() { observed = true; }
    unobserve() { observed = false; }
    disconnect() { stopped = true; }
  }
  if (supported) window.IntersectionObserver = Observer;
  vm.runInNewContext(script, { document, window, IntersectionObserver: Observer, MutationObserver: class { observe() {} disconnect() {} }, WeakSet });
  return { classes, events, element, enter: () => callback([{ target: element, isIntersecting: true }]), observed: () => observed, stopped: () => stopped };
}

test('content stays visible until entry, then animates once and cleans up', () => {
  const page = setup();
  assert.equal(page.observed(), true);
  assert.equal(page.classes.size, 0);
  page.enter();
  assert.equal(page.classes.has('meetaj-scroll-reveal'), true);
  assert.equal(page.observed(), false);
  page.events.animationend();
  assert.equal(page.classes.size, 0);
});
test('reduced motion and unsupported observers leave content unchanged', () => {
  for (const options of [{ reduced: true }, { supported: false }]) {
    const page = setup(options);
    assert.equal(page.observed(), false);
    assert.equal(page.classes.size, 0);
  }
});
test('menu navigation skips entry motion for sections crossed during scrolling', () => {
  const page = setup({ navigating: true });
  page.enter();
  assert.equal(page.classes.size, 0);
  assert.equal(page.observed(), false);
});
test('focused controls do not animate and motion preference changes stop effects', () => {
  const page = setup();
  page.element.contains = () => true;
  page.enter();
  assert.equal(page.classes.size, 0);
  page.events.motion({ matches: true });
  assert.equal(page.stopped(), true);
});
