"""Small source-builder regressions, using the standard library only."""
import sys, unittest
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / 'scripts'))
from article_structure import heading_number, renumber, section_references

class ArticleNumberingTest(unittest.TestCase):
    def test_main_prefixes_exclude_versions_and_addresses(self):
        for text in ['8.10.2 compatibility','10.0.0.1 topology','FAQ','1.2 Nested']:
            self.assertIsNone(heading_number(text))
        self.assertEqual(int(heading_number('۱۴. معماری')[1]),14)

    def test_ranges_follow_all_members_not_only_old_endpoints(self):
        mapping={9:14,10:9,11:10,12:11,13:12,14:13,15:16,16:15}
        self.assertEqual(section_references('sections 5–14; section 14; sections 15–16',mapping),'sections 5–14; section 13; sections 15–16')
        self.assertEqual(section_references('بخش‌های ۱۰–۱۴ و بخش ۱۶',mapping),'بخش‌های ۹–۱۳ و بخش ۱۵')
        self.assertEqual(section_references('بخش‌های ۵ تا ۱۴ و بخش‌های ۱۵ و ۱۶',mapping),'بخش‌های ۵ تا ۱۴ و بخش‌های ۱۵ و ۱۶')
        self.assertEqual(section_references('بخش‌های ۱۰ تا ۱۴',mapping),'بخش‌های ۹ تا ۱۳')

    def test_code_and_nested_numbers_are_exact_and_repeated_normalization_is_stable(self):
        code='<pre><code># section 14\nversion=8.10.2</code></pre>'
        source='<h2 data-en="14. Architecture" data-fa="۱۴. معماری">14. Architecture</h2><h2 data-en="3. Install" data-fa="۳. نصب">3. Install</h2><p>See section 14 and <code>section 3</code>.</p><h3>9. Nested</h3>'+code
        fixed=renumber(source)
        self.assertIn('data-en="1. Architecture" data-fa="۱. معماری"',fixed)
        self.assertIn('See section 1 and <code>section 3</code>',fixed)
        self.assertIn('<h3>9. Nested</h3>',fixed)
        self.assertIn(code,fixed)
        self.assertEqual(renumber(fixed),fixed)

if __name__ == '__main__':unittest.main()
